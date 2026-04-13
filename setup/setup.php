<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// =========================
// STEP 1: FORM SUBMIT
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
// STEP 2: RUN INSTALL
// =========================
if (isset($_GET['run'])) {

    echo "<h2>Running Installation...</h2><pre>";

    $startTime = microtime(true);
    $startDate = date("Y-m-d H:i:s");

    // =========================
    // CONNECT (NO DB LOCK ISSUES FIX)
    // =========================
    try {
        $pdo = new PDO(
            "mysql:host={$_SESSION['db_host']};charset=utf8mb4",
            $_SESSION['db_user'],
            $_SESSION['db_pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Create DB if not exists
        $dbName = $_SESSION['db_name'];
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
        $pdo->exec("USE `$dbName`");
    } catch (Exception $e) {
        die("DB Connection failed: " . $e->getMessage());
    }

    echo "Start: $startDate\n\n";

    // =========================
    // CREATE AUDIT TABLE
    // =========================
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS install_audit_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            action_type VARCHAR(50),
            status VARCHAR(20),
            message TEXT,
            query_text LONGTEXT,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
    // FILES
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

        // remove /* comments */
        $sql = preg_replace('!/\*.*?\*/!s', '', $sql);

        $queries = parseSQL($sql);

        foreach ($queries as $query) {

            $query = trim($query);
            if ($query === '') continue;

            $time = date("Y-m-d H:i:s");
            $type = getActionType($query);

            try {
                $pdo->exec($query);

                logAudit($pdo, $type, "SUCCESS", "Executed successfully", $query);

                echo "[$time] $type → SUCCESS ✓\n";
                $success++;
            } catch (Exception $e) {

                logAudit($pdo, $type, "FAILED", $e->getMessage(), $query);

                echo "[$time] $type → FAILED ❌\n";
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

    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);

    // =========================
    // FINAL REPORT
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
    echo "End: " . date("Y-m-d H:i:s") . "\n";
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
// AUDIT LOG
// =========================
function logAudit($pdo, $type, $status, $message, $query)
{
    $stmt = $pdo->prepare("
        INSERT INTO install_audit_log
        (action_type, status, message, query_text, executed_at)
        VALUES (:type, :status, :message, :query, NOW())
    ");

    $stmt->execute([
        ':type' => $type,
        ':status' => $status,
        ':message' => $message,
        ':query' => $query
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
        <input name="db_host" placeholder="DB Host" required><br><br>
        <input name="db_name" placeholder="DB Name" required><br><br>
        <input name="db_user" placeholder="DB User" required><br><br>
        <input name="db_pass" placeholder="DB Password" type="password"><br><br>
        <button>Run Install</button>
    </form>

</body>

</html>