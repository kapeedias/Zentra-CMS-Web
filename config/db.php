<?php
class Database
{
    private $host;
    private $db;
    private $user;
    private $pass;
    private $charset = 'utf8mb4';

    private $pdo;
    private static $instance;

    private function __construct($host = '', $db = '', $user = '', $pass = '')
    {
        // 1. Manual input → getenv() fallback
        $this->host = !empty($host) ? $host : getenv('DB_HOST');
        $this->db   = !empty($db)   ? $db   : getenv('DB_NAME');
        $this->user = !empty($user) ? $user : getenv('DB_USER');
        $this->pass = !empty($pass) ? $pass : getenv('DB_PASS');

        // 2. Detect if we are on Azure (SSL required) or GoDaddy (SSL not supported)
        $isAzure = !empty(getenv('WEBSITE_SITE_NAME')); // Azure auto-sets this

        if ($isAzure) {
            // Azure MySQL requires SSL
            $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset};sslmode=require";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
                PDO::MYSQL_ATTR_SSL_CA => null
            ];
        } else {
            // GoDaddy / Localhost — NO SSL
            $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
        }

        // 3. Connect
        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance($host = '', $db = '', $user = '', $pass = ''): PDO
    {
        if (!self::$instance) {
            self::$instance = (new self($host, $db, $user, $pass))->pdo;
        }
        return self::$instance;
    }
}





/* Version 1.0 - 2024-06-01
 * Database connection class using PDO with SSL support for Azure MySQL.

class Database
{
    private $host;
    private $db;
    private $user;
    private $pass;
    private $charset = 'utf8mb4';

    private $pdo;
    private static $instance;

    private function __construct()
    {
        $this->host = getenv('DB_HOST');
        $this->db   = getenv('DB_NAME');
        $this->user = getenv('DB_USER');
        $this->pass = getenv('DB_PASS');

        //$dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
        // Azure MySQL requires SSL
        //$dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset};sslmode=require";
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset};sslmode=require";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            PDO::MYSQL_ATTR_SSL_CA => null
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // Log error or handle it appropriately
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance(): PDO
    {
        if (!self::$instance) {
            self::$instance = (new self())->pdo;
        }
        return self::$instance;
    }
}
End of Version 1.0 - 2024-06-01 */



/* to test the connectivity of the database host uncomment the below code and run it
try {
    $pdo = Database::getInstance();
    echo "<p style='color: green;'>✅ Database connection successful.</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}
 */