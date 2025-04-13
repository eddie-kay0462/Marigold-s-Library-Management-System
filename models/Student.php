<?php
require_once '../config/database.php';

class Student {
    private $conn;
    private $table = 'students';

    // Student properties
    public $student_id;
    public $student_number;
    public $first_name;
    public $last_name;
    public $email;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all students
    public function read() {
        $query = "SELECT * FROM {$this->table} ORDER BY last_name, first_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create student
    public function create() {
        $query = "INSERT INTO {$this->table} (student_number, first_name, last_name, email) 
                  VALUES (:student_number, :first_name, :last_name, :email)";
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':student_number', $this->student_number);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}