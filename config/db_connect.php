<?php
class db_connect {
    private string $host = "127.0.0.1";
    private string $db_name = "projectppk"; 
    private string $username = "root";       
    private string $password = "mysql8034";           
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
        } catch (PDOException $exception) {
            // Pada mode produksi, hindari echo langsung pesan error rahasia ke layar
            error_log("Koneksi Database Gagal: " . $exception->getMessage());
            die("Gagal terhubung ke server database.");
        }

        return $this->conn;
    }
}