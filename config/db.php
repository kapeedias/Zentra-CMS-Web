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


/* to test the connectivity of the database host uncomment the below code and run it
try {
    $pdo = Database::getInstance();
    echo "<p style='color: green;'>✅ Database connection successful.</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}
 */