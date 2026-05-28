<?php
class Database {
    private static $instance = null;
    private $conn;
    
    private $host = 'localhost';
    private $dbname = 'u656591888_eva_evsu_db';
    private $username = 'u656591888_eva';
    private $password = 'Kenrepollo_12';
    
    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
}


// Also create mysqli connection for existing code
$mysqli = new mysqli('localhost', 'u656591888_eva', 'Kenrepollo_12', 'u656591888_eva_evsu_db');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$conn = $mysqli;
?>