<?php
// ==== CONFIG & DEPENDENCIES ====
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/init.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/classes/User.php';

// ==== SECURE SESSION START ====
secureSessionStart();

// ==== REQUEST CONTEXT (IP, AGENT, GEO, DEVICE) ====
$ip     = cleanIP(getClientIP());
$agent  = getUserAgent();
$browser = getBrowserName($agent);
$device  = getDeviceType($agent);
$geo     = getGeoLocation($ip);

// ==== ERROR DISPLAY HANDLER ====
$errors = $_SESSION['login_errors'] ?? [];
unset($_SESSION['login_errors']);

try {
    $pdo = Database::getInstance();
    $userObj = new User($pdo);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ==== RATE LIMITING CONFIG ====
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = [];
}

// Remove expired attempts
foreach ($_SESSION['login_attempts'] as $time => $recordedIp) {
    if (time() - $time > $lockoutTime) {
        unset($_SESSION['login_attempts'][$time]);
    }
}

// Count login attempts from this IP
$attempts = array_filter($_SESSION['login_attempts'], fn($v) => $v === $ip);

// ==== LOGIN HANDLER ====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (count($attempts) >= $maxAttempts) {
        $errors[] = 'Too many login attempts. Please wait before trying again.';
    }

    if (empty($errors)) {
        try {
            // === INPUT SANITIZATION ===
            $allowedFields = [
                'useremail'    => 'email',
                'userpassword' => 'password'
            ];
            $input = sanitizeInput($_POST, $allowedFields);
            $email = $input['useremail'];
            $password = $input['userpassword'];
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }

        // === reCAPTCHA VERIFICATION ===
        $recaptchaSecret = GOOGLE_RECAPTCHA_SECRET_KEY;
        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

        if (empty($recaptchaResponse)) {
            $errors[] = 'Please complete the reCAPTCHA.';
        } else {
            $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" .
                urlencode($recaptchaSecret) .
                "&response=" . urlencode($recaptchaResponse) .
                "&remoteip=" . urlencode($ip));
            $captchaResult = json_decode($verify);
            if (!$captchaResult->success) {
                $errors[] = 'reCAPTCHA verification failed.';
            }
        }

        // === DATABASE AUTH ===
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT id, first_name, pwd, approved, banned FROM zentra_users WHERE user_email = :email LIMIT 1");
                $stmt->execute(['email' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user || !password_verify($password, $user['pwd'])) {
                    $errors[] = 'Invalid email or password.';
                } elseif ((int)$user['banned'] === 1) {
                    $errors[] = 'Your account has been banned.';
                } elseif ((int)$user['approved'] !== 1) {
                    $errors[] = 'Your account has not been approved yet.';
                } else {
                    // ==== SUCCESSFUL LOGIN ====
                    foreach ($_SESSION['login_attempts'] as $time => $attemptIp) {
                        if ($attemptIp === $ip) {
                            unset($_SESSION['login_attempts'][$time]);
                        }
                    }

                    session_regenerate_id(true); // Prevent session fixation

                    $userId = (int)$user['id'];
                    $_SESSION['user_id']     = $user['id'];
                    $_SESSION['user_name']   = $user['first_name'];
                    $_SESSION['user_email']  = $email;
                    $_SESSION['login_time']  = time();
                    $_SESSION['last_activity'] = time();
                    $_SESSION['user_ip']     = $ip;
                    $_SESSION['user_agent']  = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

                    // ==== ACTIVITY LOG ====
                    $identifier = "User {$user['first_name']} ({$email}) logged in";
                    $userObj->logActivity(
                        $userId,
                        $identifier,
                        'Login',
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


                    header("Location: myaccount.php");
                    exit;
                }
            } catch (PDOException $e) {
                error_log("LOGIN ERROR: " . $e->getMessage());
                $errors[] = 'Login failed. Please try again later.';
            }
        }
    }

    // === LOG FAILED ATTEMPTS + REDIRECT ===
    if (!empty($errors)) {
        $_SESSION['login_attempts'][time()] = $ip;

        $errorText = implode(" | ", $errors);
        $safeEmail  = $email ?? 'unknown';
        $identifier = "Failed login attempt for email: {$safeEmail}";
        $userId = $user['id'] ?? null;

        $userObj->logActivity(
            $userId,
            $identifier,
            'Login Error',
            [
                'field_changed' => 'LOGIN_ATTEMPT',
                'old_value'     => 'UNAUTHENTICATED',
                'new_value'     => 'ERROR',
                'context_error' => $errorText,
                'ip'            => $ip,
                'browser'       => $browser,
                'device'        => $device,
                'city'          => $geo['city'],
                'region'        => $geo['region'],
                'country'       => $geo['country'],
                'geo_raw'       => $geo['raw']
            ]
        );

        // Save errors for display
        $_SESSION['login_errors'] = $errors;
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html data-bs-theme="light" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= getenv('APP_NAME') ?> - Login</title>
    <link rel="canonical" href="https://app.zentra.com/login.php">
    <meta property="og:url" content="https://app.zentra.com/login.php">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Atkinson+Hyperlegible&amp;display=swap">
    <link rel="stylesheet" href="assets/css/bss-overrides.css?h=b18bb4213f988d736c15b5952f0e61c3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="assets/css/styles.css?h=78e44c07cb97c0a96ff1eaa51ecfa3b3">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <section class="main-login">
        <div class="row g-0 vh-100">
            <div class="col-lg-6 col-xl-5 col-xxl-5 d-flex justify-content-center align-items-center login-col">
                <div class="justify-content-center align-items-center login-holder-main-div">
                    <div class="brand-loginpage"><img class="img-fluid brand-loginpage" width="450" height="75"
                            data-aos="fade-up" data-aos-duration="500" data-aos-delay="100"
                            src="assets/img/fleet-centra-logo-dark.png?h=c0e180cd77c29d554ef22814c9969407"
                            style="padding-bottom: 0px;margin-bottom: 35px;"></div>
                    <?php if (!empty($errors)): ?>
                        <div class="login-alert-window error">
                            <?php foreach ($errors as $err): ?>
                                <p><?= htmlspecialchars($err) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="justify-content-center align-items-center align-content-center align-self-center login-form-holder"
                        data-aos="fade-up" data-aos-duration="500" data-aos-delay="400">
                        <h4 class="d-flex justify-content-center pb-0">Sign In</h4>
                        <form class="forms-sample" method="POST" action="">
                            <div class="row">
                                <div class="col" style="margin-bottom: 15px;margin-top: 15px;">
                                    <input class="form-control" type="email" name="useremail"
                                        placeholder="yourname@work-email.com" autofocus="yes" autocomplete="off"
                                        required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col" style="margin-bottom: 15px;margin-top: 15px;"><input
                                        class="form-control" type="password" name="userpassword" id="userpassword"
                                        placeholder="**********" autocomplete="off" required></div>
                            </div>
                            <div class="row text-start">
                                <div class="col" style="margin-top: -3px;margin-bottom: -3px;">
                                    <div class="form-check"><input class="form-check-input" type="checkbox"
                                            id="formCheck-1"><label class="form-check-label" for="formCheck-1">Keep me
                                            signed in</label></div>
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
                                <div class="col" style="margin-top: 15px;margin-bottom: 15px;">
                                    <button class="btn btn-primary" type="submit">Sign In</button>
                                </div>
                            </div>
                        </form>
                        <ul class="list-inline login-form-links">
                            <li class="list-inline-item" data-aos="fade-left" data-aos-duration="200"
                                data-aos-delay="800"> <a href="#">Sign up</a></li>
                            <li class="list-inline-item" data-aos="fade-right" data-aos-duration="200"
                                data-aos-delay="800"> <a href="forgot.php">Reset Password</a></li>
                        </ul>
                    </div>
                    <div class="d-flex login-form-links-footer">
                        <ul class="list-inline">
                            <li class="list-inline-item" data-aos="fade-left" data-aos-duration="200"
                                data-aos-delay="800"> <a href="#">Privacy Policy</a></li>
                            <li class="list-inline-item" data-aos="fade-right" data-aos-duration="200"
                                data-aos-delay="800"> <a href="#">Terms of Service</a></li>
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