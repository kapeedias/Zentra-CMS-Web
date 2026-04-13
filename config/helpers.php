<?php
// helpers.php
function getClientIP(): string
{
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
function cleanIP($ip): string
{
    // If Azure sends "IP:PORT", strip the port
    if (strpos($ip, ':') !== false) {
        $parts = explode(':', $ip);
        return $parts[0];
    }
    return $ip;
}
//Initialize ip from getClientIP() for use in all scripts that include this helper
$ip = cleanIP(getClientIP());

function sanitizeInput(array $input, array $allowedFields): array
{
    $clean = [];

    foreach ($allowedFields as $field => $type) {
        $value = trim($input[$field] ?? '');

        switch ($type) {
            case 'email':
                $value = filter_var($value, FILTER_SANITIZE_EMAIL);
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Invalid email format for $field.");
                }
                break;

            case 'text':
                // Basic alphanumeric text, strip tags, prevent XSS
                $value = strip_tags($value);
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                break;

            case 'int':
                if (!ctype_digit($value)) {
                    throw new Exception("Invalid integer value for $field.");
                }
                $value = (int)$value;
                break;

            case 'password':
                // Don't sanitize passwords (to preserve special chars), just trim
                $value = $input[$field] ?? '';
                break;

            default:
                throw new Exception("Unknown validation type: $type");
        }

        $clean[$field] = $value;
    }

    return $clean;
}
function secureSessionStart(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();

        // Prevent JavaScript access to session cookie
        ini_set('session.cookie_httponly', 1);

        // Send cookie only over HTTPS
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);

        // Enforce strict session handling
        ini_set('session.use_strict_mode', 1);

        // Add SameSite policy
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
}
function isSessionHijacked(): bool
{
    // Load Azure‑safe client IP
    require_once __DIR__ . '/config.php';
    $ip = cleanIP(getClientIP());
    // $ip now contains the correct public IP from getClientIP()

    $expectedIp = $_SESSION['user_ip'] ?? null;
    $expectedAgent = $_SESSION['user_agent'] ?? null;
    // IMPORTANT: Use Azure‑safe IP, NOT REMOTE_ADDR
    $currentIp     = $ip;
    $currentAgent  = $_SERVER['HTTP_USER_AGENT'] ?? '';
    // Enforce IP check only if enabled
    if (SESSION_ENFORCE_IP_CHECK && $expectedIp !== $currentIp) {
        return true;
    }
    // Enforce User-Agent check only if enabled
    if (SESSION_ENFORCE_UA_CHECK && $expectedAgent !== $currentAgent) {
        return true;
    }
    return false;
}
function enforceSessionSecurity(): void
{
    // Load Azure‑safe client IP
    require_once __DIR__ . '/config.php';
    $ip = cleanIP(getClientIP());
    // $ip is now available
    $timeout = defined('SESSION_TIMEOUT_SECONDS') ? SESSION_TIMEOUT_SECONDS : 1800;
    $redirect = defined('SESSION_REDIRECT_ON_TIMEOUT') ? SESSION_REDIRECT_ON_TIMEOUT : 'login.php?timeout=1';

    $now = time();

    // Check for hijacking or timeout
    if (
        isSessionHijacked() ||
        (isset($_SESSION['last_activity']) && ($now - $_SESSION['last_activity']) > $timeout)
    ) {
        // Log the forced logout with user ID and IP if available
        if (isset($_SESSION['user_id'])) {
            try {
                $pdo = Database::getInstance();
                $userObj = new User($pdo);
                $reason = isSessionHijacked() ? "Session hijacking suspected" : "Session timed out";
                //$userObj->logActivity($_SESSION['user_id'], $reason, 'Forced Logout');
                $userObj->logActivity($_SESSION['user_id'], $reason, 'Forced Logout', [
                    'ip' => $ip
                ]);
            } catch (Throwable $e) {
                error_log("Session termination log failed: " . $e->getMessage());
            }
        }
        // Destroy session and redirect
        session_unset();
        session_destroy();
        header("Location: $redirect");
        exit;
    }

    // Update last activity timestamp
    $_SESSION['last_activity'] = $now;
}
/**
 * Get the user's browser user agent string.
 * Centralized here so SOC2 audit logs use consistent logic.
 */

function getUserAgent(): string
{
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}
function getBrowserName($agent)
{
    if (strpos($agent, 'Edg') !== false) return 'Microsoft Edge';
    if (strpos($agent, 'Chrome') !== false) return 'Google Chrome';
    if (strpos($agent, 'Firefox') !== false) return 'Mozilla Firefox';
    if (strpos($agent, 'Safari') !== false) return 'Safari';
    if (strpos($agent, 'OPR') !== false) return 'Opera';
    return 'Unknown Browser';
    //Usage to get the browser
    //$browser = getBrowserName($agent);
}
function getDeviceType($agent)
{
    if (preg_match('/mobile/i', $agent)) return 'Mobile';
    if (preg_match('/tablet|ipad/i', $agent)) return 'Tablet';
    return 'Desktop';
    //Usage to get the device 
    //$device = getDeviceType($agent);
}
function getGeoLocation($ip): array
{
    $raw = @file_get_contents("https://ipwho.is/{$ip}");

    if (!$raw) {
        return [
            'city'    => 'Unknown',
            'region'  => 'Unknown',
            'country' => 'Unknown',
            'raw'     => null
        ];
    }

    $data = json_decode($raw, true);

    if (!isset($data['success']) || !$data['success']) {
        return [
            'city'    => 'Unknown',
            'region'  => 'Unknown',
            'country' => 'Unknown',
            'raw'     => $raw
        ];
    }

    return [
        'city'    => $data['city'] ?? 'Unknown',
        'region'  => $data['region'] ?? 'Unknown',
        'country' => $data['country'] ?? 'Unknown',
        'raw'     => $raw
    ];
}





$agent  = getUserAgent();
$browser = getBrowserName($agent);
$device  = getDeviceType($agent);
$geo     = getGeoLocation($ip);
