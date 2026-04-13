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

    // =========================
    // CONNECT (CREATE DB FIRST)
    // =========================
    try {
        $pdo = new PDO(
            "mysql:host={$_SESSION['db_host']};charset=utf8mb4",
            $_SESSION['db_user'],
            $_SESSION['db_pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $dbName = $_SESSION['db_name'];

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
        $pdo->exec("USE `$dbName`");
    } catch (Exception $e) {
        die("DB Connection failed: " . $e->getMessage());
    }

    echo "DB Connected ✓\n\n";

    // =========================
    // CREATE INSTALL LOG TABLE (FIXED)
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

        echo "FILE: " . basename($file) . "\n";
        echo "-------------------------\n";

        $sql = file_get_contents($file);

        // remove block comments safely
        $sql = preg_replace('!/\*.*?\*/!s', '', $sql);

        $queries = parseSQL($sql);

        foreach ($queries as $query) {

            $query = trim($query);
            if ($query === '') continue;

            $type = getActionType($query);
            $table = getTableName($query);
            $time = getTimeData();

            try {

                // INSERT tracking start
                if ($type === 'INSERT') {
                    logAudit($pdo, "INSERT_START", $table, "Insert started", $query);
                }

                $pdo->exec($query);

                // ACTION LOGS
                if ($type === 'CREATE_TABLE') {
                    logAudit($pdo, $type, $table, "Table created", $query);
                } elseif ($type === 'TRUNCATE') {
                    logAudit($pdo, $type, $table, "Table truncated", $query);
                } elseif ($type === 'INSERT') {
                    logAudit($pdo, "INSERT_END", $table, "Insert finished", $query);
                } else {
                    logAudit($pdo, $type, $table, "Executed", $query);
                }

                echo "[{$time['local']}] $type → SUCCESS ✓ ($table)\n";

                $success++;
            } catch (Exception $e) {

                logAudit($pdo, $type, $table, $e->getMessage(), $query);

                echo "[{$time['local']}] $type → FAILED ❌ ($table)\n";
                echo $e->getMessage() . "\n\n";

                $failed++;
            }
        }
    }

    // =========================
    // DB STATS AFTER
    // =========================
    $afterSize = getDbSize($pdo, $dbName);
    $tablesAfter = count($pdo->query("SHOW TABLES")->fetchAll());

    $duration = round(microtime(true) - $startTime, 2);

    // =========================
    // SUMMARY
    // =========================
    echo "\n========================\n";
    echo "INSTALL SUMMARY\n";
    echo "========================\n";

    echo "Success: $success\n";
    echo "Failed: $failed\n\n";

    echo "DB Size BEFORE: {$beforeSize} MB\n";
    echo "DB Size AFTER : {$afterSize} MB\n\n";

    echo "Tables BEFORE: $tablesBefore\n";
    echo "Tables AFTER : $tablesAfter\n\n";

    echo "Duration: {$duration}s\n";
    echo "========================\n";

    exit;
}

// =========================
// ACTION TYPE DETECTOR
// =========================
function getActionType($sql)
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
// TABLE NAME DETECTOR
// =========================
function getTableName($sql)
{
    if (preg_match('/CREATE TABLE `?([a-zA-Z0-9_]+)`?/i', $sql, $m)) return $m[1];
    if (preg_match('/DROP TABLE.*`?([a-zA-Z0-9_]+)`?/i', $sql, $m)) return $m[1];
    if (preg_match('/TRUNCATE TABLE `?([a-zA-Z0-9_]+)`?/i', $sql, $m)) return $m[1];
    if (preg_match('/INSERT INTO `?([a-zA-Z0-9_]+)`?/i', $sql, $m)) return $m[1];
    return null;
}

// =========================
// TIME (UTC + LOCAL + TZ)
// =========================
function getTimeData()
{
    $tz = date_default_timezone_get();
    $dt = new DateTime("now", new DateTimeZone($tz));

    return [
        'utc' => gmdate("Y-m-d H:i:s"),
        'local' => $dt->format("Y-m-d H:i:s"),
        'timezone' => $tz
    ];
}

// =========================
// AUDIT LOGGER
// =========================
function logAudit($pdo, $type, $table, $message, $query)
{
    $time = getTimeData();

    $stmt = $pdo->prepare("
        INSERT INTO install_log
        (action_type, table_name, status, message, query_text,
         utc_time, local_time, `timezone`)
        VALUES
        (:type, :table, :status, :message, :query,
         :utc, :local, :tz)
    ");

    $stmt->execute([
        ':type' => $type,
        ':table' => $table,
        ':status' => (strpos($type, 'FAILED') !== false) ? 'FAILED' : 'SUCCESS',
        ':message' => $message,
        ':query' => $query,
        ':utc' => $time['utc'],
        ':local' => $time['local'],
        ':tz' => $time['timezone']
    ]);
}

// =========================
// DB SIZE
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

// =========================
// SQL PARSER (DELIMITER SAFE)
// =========================
function parseSQL($sql)
{
    $lines = explode("\n", $sql);

    $delimiter = ';';
    $buffer = '';
    $queries = [];

    foreach ($lines as $line) {

        $trim = trim($line);

        if (preg_match('/^DELIMITER\s+(.+)$/i', $trim, $m)) {
            $delimiter = $m[1];
            continue;
        }

        $buffer .= $line . "\n";

        if (substr(trim($buffer), -strlen($delimiter)) === $delimiter) {

            $query = trim(substr(trim($buffer), 0, -strlen($delimiter)));

            $queries[] = $query;
            $buffer = '';
        }
    }

    if (trim($buffer) !== '') {
        $queries[] = trim($buffer);
    }

    return $queries;
}
?>

<!-- SIMPLE UI -->
<!DOCTYPE html>
<html>

<head>
    <title>Installer</title>
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