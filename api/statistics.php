<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../config/database.php';

// Ensure the Database class is defined in the included file
if (!class_exists('Database')) {
    class Database {
        private $host = "localhost";
        private $db_name = "sdo_gentri";
        private $username = "root";
        private $password = "";
        public $conn;

        public function getConnection() {
            $this->conn = null;
            try {
                $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
                $this->conn->exec("set names utf8");
            } catch(PDOException $exception) {
                echo "Connection error: " . $exception->getMessage();
            }
            return $this->conn;
        }
    }
}

$db = new Database();
$conn = $db->getConnection();

// GET all active statistics
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM statistics WHERE is_active = 1 ORDER BY display_order ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $statistics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($statistics);
}

// Admin endpoints would go here (protected by authentication)