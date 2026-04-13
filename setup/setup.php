<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// =========================
// STEP 1: HANDLE FORM
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

    // -------------------------
    // CHECK SESSION
    // -------------------------
    if (!isset($_SESSION['db_host'])) {
        die("ERROR: Session missing. Please go back and submit form again.");
    }

    $startTime = microtime(true);
    $startDate = date("Y-m-d H:i:s");

    echo "Start time: $startDate\n\n";

    // -------------------------
    // CONNECT DB
    // -------------------------
    try {
        $pdo = new PDO(
            "mysql:host={$_SESSION['db_host']};dbname={$_SESSION['db_name']};charset=utf8mb4",
            $_SESSION['db_user'],
            $_SESSION['db_pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );

        echo "DB Connected ✓\n\n";
    } catch (Exception $e) {
        die("DB Connection failed: " . $e->getMessage());
    }

    // -------------------------
    // FIND SQL FILES
    // -------------------------
    $sqlFiles = glob(__DIR__ . "/*.sql");

    if (!$sqlFiles) {
        die("ERROR: No .sql files found in setup folder.");
    }

    echo "Found " . count($sqlFiles) . " SQL file(s)\n\n";

    // -------------------------
    // PROCESS EACH FILE
    // -------------------------
    foreach ($sqlFiles as $file) {

        echo "========================\n";
        echo "FILE: " . basename($file) . "\n";
        echo "========================\n\n";

        $sql = file_get_contents($file);

        if (!$sql) {
            echo "EMPTY FILE SKIPPED\n\n";
            continue;
        }

        $queries = parseSQL($sql);

        echo "Queries detected: " . count($queries) . "\n\n";

        foreach ($queries as $query) {

            $query = trim($query);
            if ($query === '') continue;

            $upper = strtoupper($query);

            // -------------------------
            // SAFETY RULES
            // -------------------------
            if (str_contains($upper, "DROP DATABASE")) {
                echo "SKIPPED DROP DATABASE\n\n";
                continue;
            }

            if (str_contains($upper, "TRUNCATE")) {
                echo "SKIPPED TRUNCATE\n\n";
                continue;
            }

            // -------------------------
            // EXECUTE
            // -------------------------
            try {
                $pdo->exec($query);
                echo "OK ✓\n";
            } catch (Exception $e) {
                echo "FAILED ❌\n";
                echo $e->getMessage() . "\n\n";
            }
        }

        echo "\n";
    }

    // -------------------------
    // FINISH LOG
    // -------------------------
    $duration = round(microtime(true) - $startTime, 2);
    $endDate = date("Y-m-d H:i:s");

    echo "\n========================\n";
    echo "INSTALL COMPLETE ✓\n";
    echo "End time: $endDate\n";
    echo "Duration: {$duration}s\n";
    echo "========================\n";

    exit;
}

// =========================
// SMART SQL PARSER
// supports:
// - procedures
// - triggers
// - functions
// - DELIMITER $$
// =========================
function parseSQL($sql)
{
    $lines = explode("\n", $sql);

    $delimiter = ';';
    $buffer = '';
    $queries = [];

    foreach ($lines as $line) {

        $trim = trim($line);

        // detect DELIMITER change
        if (preg_match('/^DELIMITER\s+(.+)$/i', $trim, $m)) {
            $delimiter = $m[1];
            continue;
        }

        $buffer .= $line . "\n";

        // check delimiter end
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
     HTML FORM
========================= -->
<!DOCTYPE html>
<html>

<head>
    <title>Setup Installer</title>
    <style>
    body {
        font-family: Arial;
        background: #f5f5f5;
    }

    .box {
        width: 420px;
        margin: 60px auto;
        padding: 20px;
        background: white;
        border-radius: 10px;
    }

    input {
        width: 100%;
        padding: 10px;
        margin: 8px 0;
    }

    button {
        width: 100%;
        padding: 10px;
        background: #28a745;
        color: white;
        border: none;
    }
    </style>
</head>

<body>

    <div class="box">
        <h2>Project Setup</h2>

        <form method="POST">
            <input name="db_host" placeholder="DB Host" required>
            <input name="db_name" placeholder="DB Name" required>
            <input name="db_user" placeholder="DB User" required>
            <input name="db_pass" placeholder="DB Password" type="password">
            <button type="submit">Run Setup</button>
        </form>
    </div>

</body>

</html>