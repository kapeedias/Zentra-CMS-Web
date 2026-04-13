<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// =========================
// FORM HANDLER
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
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $dbName = $_SESSION['db_name'];

        echo "DB Connected ✓\n";

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
        $pdo->exec("USE `$dbName`");

        // 🔥 IMPORTANT: disable FK checks during install
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    } catch (Exception $e) {
        die("DB Connection failed: " . $e->getMessage());
    }

    // =========================
    // DB STATS BEFORE
    // =========================
    $beforeSize = getDbSize($pdo, $dbName);
    $tablesBefore = count($pdo->query("SHOW TABLES")->fetchAll());

    echo "DB Size BEFORE: {$beforeSize} MB\n";
    echo "Tables BEFORE: $tablesBefore\n\n";

    // =========================
    // LOAD SQL FILES
    // =========================
    $files = glob(__DIR__ . "/*.sql");

    if (!$files) {
        die("No SQL files found.");
    }

    $success = 0;
    $failed = 0;

    // =========================
    // PROCESS FILES
    // =========================
    foreach ($files as $file) {

        echo "========================\n";
        echo "FILE: " . basename($file) . "\n";
        echo "========================\n";

        $sql = file_get_contents($file);

        // remove comments safely
        $sql = preg_replace('!/\*.*?\*/!s', '', $sql);

        // split safely (handles multi-statements)
        $queries = splitSQL($sql);

        foreach ($queries as $query) {

            $query = trim($query);
            if ($query === '') continue;

            $type = detectType($query);
            $table = detectTable($query);
            $time = getTime();

            echo "[{$time['local']}] $type";

            if ($table) {
                echo " → $table";
            }

            try {

                $pdo->exec($query);

                echo " → SUCCESS ✓\n";

                $success++;
            } catch (Exception $e) {

                echo " → FAILED ❌\n";
                echo "   " . $e->getMessage() . "\n";

                $failed++;
            }
        }
    }

    // re-enable FK checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // =========================
    // DB STATS AFTER
    // =========================
    $afterSize = getDbSize($pdo, $dbName);
    $tablesAfter = count($pdo->query("SHOW TABLES")->fetchAll());

    $duration = round(microtime(true) - $startTime, 2);

    echo "\n========================\n";
    echo "INSTALL SUMMARY\n";
    echo "========================\n";

    echo "Success Queries: $success\n";
    echo "Failed Queries : $failed\n\n";

    echo "DB Size BEFORE: {$beforeSize} MB\n";
    echo "DB Size AFTER : {$afterSize} MB\n\n";

    echo "Tables BEFORE: $tablesBefore\n";
    echo "Tables AFTER : $tablesAfter\n\n";

    echo "Duration: {$duration}s\n";
    echo "========================\n";

    exit;
}

// =========================
// SAFE SQL SPLITTER
// =========================
function splitSQL($sql)
{
    $statements = [];
    $buffer = '';
    $inString = false;
    $quote = '';

    $len = strlen($sql);

    for ($i = 0; $i < $len; $i++) {

        $char = $sql[$i];

        if (($char === "'" || $char === '"') && ($i === 0 || $sql[$i - 1] !== '\\')) {
            if (!$inString) {
                $inString = true;
                $quote = $char;
            } elseif ($quote === $char) {
                $inString = false;
            }
        }

        if ($char === ';' && !$inString) {
            $statements[] = $buffer;
            $buffer = '';
        } else {
            $buffer .= $char;
        }
    }

    if (trim($buffer) !== '') {
        $statements[] = $buffer;
    }

    return $statements;
}

// =========================
// TYPE DETECTOR (SAFE)
// =========================
function detectType($sql)
{
    $sql = strtoupper(trim($sql));

    if (strpos($sql, 'DROP TABLE') === 0) return 'DROP_TABLE';
    if (strpos($sql, 'CREATE TABLE') === 0) return 'CREATE_TABLE';
    if (strpos($sql, 'TRUNCATE') === 0) return 'TRUNCATE';
    if (strpos($sql, 'INSERT') === 0) return 'INSERT';
    if (strpos($sql, 'UPDATE') === 0) return 'UPDATE';
    if (strpos($sql, 'DELETE') === 0) return 'DELETE';
    if (strpos($sql, 'ALTER') === 0) return 'ALTER';

    return 'OTHER';
}

// =========================
// TABLE DETECTOR (FIXED - NO GARBAGE LIKE "g")
// =========================
function detectTable($sql)
{
    $sql = trim($sql);

    if (preg_match('/DROP TABLE (IF EXISTS )?`?([a-zA-Z0-9_]+)`?/i', $sql, $m)) {
        return $m[2];
    }

    if (preg_match('/CREATE TABLE (IF NOT EXISTS )?`?([a-zA-Z0-9_]+)`?/i', $sql, $m)) {
        return $m[2];
    }

    if (preg_match('/TRUNCATE TABLE `?([a-zA-Z0-9_]+)`?/i', $sql, $m)) {
        return $m[1];
    }

    if (preg_match('/INSERT INTO `?([a-zA-Z0-9_]+)`?/i', $sql, $m)) {
        return $m[1];
    }

    if (preg_match('/UPDATE `?([a-zA-Z0-9_]+)`?/i', $sql, $m)) {
        return $m[1];
    }

    if (preg_match('/DELETE FROM `?([a-zA-Z0-9_]+)`?/i', $sql, $m)) {
        return $m[1];
    }

    return null;
}

// =========================
// TIME
// =========================
function getTime()
{
    $tz = date_default_timezone_get();
    $dt = new DateTime("now", new DateTimeZone($tz));

    return [
        'utc' => gmdate("Y-m-d H:i:s"),
        'local' => $dt->format("Y-m-d H:i:s"),
        'tz' => $tz
    ];
}

// =========================
// DB SIZE
// =========================
function getDbSize($pdo, $dbName)
{
    $stmt = $pdo->query("
        SELECT SUM(data_length + index_length)/1024/1024 AS size_mb
        FROM information_schema.tables
        WHERE table_schema = '$dbName'
    ");

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return round($row['size_mb'] ?? 0, 2);
}
?>

<!-- =========================
     UI
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
        <input name="db_name" placeholder="DB Name" required><br><br>
        <input name="db_user" placeholder="User" required><br><br>
        <input name="db_pass" placeholder="Password" type="password"><br><br>
        <button>Install</button>
    </form>

</body>

</html>