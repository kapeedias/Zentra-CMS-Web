<?php
// ==== CONFIG & DEPENDENCIES ====
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Mailer.php';

// ==== SECURE SESSION START ====
secureSessionStart();

// ==== INITIALIZE ====
$errors = $_SESSION['forgot_errors'] ?? [];
unset($_SESSION['forgot_errors']);
$success = $_SESSION['forgot_success'] ?? null;
unset($_SESSION['forgot_success']);

try {
    $pdo = Database::getInstance();
    $userObj = new User($pdo);
    $mailer = new Mailer();
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

// ==== RATE LIMITING CONFIG ====
if (!isset($_SESSION['forgot_attempts'])) {
    $_SESSION['forgot_attempts'] = [];
}

// Remove expired attempts
foreach ($_SESSION['forgot_attempts'] as $time => $recordedIp) {
    if (time() - $time > $lockoutTime) {
        unset($_SESSION['forgot_attempts'][$time]);
    }
}

// Count attempts from this IP
$attempts = array_filter($_SESSION['forgot_attempts'], fn($v) => $v === $ip);

// ==== FORM SUBMISSION HANDLER ====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $success = [];
    if (count($attempts) >= $maxAttempts) {
        $errors[] = 'Too many password reset requests from your IP. Please wait and try again later.';
    }
    // Trim email before sanitization
    $_POST['useremail'] = trim($_POST['useremail'] ?? '');

    // Sanitize and validate input
    $allowedFields = ['useremail' => 'email'];
    try {
        $input = sanitizeInput($_POST, $allowedFields);
        $email = $input['useremail'];
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }

    // reCAPTCHA verification
    $recaptchaSecret = GOOGLE_RECAPTCHA_SECRET_KEY;
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    if (empty($recaptchaResponse)) {
        $errors[] = 'Please complete the reCAPTCHA.';
    } else {
        $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?" . http_build_query([
            'secret' => $recaptchaSecret,
            'response' => $recaptchaResponse,
            'remoteip' => $ip
        ]));
        $captchaResult = json_decode($verify);
        if (!$captchaResult->success) {
            $errors[] = 'reCAPTCHA verification failed.';
        }
    }

    if (empty($errors)) {
        try {
            // Check if user exists and is approved & not banned
            $stmt = $pdo->prepare("SELECT id, first_name, user_email, approved, banned FROM fleetcentra_users WHERE user_email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                // To prevent user enumeration, do NOT reveal this directly
                $errors[] = "We have successfully got your password reset request. If our system finds an account with the email provided, a reset link will be sent to the same email address shortly.";
            } elseif ((int)$user['banned'] === 1) {
                $errors[] = 'This account has been banned.';
            } elseif ((int)$user['approved'] !== 1) {
                $errors[] = 'This account has not been approved yet.';
            } else {
                // Generate token and expiry (1 hour validity)
                $token = bin2hex(random_bytes(16));
                $expires = date('Y-m-d H:i:s', time() + 3600);

                // Store token and expiry in your password resets table
                $insert = $pdo->prepare("INSERT INTO fleetcentra_password_resets (user_id, reset_token, expires_at) VALUES (:uid, :token, :expires)");
                $insert->execute([
                    'uid' => $user['id'],
                    'token' => $token,
                    'expires' => $expires
                ]);

                // Send reset email
                $mailSent = $mailer->sendResetPasswordEmail($user['user_email'], $user['first_name'], $token);

                if ($mailSent) {
                    $success[] = "A password reset link has been sent to your email.";
                    $identifier = "Password reset requested for user {$user['user_email']}";
                    $userObj->logActivity($user['id'], $identifier, 'Password Reset Requested');
                } else {
                    $errors[] = "Failed to send reset email. Please try again later.";
                }
            }
        } catch (PDOException $e) {
            error_log("FORGOT PASSWORD ERROR: " . $e->getMessage());
            $errors[] = 'An error occurred. Please try again later.';
        }
    }

    // Log attempts and errors
    if (!empty($errors)) {
        $_SESSION['forgot_attempts'][time()] = $ip;
        $identifier = "Failed password reset request for email: " . ($email ?? '[unknown]');
        $userId = $user['id'] ?? null;
        $userObj->logActivity(
            $userId,
            $identifier,
            'Password Reset Error',
            ['context_error' => implode(" | ", $errors)]
        );
    }

    // Save errors or success message for display and redirect to self
    $_SESSION['forgot_errors'] = $errors;
    $_SESSION['forgot_success'] = $success ?? null;
    header("Location: forgot.php");
    exit;
}
?>


<!DOCTYPE html>
<html data-bs-theme="light" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= APP_NAME ?> - Login</title>
    <link rel="canonical" href="https://app.fleetcentra.com/forgot.php">
    <meta property="og:url" content="https://app.fleetcentra.com/forgot.php">
    <script type="application/ld+json">
        {
            "@context": "http://schema.org",
            "@type": "WebSite",
            "name": "FleetCentra",
            "url": "https://app.fleetcentra.com"
        }
    </script>
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
                            data-aos="fade-up" data-aos-duration="250"
                            src="assets/img/fleet-centra-logo-dark.png?h=c0e180cd77c29d554ef22814c9969407"
                            style="padding-bottom: 0px;margin-bottom: 35px;"></div>
                    <?php if (!empty($success)): ?>
                        <div class="login-alert-window success">
                            <?= implode('<br>', $success) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="login-alert-window error">
                            <?= implode('<br>', $errors) ?>
                        </div>
                    <?php endif; ?>
                    <div class="justify-content-center align-items-center align-content-center align-self-center login-form-holder"
                        data-aos="fade-up" data-aos-duration="500" data-aos-delay="400">
                        <h4 class="d-flex justify-content-center pb-0">Reset Password</h4>
                        <form class="forms-pwd-reset" method="POST" action="forgot.php">
                            <div class="row">
                                <div class="col" style="margin-bottom: 15px;margin-top: 15px;"><input
                                        class="form-control" type="email" id="useremail" name="useremail"
                                        placeholder="yourname@work-email.com" autofocus="" autocomplete="off" required>
                                </div>
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
                                        class="btn btn-primary" type="submit">Reset</button></div>
                            </div>
                        </form>
                        <ul class="list-inline login-form-links">
                            <li class="list-inline-item" data-aos="fade-left" data-aos-duration="200" data-aos-delay="800"><a href="login.php">Sign In</a></li>
                        </ul>
                    </div>
                    <div class="d-flex login-form-links-footer">
                        <ul class="list-inline">
                            <li class="list-inline-item" data-aos="fade-left" data-aos-duration="200" data-aos-delay="800"><a href="#">Privacy Policy</a></li>
                            <li class="list-inline-item" data-aos="fade-left" data-aos-duration="200" data-aos-delay="800"><a href="#">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col d-flex align-items-center login-col">
                <div data-aos="slide-right" data-aos-duration="500" class="login-promo">
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