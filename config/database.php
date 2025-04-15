<?php
class Database {
    private $host = "localhost";
    private $db_name = "marigold_db";
    private $username = "root";
    private $password = "";
    private $conn;

    public function getConnection() {
        try {
            // Check if database exists
            $temp = new PDO(
                "mysql:host=" . $this->host,
                $this->username,
                $this->password
            );
            
            $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
            $stmt = $temp->prepare($query);
            $stmt->execute([$this->db_name]);
            
            if (!$stmt->fetch()) {
                throw new Exception("Database '{$this->db_name}' does not exist");
            }
            
            // Connect to the specific database
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->exec("SET NAMES utf8");
            
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database Connection Error: " . $e->getMessage());
        } catch(Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }
}