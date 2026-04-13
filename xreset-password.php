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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= APP_NAME ?> - Reset Password</title>
    <!-- core:css -->
    <link rel="stylesheet" href="assets/vendors/core/core.css">
    <!-- endinject -->
    <!-- plugin css for this page -->
    <!-- end plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="assets/fonts/feather-font/css/iconfont.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icon.min.css">
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/images/favicon.png" />
</head>

<body class="sidebar-dark">
    <div class="main-wrapper">
        <div class="page-wrapper full-page">
            <div class="page-content d-flex align-items-center justify-content-center">
                <div class="row w-100 mx-0 auth-page">
                    <div class="col-lg-4 mx-auto">
                        <div class="card">
                            <div class="row">
                                <div class="col">
                                    <div class="auth-form-wrapper px-4 py-5">
                                        <a href="#" class="sidebar-brand">
                                            <img src="assets/images/fleet-centra-logo-dark.png"
                                                class="img-responsive-brand text-center">
                                        </a>
                                        <hr />
                                        <h4 class="text-center">Reset Password</h4>
                                        <hr />
                                        <?php if (!empty($success)): ?>
                                            <div class="alert alert-success">
                                                <?= implode('<br>', array_map('htmlspecialchars', $success)) ?>
                                            </div>
                                            <a href="login.php" class="btn btn-primary text-white mt-3">Login Now</a>
                                        <?php endif; ?>

                                        <?php if (!empty($errors)): ?>
                                            <div class="alert alert-danger">
                                                <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
                                            </div>

                                        <?php endif; ?>

                                        <?php if (!$tokenValid && $form_display == 1): ?>
                                            <a href="forgot.php" class="btn btn-secondary mt-3">Try Forgot Password
                                                Again</a>
                                        <?php endif; ?>

                                        <!-- Show form only when allowed -->
                                        <?php if ($form_display == 1 && $tokenValid && empty($success)): ?>
                                            <form class="forms-sample" method="POST" action="">

                                                <div class="form-group">
                                                    <label for="NewPassword">New Password</label>
                                                    <input type="password" class="form-control" id="password"
                                                        name="password" placeholder="New Password" required maxlength="20">
                                                </div>
                                                <div class="form-group">
                                                    <label for="ConfirmNewPassword">Confirm New Password</label>
                                                    <input type="password" class="form-control" id="confirm" name="confirm"
                                                        placeholder="Confirm New Password" required maxlength="20">
                                                </div>

                                                <div class="mt-3">
                                                    <div class="g-recaptcha"
                                                        data-sitekey="<?= GOOGLE_RECAPTCHA_SITE_KEY; ?>"></div>
                                                    <script src="https://www.google.com/recaptcha/api.js" async defer>
                                                    </script>

                                                    <button type="submit"
                                                        class="btn btn-primary mr-2 mb-2 mb-md-0 mt-3 text-white">Reset
                                                        Password</button>

                                                </div>
                                                <a href="login.php" class="d-block mt-3 text-right text-muted">Login Now
                                                </a>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- core:js -->
    <script src="assets/vendors/core/core.js"></script>
    <script src="assets/vendors/feather-icons/feather.min.js"></script>
    <script src="assets/js/template.js"></script>
    <!-- Simple password strength meter (front-end UX only) -->
    <!-- Simple password strength meter (front-end UX only) -->
    <script>
        const passwordInput = document.getElementById('password');
        const strengthText = document.getElementById('password-strength-text');

        function evaluateStrength(pwd) {
            let score = 0;

            // Enforce max length of 20 characters
            if (pwd.length > 20) {
                return 'Password cannot exceed 20 characters';
            }

            if (pwd.length >= 8) score++;
            if (/[A-Z]/.test(pwd)) score++;
            if (/[0-9]/.test(pwd)) score++;
            if (/[^A-Za-z0-9]/.test(pwd)) score++;

            if (!pwd) return '';
            if (score <= 1) return 'Weak password';
            if (score === 2) return 'Medium strength password';
            if (score >= 3) return 'Strong password';
        }

        if (passwordInput && strengthText) {
            passwordInput.addEventListener('input', function() {
                const msg = evaluateStrength(this.value);
                strengthText.textContent = msg;
            });
        }
    </script>
</body>

</html>