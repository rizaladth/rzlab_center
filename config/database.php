<?php
/**
 * =============================================
 * RizalLab Command - Database Configuration
 * =============================================
 * Konfigurasi koneksi ke database MySQL.
 * Menggunakan PDO untuk keamanan (prevent SQL Injection).
 */

class Database {
    private $host = 'localhost';
    private $dbname = 'rizallab_inventory';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    private $conn = null;

    /**
     * Mengembalikan koneksi PDO ke database
     * @return PDO
     */
    public function connect(): PDO {
        if ($this->conn !== null) {
            return $this->conn;
        }

        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Koneksi database gagal: ' . $e->getMessage()
            ]);
            exit;
        }

        return $this->conn;
    }
}
