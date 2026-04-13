<?php
// File: config/config.php
date_default_timezone_set('UTC');

// Turn off displaying errors to users
ini_set('display_errors', 0);

// Enable logging
ini_set('log_errors', 1);

// Set custom log file in the current directory
ini_set('error_log', __DIR__ . '/Zentra_App_Error_log');

// Optionally set error reporting level (log everything)
error_reporting(E_ALL);


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Load flash messages from session if available
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? [];
// Immediately unset to avoid persisting messages
unset($_SESSION['errors'], $_SESSION['success']);

// App Metadata
define('APP_NAME', 'Zentra');
define('SUPPORT_EMAIL', 'support@app.livewd.ca');

// Base Domain URL - strict validation domain
$allowed_domains = [
    'app.zentra.com',
    'www.app.zentra.com',
    'zentra.azurewebsites.net',
];

$current_domain = $_SERVER['HTTP_HOST'] ?? '';
$domain_clean = explode(':', $current_domain)[0];

if (!in_array($domain_clean, $allowed_domains, true)) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied. Unauthorized domain.');
}

// Correct BASE_URL definition
define('BASE_URL', (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $domain_clean);
define('SITE_URL', BASE_URL . '/');

define('APP_ROOT', dirname(__DIR__));

// Login/Reset URL (can be routed to actual files)
define('LOGIN_URL', SITE_URL . 'login.php');
define('RESET_URL', SITE_URL . 'reset_password.php');
define('SESSION_TIMEOUT_SECONDS', 1800); // 30 minutes
define('SESSION_ENFORCE_IP_CHECK', true);
define('SESSION_ENFORCE_UA_CHECK', true);
define('SESSION_REDIRECT_ON_TIMEOUT', 'login.php?timeout=1');

// SendGrid settings - AZURE
define('SENDGRID_API_KEY', getenv('SENDGRID_API_KEY'));
define('SENDGRID_SENDER_EMAIL', getenv('SENDGRID_SENDER_EMAIL'));
define('SENDGRID_SENDER_NAME', getenv('SENDGRID_SENDER_NAME'));
define('SMTP_HOST', getenv('SENDGRID_SMTP_HOST'));
define('SMTP_USER', getenv('SENDGRID_SMTP_USER'));
define('SMTP_PASS', getenv('SENDGRID_SMTP_PASS'));
define('SMTP_PORT', getenv('SENDGRID_SMTP_PORT'));
define('EMAIL_FROM', getenv('SENDGRID_SENDER_EMAIL'));
define('EMAIL_FROM_NAME', getenv('SENDGRID_SENDER_NAME'));

//Mailjet Settings - EMAIL API - AZURE
define('MAILJET_API_KEY', getenv('MAILJET_API_KEY'));
define('MAILJET_SECRET_KEY', getenv('MAILJET_SECRET_KEY'));
define('MAILJET_FROM_EMAIL', getenv('MAILJET_FROM_EMAIL'));
define('MAILJET_FROM_NAME', getenv('MAILJET_FROM_NAME'));

// Twilio SMS/WhatsApp settings - AZURE
define('TWILIO_ACCOUNT_SID', getenv('TWILIO_ACCOUNT_SID'));
define('TWILIO_AUTH_TOKEN', getenv('TWILIO_AUTH_TOKEN'));
define('TWILIO_SMS_FROM', getenv('TWILIO_SMS_FROM'));
define('TWILIO_WHATSAPP_FROM', getenv('TWILIO_WHATSAPP_FROM'));

// Google APIs - AZURE
define('GOOGLE_MAPS_API_KEY', getenv('GOOGLE_MAPS_API_KEY'));
define('GOOGLE_CALENDAR_CLIENT_ID', getenv('GOOGLE_CALENDAR_CLIENT_ID'));
define('GOOGLE_CALENDAR_CLIENT_SECRET', getenv('GOOGLE_CALENDAR_CLIENT_SECRET'));
define('GOOGLE_CALENDAR_REDIRECT_URI', getenv('GOOGLE_CALENDAR_REDIRECT_URI'));
define('GOOGLE_RECAPTCHA_SITE_KEY', getenv('GOOGLE_RECAPTCHA_SITE_KEY'));
define('GOOGLE_RECAPTCHA_SECRET_KEY', getenv('GOOGLE_RECAPTCHA_SECRET_KEY'));

// Token Expiration
define('TOKEN_EXPIRY_MINUTES', 30);
define('REMEMBER_ME_EXPIRY_DAYS', 7);

// Default User Info
define('DEFAULT_COUNTRY', 'Canada');

// Password Settings
define('PASSWORD_MIN_LENGTH', getenv('PASSWORD_MIN_LENGTH') ?: 8); // minimum length requirement
define('PASSWORD_MAX_LENGTH', getenv('PASSWORD_MAX_LENGTH') ?: 20);  // optional max length, or you can skip it

// CDN Integration
define('USE_CDN', getenv('USE_CDN') === 'true'); // Toggle CDN usage

// CDN Base URLs
define('CDN_AWS_URL', getenv('CDN_AWS_URL'));
define('CDN_AZURE_URL', getenv('CDN_AZURE_URL'));
define('CDN_GCP_URL', getenv('CDN_GCP_URL'));

// Select active CDN
// Options: AWS, AZURE, GCP
define('ACTIVE_CDN', getenv('ACTIVE_CDN') ?: 'AWS');

// Return active CDN base URL
function getCdnBaseUrl()
{
    switch (ACTIVE_CDN) {
        case 'AWS':
            return CDN_AWS_URL;
        case 'AZURE':
            return CDN_AZURE_URL;
        case 'GCP':
            return CDN_GCP_URL;
        default:
            return SITE_URL . 'assets';
    }
}

// Helper to get full path to CDN file
function cdn_asset($path)
{
    return rtrim(getCdnBaseUrl(), '/') . '/' . ltrim($path, '/');
}
// Basic password complexity check
function validatePasswordComplexity($password)
{
    $errs = [];
    if (strlen($password) < PASSWORD_MIN_LENGTH) $errs[] = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters.";
    if (defined('PASSWORD_MAX_LENGTH') && strlen($password) > PASSWORD_MAX_LENGTH) $errs[] = "Password must not exceed " . PASSWORD_MAX_LENGTH . " characters.";
    if (!preg_match('/[A-Z]/', $password)) $errs[] = "Password must include an uppercase letter.";
    if (!preg_match('/[a-z]/', $password)) $errs[] = "Password must include a lowercase letter.";
    if (!preg_match('/[0-9]/', $password)) $errs[] = "Password must include a number.";
    if (!preg_match('/[\W_]/', $password)) $errs[] = "Password must include a special character.";
    //if (preg_match('/(.)\\1/', $password)) $errs[] = "Password must not contain repeated characters next to each other.";
    return empty($errs) ? true : $errs;
}


function generatePassword(int $length = 20, string $complexity = 'strong', string $customChars = ''): string
{
    $charSets = [
        'lowercase' => 'abcdefghijklmnopqrstuvwxyz',
        'uppercase' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'digits'    => '0123456789',
        'symbols'   => '!@#$%^&*()-_=+[]{}<>?/|~',
    ];

    // Define complexity options
    switch ($complexity) {
        case 'low':
            $chars = $charSets['lowercase'];
            break;
        case 'medium':
            $chars = $charSets['lowercase'] . $charSets['digits'];
            break;
        case 'high':
            $chars = $charSets['lowercase'] . $charSets['uppercase'] . $charSets['digits'];
            break;
        case 'strong':
        default:
            $chars = implode('', $charSets);
            break;
    }

    // Override with custom characters if provided
    if (!empty($customChars)) {
        $chars = $customChars;
    }

    // Shuffle and build password
    $password = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, $max)];
    }

    return $password;
}

function logAppError($exception)
{
    $logFile = __DIR__ . '/Zentra_App_Error_log';
    $errorMessage = "[" . date('Y-m-d H:i:s') . "] " . $exception->getMessage() . "\n";
    file_put_contents($logFile, $errorMessage, FILE_APPEND);
}


$maxAttempts = 5;
$lockoutTime = 15 * 60;
