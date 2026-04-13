<?php
session_start();

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
// RUN INSTALL
// =========================
if (isset($_GET['run'])) {

    $startTime = microtime(true);
    $startDate = date("Y-m-d H:i:s");

    $pdo = connectDB(
        $_SESSION['db_host'],
        $_SESSION['db_name'],
        $_SESSION['db_user'],
        $_SESSION['db_pass']
    );

    echo "<h2>Running Installation...</h2><pre>";

    logEvent($pdo, "START", "Installation started at $startDate");

    // ensure log table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS install_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_type VARCHAR(50),
            message TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $sqlFiles = glob(__DIR__ . "/*.sql");

    if (!$sqlFiles) {
        logEvent($pdo, "ERROR", "No SQL files found");
        die("No SQL files found.");
    }

    foreach ($sqlFiles as $file) {

        $fileName = basename($file);

        logEvent($pdo, "FILE_START", "Running file: $fileName");

        echo "FILE: $fileName\n";

        $sql = file_get_contents($file);
        $queries = parseSQLFile($sql);

        foreach ($queries as $query) {

            $trim = trim($query);
            if ($trim === '') continue;

            try {
                $pdo->exec($trim);
                logEvent($pdo, "SUCCESS", $trim);

                echo "OK ✓\n";
            } catch (Exception $e) {

                $error = $e->getMessage();

                logEvent($pdo, "ERROR", $trim . " | ERROR: " . $error);

                echo "FAILED ❌: $error\n";
            }
        }

        logEvent($pdo, "FILE_END", "Finished file: $fileName");
    }

    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);

    $endDate = date("Y-m-d H:i:s");

    logEvent($pdo, "END", "Installation finished at $endDate | Duration: {$duration}s");

    echo "\nDONE ✓ in {$duration}s</pre>";

    exit;
}

// =========================
// DB CONNECT
// =========================
function connectDB($host, $db, $user, $pass)
{
    return new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
}

// =========================
// LOG FUNCTION
// =========================
function logEvent($pdo, $type, $message)
{
    $stmt = $pdo->prepare("
        INSERT INTO install_logs (event_type, message)
        VALUES (:type, :message)
    ");

    $stmt->execute([
        ':type' => $type,
        ':message' => $message
    ]);
}

// =========================
// SQL PARSER (supports procedures/triggers)
// =========================
function parseSQLFile($sql)
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
    <style>
    body {
        font-family: Arial;
        background: #f5f5f5;
    }

    .box {
        width: 420px;
        margin: 60px auto;
        padding: 20px;
        background: #fff;
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
        background: #007bff;
        color: #fff;
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