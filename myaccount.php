<?php
// ==== CONFIG & DEPENDENCIES ====
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/init.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/classes/User.php';

secureSessionStart();
enforceSessionSecurity();



?>
<!DOCTYPE html>
<html lang="en">
<?php require_once('_include/head.php'); ?>

<body class="sidebar-dark">
    <div class="main-wrapper">

        <!-- Start Side Navigation -->
        <?php require_once('_include/nav_side.php'); ?>
        <!-- End Side Navigation -->

        <div class="page-wrapper">

            <!-- Start Top Navigation -->
            <?php require_once('_include/nav_top.php'); ?>
            <!-- End Top Navigation -->

            <div class="page-content">

                <?php
                /* echo htmlspecialchars($_SESSION['user_id']) . "<br />"; -->
                      echo htmlspecialchars($_SESSION['user_name']) . "<br />";
                      echo htmlspecialchars($_SESSION['user_email']) . "<br />"; */
                ?>

            </div>
            <!-- Start Inner Footer -->
            <?php require_once('_include/inner-footer.php'); ?>
            <!-- End Inner Footer -->

        </div>
    </div>

    <?php require_once('_include/body_end_plugins.php'); ?>
</body>

</html>