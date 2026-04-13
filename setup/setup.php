<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// =========================
// FORM
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
// RUN INSTALL
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
    } catch (Exception $e) {
        die("DB Connection failed: " . $e->getMessage());
    }

    // =========================
    // STATS BEFORE
    // =========================
    $beforeSize = getDbSize($pdo, $dbName);
    $tablesBefore = count($pdo->query("SHOW TABLES")->fetchAll());

    echo "DB Size BEFORE: {$beforeSize} MB\n";
    echo "Tables BEFORE: $tablesBefore\n\n";

    // =========================
    // FILES
    // =========================
    $files = glob(__DIR__ . "/*.sql");

    if (!$files) {
        die("No SQL files found.");
    }

    $totalSuccess = 0;
    $totalFailed = 0;

    // =========================
    // PROCESS FILES
    // =========================
    foreach ($files as $file) {

        echo "========================\n";
        echo "FILE: " . basename($file) . "\n";
        echo "========================\n";

        $sql = file_get_contents($file);

        // remove block comments
        $sql = preg_replace('!/\*.*?\*/!s', '', $sql);

        $queries = splitSQL($sql);

        foreach ($queries as $query) {

            $query = trim($query);
            if ($query === '') continue;

            $type = detectType($query);
            $table = detectTable($query);
            $time = getTime();

            echo "[{$time['local']}] $type";

            if ($table) echo " → $table";

            try {

                $pdo->exec($query);

                echo " → SUCCESS ✓\n";

                $totalSuccess++;
            } catch (Exception $e) {

                echo " → FAILED ❌\n";
                echo "   " . $e->getMessage() . "\n";

                $totalFailed++;
            }
        }
    }

    // =========================
    // FINAL STATS
    // =========================
    $afterSize = getDbSize($pdo, $dbName);
    $tablesAfter = count($pdo->query("SHOW TABLES")->fetchAll());

    $duration = round(microtime(true) - $startTime, 2);

    echo "\n========================\n";
    echo "INSTALL SUMMARY\n";
    echo "========================\n";

    echo "Success Queries: $totalSuccess\n";
    echo "Failed Queries : $totalFailed\n\n";

    echo "DB Size BEFORE: {$beforeSize} MB\n";
    echo "DB Size AFTER : {$afterSize} MB\n\n";

    echo "Tables BEFORE: $tablesBefore\n";
    echo "Tables AFTER : $tablesAfter\n\n";

    echo "Duration: {$duration}s\n";
    echo "========================\n";

    exit;
}

// =========================
// SAFE SQL SPLITTER (FIXED)
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
// TYPE DETECTOR
// =========================
function detectType($sql)
{
    $sql = strtoupper(trim($sql));

    if (str_starts_with($sql, 'DROP TABLE')) return 'DROP_TABLE';
    if (str_starts_with($sql, 'CREATE TABLE')) return 'CREATE_TABLE';
    if (str_starts_with($sql, 'TRUNCATE')) return 'TRUNCATE';
    if (str_starts_with($sql, 'INSERT')) return 'INSERT';
    if (str_starts_with($sql, 'UPDATE')) return 'UPDATE';
    if (str_starts_with($sql, 'DELETE')) return 'DELETE';
    if (str_starts_with($sql, 'ALTER')) return 'ALTER';

    return 'OTHER';
}

// =========================
// TABLE DETECTOR
// =========================
function detectTable($sql)
{
    if (preg_match('/CREATE TABLE `?([a-zA-Z0-9_]+)`?/i', $sql, $m)) return $m[1];
    if (preg_match('/DROP TABLE.*`?([a-zA-Z0-9_]+)`?/i', $sql, $m)) return $m[1];
    if (preg_match('/TRUNCATE TABLE `?([a-zA-Z0-9_]+)`?/i', $sql, $m)) return $m[1];
    if (preg_match('/INSERT INTO `?([a-zA-Z0-9_]+)`?/i', $sql, $m)) return $m[1];

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

<!-- UI -->
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