<?php
// -----------------------------------------------------------------------------
// reset-password.php
// -----------------------------------------------------------------------------
// This page handles password reset via a secure, single-use token.
// Flow:
// 1. User clicks link from email: reset-password.php?token=XYZ
// 2. We validate the token (exists, not expired, not used).
// 3. We validate the user's account status (not banned, approved, verified, not terminated).
// 4. If valid, we show the reset form.
// 5. On POST, we validate the new password, hash it, update the user, mark token used,
//    optionally delete other tokens, and log everything for SOC2.
// -----------------------------------------------------------------------------
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/init.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/classes/User.php';

secureSessionStart();

// -----------------------------------------------------------------------------
// Initialize variables for UI state and dependencies
// -----------------------------------------------------------------------------
$pdo          = Database::getInstance();
$errors       = [];   // Collects error messages to show in UI
$success      = [];   // Collects success messages to show in UI
$form_display = 0;    // Controls whether the form is shown (0 = hide, 1 = show)
$tokenValid   = false; // Tracks whether the token is valid and can be used

// Capture request metadata for SOC2 audit logging
$ip    = getClientIP();
$agent = getUserAgent();

// Extract token from URL
$token = $_GET['token'] ?? '';

// We'll store the reset record here if found
$reset = null;

/**
 * ------------------------------------------------------------
 * STEP 1: Validate the token
 * ------------------------------------------------------------
 * - Ensure token exists
 * - Ensure token matches a record
 * - Ensure token is not expired
 * - Ensure token has not already been used (replay protection)
 * - Ensure user account is in good standing (not banned, approved, verified, not terminated)
 */
