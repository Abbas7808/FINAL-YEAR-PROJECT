<?php
// Mail configuration for SMTP (Gmail example)
return [
    // SMTP host and port
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    // Your Gmail username (email) and app password
    'smtp_user' => 'your-email@gmail.com',
    'smtp_pass' => 'your-app-password',
    'smtp_from' => 'your-email@gmail.com',
    'smtp_from_name' => 'Med-Nova Notifications',
    // Verbose debug logging to file (helpful while testing)
    'debug_log' => __DIR__ . '/../mail_debug.log'
];
