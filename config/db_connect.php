<?php
date_default_timezone_set('Asia/Jakarta');

class db_connect {
    private string $host = "127.0.0.1";
    private string $db_name = "projectppk"; 
    private string $username = "root";       
    private string $password = "";           
    private string $charset = "utf8mb4";
    public ?PDO $conn = null;

    public function getConnection(): ?PDO {
        if ($this->conn !== null) {
            return $this->conn; 
        }

        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      
            PDO::ATTR_EMULATE_PREPARES   => false,                  
        ];

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            try {
                $this->conn = new PDO($dsn, $this->username, "mysql8034", $options);
            } catch (PDOException $e2) {
                error_log("Koneksi Database Gagal: " . $e2->getMessage());
                die("Gagal terhubung ke server database.");
            }
        }

        return $this->conn;
    }
}

class Database extends db_connect {}

$db_connect_instance = new db_connect();
$conn = $db_connect_instance->getConnection();
$pdo = $conn;