<?php
require_once(__DIR__ . '/../database/Database.php');

class Auth extends Database {

    public function __construct() {
        parent::__construct();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Generates and stores a CSRF token in the session.
     * @return string The generated token.
     */
    public function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validates a provided CSRF token against the session token.
     * @param string $token The token to validate.
     * @return bool True if valid, false otherwise.
     */
    public function validateCsrfToken($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Checks if a user is currently logged in.
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Gets the currently authenticated user from the database.
     * @return array|null The user record or null if not logged in.
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $sql = "SELECT user_id, student_id, first_name, last_name, email, phone, role
                FROM users
                WHERE user_id = ? LIMIT 1";

        return $this->getRow($sql, [$_SESSION['user_id']]);
    }

    /**
     * Ensures the user is logged in; otherwise redirects to login.
     * @param string $redirectTo Default redirect page.
     */
    public function requireLogin($redirectTo = 'login.php') {
        if (!$this->isLoggedIn()) {
            header("Location: $redirectTo");
            exit();
        }
    }

    /**
     * Ensures the user has a specific role.
     * @param string $role The required role (e.g., 'admin', 'student').
     * @param string $redirectTo Redirect page on failure.
     */
    public function requireRole($role, $redirectTo = 'login.php') {
        $this->requireLogin($redirectTo);
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
            // Log unauthorized access attempt if needed
            header("Location: $redirectTo?error=unauthorized");
            exit();
        }
    }

    /**
     * Authenticates a user by email and password.
     * @param string $email
     * @param string $password
     * @return array|null User record on success, null on failure.
     */
    public function authenticate($email, $password) {
        $sql = "SELECT user_id, password_hash, role FROM users WHERE email = ? LIMIT 1";
        $user = $this->getRow($sql, [$email]);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];

            return $user;
        }

        return null;
    }

    /**
     * Destroys the current authenticated session.
     */
    public function logout() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}

$auth = new Auth();
