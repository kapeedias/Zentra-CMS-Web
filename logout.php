<?php



require_once __DIR__ . '/config/config.php';   // session already started here
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/classes/User.php';

// Capture IP and User Agent
$ip = cleanIP(getClientIP());
$ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

// Validate active session
if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['user_email'])
) {
    // No active session â†’ redirect safely
    header("Location: /login.php");
    exit;
}

$userId    = $_SESSION['user_id'];
$userEmail = $_SESSION['user_email'];

// Optional: Log logout activity (SOC2 audit)
try {
    $pdo = Database::getInstance();
    $userObj = new User($pdo);

    $userObj->logActivity(
        $userId,
        "User logged out",
        "LOGOUT",
        [
            'ip'      => $ip,
            'browser' => $browser,
            'device'  => $device,
            'city'    => $geo['city'],
            'region'  => $geo['region'],
            'country' => $geo['country'],
            'geo_raw'  => $geo['raw']
        ]
    );
} catch (Exception $e) {
    // Log error but continue logout
    error_log("Logout logging failed: " . $e->getMessage());
}

// Clear all session variables
$_SESSION = [];

// Destroy session cookie securely
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect using absolute path (fixes Azure 404)
header("Location: /login.php");
exit;
