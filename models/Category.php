<?php
class Category {
    private $conn;
    private $table = 'categories';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all categories
    public function read() {
        try {
            $query = "SELECT category_id, category_name as name FROM " . $this->table . " ORDER BY category_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Category Read Error: " . $e->getMessage());
            throw new Exception("Error reading categories: " . $e->getMessage());
        }
    }

    // Read single category
    public function read_single($id) {
        try {
            $query = "SELECT category_id, category_name as name FROM " . $this->table . " WHERE category_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Category Read Single Error: " . $e->getMessage());
            throw new Exception("Error reading category: " . $e->getMessage());
        }
    }
}
?> 