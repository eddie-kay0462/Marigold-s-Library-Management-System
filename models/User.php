<?php
require_once '../config/database.php';

class User {
    private $conn;
    private $table = 'users';

    // User properties
    public $user_id;
    public $username;
    public $password;
    public $first_name;
    public $last_name;
    public $email;
    public $role_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Login user
    public function login() {
        $query = "SELECT * FROM {$this->table} WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $this->username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->user_id = $row['user_id'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->role_id = $row['role_id'];
            $hashed_password = $row['password'];

            // Verify password
            if(password_verify($this->password, $hashed_password)) {
                return true;
            }
        }
        return false;
    }

    // Create user
    public function create() {
        $query = "INSERT INTO {$this->table} (username, password, first_name, last_name, email, role_id) 
                  VALUES (:username, :password, :first_name, :last_name, :email, :role_id)";
        $stmt = $this->conn->prepare($query);
        
        // Hash password
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Bind parameters
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':role_id', $this->role_id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}