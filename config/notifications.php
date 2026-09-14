<?php
/**
 * SPVAI Notification Configuration
 * Use environment variables or a safe config file for sensitive data.
 */
return [
    'email' => [
        'host' => 'smtp.example.com',
        'port' => 587,
        'username' => 'notifications@spvai.edu.ph',
        'password' => 'your-secure-password',
        'from_address' => 'notifications@spvai.edu.ph',
        'from_name' => 'SPVAI Records Office',
        'encryption' => 'tls', // 'tls' or 'ssl'
    ],
    'notifications' => [
        'max_history' => 100, // Max notifications per user before archival
    ],
];
