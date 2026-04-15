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

                <div class="row">
                    <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Line chart</h6>
                                <div id="sparklineLine"><canvas width="150" height="50"
                                        style="display: inline-block; width: 150px; height: 50px; vertical-align: top;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Area chart</h6>
                                <div id="sparklineArea"><canvas width="150" height="50"
                                        style="display: inline-block; width: 150px; height: 50px; vertical-align: top;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Bar chart</h6>
                                <div id="sparklineBar"><canvas width="114" height="50"
                                        style="display: inline-block; width: 114px; height: 50px; vertical-align: top;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Stacked Bar chart</h6>
                                <div id="sparklineBarStacked"><canvas width="109" height="50"
                                        style="display: inline-block; width: 109px; height: 50px; vertical-align: top;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- Start Inner Footer -->
            <?php require_once('_include/inner-footer.php'); ?>
            <!-- End Inner Footer -->

        </div>
    </div>

    <?php require_once('_include/body_end_plugins.php'); ?>
</body>

</html>