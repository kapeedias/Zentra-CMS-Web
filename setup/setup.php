<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// =========================
// FORM SUBMIT
// =========================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $_SESSION['db_host'] = $_POST['db_host'];
    $_SESSION['db_name'] = $_POST['db_name'];
    $_SESSION['db_user'] = $_POST['db_user'];
    $_SESSION['db_pass'] = $_POST['db_pass'];

    header("Location: setup.php?run=1");
    exit;
}

// =========================
// RUN INSTALLER
// =========================
if (isset($_GET['run'])) {

    echo "<h2>Running Installation...</h2><pre>";

    $startTime = microtime(true);

    try {
        $pdo = new PDO(
            "mysql:host={$_SESSION['db_host']};charset=utf8mb4",
            $_SESSION['db_user'],
            $_SESSION['db_pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        );

        $dbName = $_SESSION['db_name'];

        echo "DB Connected ✓\n";

        // Create DB if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
        $pdo->exec("USE `$dbName`");
    } catch (Exception $e) {
        die("DB Connection failed: " . $e->getMessage());
    }

    // =========================
    // CREATE LOG TABLE (SAFE)
    // =========================
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS install_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            action_type VARCHAR(50),
            table_name VARCHAR(255),
            status VARCHAR(20),
            message TEXT,
            query_text LONGTEXT,
            utc_time DATETIME,
            local_time DATETIME,
            `timezone` VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // =========================
    // DB STATS BEFORE
    // =========================
    $beforeSize = getDbSize($pdo, $dbName);
    $tablesBefore = count($pdo->query("SHOW TABLES")->fetchAll());

    echo "DB Size BEFORE: {$beforeSize} MB\n";
    echo "Tables BEFORE: $tablesBefore\n\n";

    // =========================
    // SQL FILES
    // =========================
    $files = glob(__DIR__ . "/*.sql");

    if (!$files) {
        die("No SQL files found.");
    }

    $success = 0;
    $failed = 0;

    foreach ($files as $file) {

        echo "========================\n";
        echo "FILE: " . basename($file) . "\n";
        echo "========================\n";

        $sql = file_get_contents($file);

        // remove block comments safely
        $sql = preg_replace('!/\*.*?\*/!s', '', $sql);

        // 🔥 CRITICAL FIX: USE MYSQL MULTI QUERY (NO SPLITTING)
        try {

            if ($pdo->exec($sql) !== false) {
                echo "FILE EXECUTED SUCCESSFULLY ✓\n";
                $success++;
            }
        } catch (Exception $e) {

            echo "FILE FAILED ❌\n";
            echo $e->getMessage() . "\n\n";
            $failed++;
        }
    }

    // =========================
    // DB STATS AFTER
    // =========================
    $afterSize = getDbSize($pdo, $dbName);
    $tablesAfter = count($pdo->query("SHOW TABLES")->fetchAll());

    $duration = round(microtime(true) - $startTime, 2);

    echo "\n========================\n";
    echo "INSTALL SUMMARY\n";
    echo "========================\n";

    echo "Success Files: $success\n";
    echo "Failed Files: $failed\n\n";

    echo "DB Size BEFORE: {$beforeSize} MB\n";
    echo "DB Size AFTER : {$afterSize} MB\n\n";

    echo "Tables BEFORE: $tablesBefore\n";
    echo "Tables AFTER : $tablesAfter\n\n";

    echo "Duration: {$duration}s\n";
    echo "========================\n";

    exit;
}

// =========================
// DB SIZE FUNCTION
// =========================
function getDbSize($pdo, $dbName)
{
    $stmt = $pdo->query("
        SELECT SUM(data_length + index_length) / 1024 / 1024 AS size_mb
        FROM information_schema.tables
        WHERE table_schema = '$dbName'
    ");

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return round($row['size_mb'] ?? 0, 2);
}
?>

<!-- =========================
     SIMPLE UI
========================= -->
<!DOCTYPE html>
<html>

<head>
    <title>Setup Installer</title>
</head>

<body>

    <h2>Database Setup</h2>

    <form method="POST">
        <input name="db_host" placeholder="Host" required><br><br>
        <input name="db_name" placeholder="Database Name" required><br><br>
        <input name="db_user" placeholder="User" required><br><br>
        <input name="db_pass" placeholder="Password" type="password"><br><br>
        <button>Install</button>
    </form>

</body>

</html>