if (!$token) {
    // No token provided at all
    $errors[]     = "Invalid password reset link.";
    $form_display = 1;
} else {
    // Look up token and associated user
    // Assumptions:
    // - zentra_password_resets: id, user_id, reset_token, expires_at, used_at
    // - zentra_users: id, user_email, banned, approved, email_verify, termination_date, termination_reason
    $stmt = $pdo->prepare(" SELECT pr.id, pr.user_id, pr.expires_at, pr.used_at, u.user_email, u.banned, 
    u.approved, u.email_verify, u.termination_date, u.termination_reason FROM zentra_password_resets pr 
    JOIN zentra_users u ON u.id = pr.user_id WHERE pr.reset_token = :token LIMIT 1");
    $stmt->execute(['token' => $token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        // Token not found or already cleaned up
        $errors[]     = "Invalid or expired reset link.";
        $form_display = 1;
    } elseif (!empty($reset['used_at'])) {
        // Token replay protection: token already used once
        $errors[]     = "This reset link has already been used.";
        $form_display = 1;
    } elseif (strtotime($reset['expires_at']) < time()) {
        // Token expired based on expires_at
        $errors[]     = "This reset link has expired.";
        $form_display = 1;
    } else {
        // ------------------------------------------------------------
        // ACCOUNT STATUS VALIDATION (SOC2 CONTROL)
        // ------------------------------------------------------------

        // If the account is banned
        if (!empty($reset['banned']) && (int)$reset['banned'] === 1) {
            $errors[]     = "This account has been banned.";
            $form_display = 1;
            $tokenValid   = false;

            $userObj = new User($pdo);
            $userObj->logActivity(
                $reset['user_id'],
                "Password reset blocked: account banned",
                "Password Reset Blocked",
                ['ip' => $ip, 'agent' => $agent]
            );
        }
        // If the account is not approved
        elseif (isset($reset['approved']) && (int)$reset['approved'] === 0) {
            $errors[]     = "This account is not approved.";
            $form_display = 1;
            $tokenValid   = false;

            $userObj = new User($pdo);
            $userObj->logActivity(
                $reset['user_id'],
                "Password reset blocked: account not approved",
                "Password Reset Blocked",
                ['ip' => $ip, 'agent' => $agent]
            );
        }
        // If email is not verified
        elseif (isset($reset['email_verify']) && (int)$reset['email_verify'] === 0) {
            $errors[]     = "Email address must be verified before resetting password.";
            $form_display = 1;
            $tokenValid   = false;

            $userObj = new User($pdo);
            $userObj->logActivity(
                $reset['user_id'],
                "Password reset blocked: email not verified",
                "Password Reset Blocked",
                ['ip' => $ip, 'agent' => $agent]
            );
        }
        // If account is terminated
        elseif (!empty($reset['termination_date'])) {
            $errors[]     = "This account has been terminated.";
            $form_display = 1;
            $tokenValid   = false;

            $userObj = new User($pdo);
            $userObj->logActivity(
                $reset['user_id'],
                "Password reset blocked: account terminated ({$reset['termination_reason']})",
                "Password Reset Blocked",
                ['ip' => $ip, 'agent' => $agent]
            );
        }
        // If all checks pass, token is valid and account is in good standing
        else {
            $tokenValid   = true;
            $form_display = 1;
        }
    }
}

/**
 * ------------------------------------------------------------
 * STEP 2: Handle password reset submission
 * ------------------------------------------------------------
 * - Only if:
 *   - Request is POST
 *   - Token is valid
 *   - We have a reset record
 * - Validate password
 * - Enforce strength rules
 * - Hash password
 * - Update user record
 * - Mark token as used (replay protection)
 * - Optionally delete other tokens
 * - Log audit event (SOC2)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid && !empty($reset)) {

    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    // Basic validation: match + minimum length
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }
    if (strlen($password) > 20) {
        $errors[] = "Password cannot exceed 20 characters.";
    }
    // Stronger server-side rules (SOC2 recommended)
    // You can adjust these rules based on your policy.
    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter and one number.";
    }

    if (empty($errors)) {

        // Hash password securely using PASSWORD_DEFAULT (future-proof)
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Update user password
        $update = $pdo->prepare("UPDATE zentra_users SET pwd = :pwd WHERE id = :uid");
        $update->execute([
            'pwd' => $hashed,
            'uid' => $reset['user_id']
        ]);

        // Mark token as used (prevents replay attacks)
        $markUsed = $pdo->prepare("
            UPDATE zentra_password_resets
            SET used_at = NOW()
            WHERE id = :id
        ");
        $markUsed->execute(['id' => $reset['id']]);

        // Optional: delete all other tokens for this user to reduce attack surface
        $deleteOthers = $pdo->prepare("DELETE FROM zentra_password_resets WHERE user_id = :uid AND id <> :id");
        $deleteOthers->execute([
            'uid' => $reset['user_id'],
            'id'  => $reset['id']
        ]);

        // SOC2 audit logging: successful password reset
        $userObj    = new User($pdo);
        $identifier = "Password reset completed for user_id {$reset['user_id']}";
        $userObj->logActivity(
            $reset['user_id'],
            $identifier,
            'Password Reset Completed',
            [
                'ip'    => $ip,
                'agent' => $agent
            ]
        );

        // Success message for UI
        $success[]    = "Your password has been reset successfully.";
        $form_display = 0;

        // Optional: redirect to login page instead of showing message
        // header("Location: login.php?reset=success");
        // exit;

    } else {
        // Log failed attempt (SOC2)
        if (!empty($reset['user_id'])) {
            $userObj    = new User($pdo);
            $identifier = "Password reset attempt failed for user_id {$reset['user_id']}";
            $userObj->logActivity(
                $reset['user_id'],
                $identifier,
                'Password Reset Error',
                [
                    'ip'     => $ip,
                    'agent'  => $agent,
                    'errors' => implode(' | ', $errors)
                ]
            );
        }
    }
}
?>

<!DOCTYPE html>
<html data-bs-theme="light" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= APP_NAME ?> - Reset Password</title>
    <link rel="canonical" href="https://app.zentra.com/reset-password.php">
    <meta property="og:url" content="https://app.zentra.com/reset-password.php">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Atkinson+Hyperlegible&amp;display=swap">
    <link rel="stylesheet" href="assets/css/bss-overrides.css?h=b18bb4213f988d736c15b5952f0e61c3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="assets/css/styles.css?h=78e44c07cb97c0a96ff1eaa51ecfa3b3">
</head>

<body>
    <section class="main-login">
        <div class="row g-0 vh-100">
            <div class="col-lg-6 col-xl-5 col-xxl-5 d-flex justify-content-center align-items-center login-col">
                <div class="justify-content-center align-items-center login-holder-main-div">
                    <div class="brand-loginpage"><img class="img-fluid brand-loginpage" width="450" height="75"
                            data-aos="fade-up" data-aos-duration="500"
                            src="assets/img/fleet-centra-logo-dark.png?h=c0e180cd77c29d554ef22814c9969407"
                            style="padding-bottom: 0px;margin-bottom: 35px;"></div>
                    <?php if (!empty($success)): ?>
                        <div class="login-alert-window success">
                            <?= implode('<br>', array_map('htmlspecialchars', $success)) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="login-alert-window error">
                            <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!$tokenValid && $form_display == 1): ?>
                        <a href="forgot.php" class="btn btn-primary mt-3">Try Forgot Password
                            Again</a>
                    <?php endif; ?>

                    <div class="justify-content-center align-items-center align-content-center align-self-center login-form-holder"
                        data-aos="fade-up" data-aos-duration="500" data-aos-delay="400">
                        <h4 class="d-flex justify-content-center pb-0">Password Reset</h4>
                        <?php if ($form_display == 1 && $tokenValid && empty($success)): ?>
                            <form class="forms-login" method="POST" action="">
                                <div class="row">
                                    <div class="col" style="margin-bottom: 15px;margin-top: 15px;">
                                        <input class="form-control" type="password" id="password" name="password"
                                            placeholder="Enter New Password" required maxlength="20" min-length="8"
                                            autocomplete="off">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col" style="margin-bottom: 15px;margin-top: 15px;"><input
                                            class="form-control" type="password" id="confirm" name="confirm"
                                            placeholder="Confirm New Password" required autocomplete="off" maxlength="20"
                                            min-length="8"></div>
                                </div>
                                <div class="row">
                                    <div class="col" style="margin-top: 15px;margin-bottom: 15px;">
                                        <div class="g-recaptcha" data-sitekey="<?= GOOGLE_RECAPTCHA_SITE_KEY; ?>"></div>
                                        <script src="https://www.google.com/recaptcha/api.js" async defer>
                                        </script>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col" style="margin-top: 15px;margin-bottom: 15px;"><button
                                            class="btn btn-primary" type="submit">Save</button></div>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex login-form-links-footer">
                        <ul class="list-inline">
                            <li class="list-inline-item" data-aos="fade-left" data-aos-duration="200"
                                data-aos-delay="800"><a href="#">Policy</a></li>
                            <li class="list-inline-item" data-aos="fade-left" data-aos-duration="200"
                                data-aos-delay="800"><a href="#">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col d-flex align-items-center login-col">
                <div data-aos="slide-right" data-aos-duration="500" data-aos-delay="400" class="login-promo">
                    <h1 class="text-white">Unified Fleet, Shipment &amp; Maintenance Hub</h1>
                </div>
            </div>
        </div>
    </section>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/4.0.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="assets/js/bs-init.js?h=d0c6de1d0ecd5065d55e7b94664b5b10"></script>
</body>

</html>