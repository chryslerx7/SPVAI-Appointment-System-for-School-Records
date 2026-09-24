<?php
require_once(__DIR__ . '/../database/Database.php');

class NotificationService extends Database {

    private $config;

    public function __construct() {
        parent::__construct();
        $this->config = require(__DIR__ . '/../config/notifications.php');
    }

    /**
     * Central method to notify a user.
     * Orchestrates both In-App and Email notifications.
     */
    public function notifyUser($userId, $requestId, $type, $message, $emailDetails = null) {
        // 1. Create In-App Notification
        $this->createInAppNotification($userId, $requestId, $type, $message);

        // 2. Attempt Email Notification
        if ($emailDetails) {
            $this->sendEmailNotification($userId, $emailDetails);
        }
    }

    /**
     * Stores a notification record in the database.
     */
    private function createInAppNotification($userId, $requestId, $type, $message) {
        // Duplicate prevention: avoid sending the same notification for the same request and type
        // within the last 5 minutes (unless it's a new state change)
        $checkSql = "SELECT notification_id FROM notifications
                     WHERE user_id = ? AND request_id = ? AND type = ?
                     AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE) LIMIT 1";

        if ($this->getRow($checkSql, [$userId, $requestId, $type])) {
            return false; // Duplicate prevented
        }

        $sql = "INSERT INTO notifications (user_id, request_id, type, message, is_read) VALUES (?, ?, ?, ?, 0)";
        try {
            $this->insertRow($sql, [$userId, $requestId, $type, $message]);
            return true;
        } catch (Exception $e) {
            error_log("Notification Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handles email sending.
     *- @param int $userId The user to notify.
     * - @param array $details ['subject' => ..., 'body' => ...]
     */
    private function sendEmailNotification($userId, $details) {
        // Retrieve user email
        $userSql = "SELECT email, first_name FROM users WHERE user_id = ? LIMIT 1";
        $user = $this->getRow($userSql, [$userId]);

        if (!$user || empty($user['email'])) {
            return false;
        }

        $to = $user['email'];
        $subject = $details['subject'];
        $body = str_replace('[Student Name]', $user['first_name'], $details['body']);

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . $this->config['email']['from_name'] . " <" . $this->config['email']['from_address'] . ">" . "\r\n";
        $headers .= "Reply-To: " . $this->config['email']['from_address'] . "\r\n";

        try {
            // Using native mail() as a baseline for this project's environment.
            // In a production environment, PHPMailer or a professional API would be used here.
            // The @ operator keeps SMTP connection warnings out of the HTTP
            // response (which would corrupt JSON envelopes); delivery failure
            // is still detected via the return value and logged below.
            $sent = @mail($to, $subject, $body, $headers);
            if (!$sent) {
                $lastError = error_get_last();
                error_log("Email Delivery Failure: " . ($lastError['message'] ?? 'mail() returned false') . " [to: " . $to . "]");
            }
            return $sent;
        } catch (Exception $e) {
            error_log("Email Delivery Failure: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper to construct standardized email templates.
     */
    public function getTemplate($type, $data = []) {
        $templates = [
            'request_approved' => [
                'subject' => 'SPVAI Document Request Approved',
                'body' => '<p>Hello [Student Name],</p><p>Your document request <strong>' . ($data['ref'] ?? '') . '</strong> has been approved.</p><p>Document: ' . ($data['doc'] ?? '') . '</p><p>You may view your request in the SPVAI portal for more information.</p><p>Records Office<br>SPVAI</p>'
            ],
            'request_rejected' => [
                'subject' => 'SPVAI Document Request Rejected',
                'body' => '<p>Hello [Student Name],</p><p>Your document request <strong>' . ($data['ref'] ?? '') . '</strong> was rejected.</p><p><strong>Reason:</strong><br>' . ($data['remarks'] ?? 'No reason provided') . '</p><p>Records Office<br>SPVAI</p>'
            ],
            'request_processing' => [
                'subject' => 'SPVAI Document Request Processing',
                'body' => '<p>Hello [Student Name],</p><p>The Records Office is now processing your request <strong>' . ($data['ref'] ?? '') . '</strong>.</p><p>Records Office<br>SPVAI</p>'
            ],
            'request_ready' => [
                'subject' => 'SPVAI Document Ready for Pickup',
                'body' => '<p>Hello [Student Name],</p><p>Your requested document <strong>' . ($data['ref'] ?? '') . '</strong> is ready!</p>' . (isset($data['app']) ? '<p><strong>Appointment:</strong><br>' . $data['app'] . '</p>' : '') . '<p>Records Office<br>SPVAI</p>'
            ],
            'request_completed' => [
                'subject' => 'SPVAI Document Request Completed',
                'body' => '<p>Hello [Student Name],</p><p>Your document request <strong>' . ($data['ref'] ?? '') . '</strong> has been marked as completed.</p><p>Records Office<br>SPVAI</p>'
            ],
            'appointment_confirmed' => [
                'subject' => 'SPVAI Appointment Confirmed',
                'body' => '<p>Hello [Student Name],</p><p>Your appointment for request <strong>' . ($data['ref'] ?? '') . '</strong> has been confirmed.</p><p><strong>Date:</strong> ' . ($data['date'] ?? '') . '<br><strong>Time:</strong> ' . ($data['time'] ?? '') . '</p><p>Records Office<br>SPVAI</p>'
            ],
            'payment_verified' => [
                'subject' => 'SPVAI Payment Verified',
                'body' => '<p>Hello [Student Name],</p><p>Your payment for request <strong>' . ($data['ref'] ?? '') . '</strong> has been verified.</p><p><strong>Amount:</strong> ₱' . ($data['amount'] ?? '0.00') . '<br><strong>Method:</strong> ' . ($data['method'] ?? '') . '</p><p>Records Office<br>SPVAI</p>'
            ],
        ];

        return $templates[$type] ?? ['subject' => 'SPVAI Notification', 'body' => 'Message regarding your request.'];
    }
}

$notificationService = new NotificationService();
