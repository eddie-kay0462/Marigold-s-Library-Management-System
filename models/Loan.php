<?php
require_once '../config/database.php';

class Loan {
    private $conn;
    private $table = 'active_loans';

    // Loan properties
    public $loan_id;
    public $student_id;
    public $copy_id;
    public $loan_date;
    public $due_date;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get active loans
    public function read_active() {
        $query = "SELECT l.*, s.first_name, s.last_name, b.title, b.author 
                  FROM {$this->table} l
                  JOIN students s ON l.student_id = s.student_id
                  JOIN book_copies bc ON l.copy_id = bc.copy_id
                  JOIN books b ON bc.book_id = b.book_id
                  WHERE l.status = 'Active' OR l.status = 'Overdue'
                  ORDER BY l.due_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create loan
    public function create() {
        $query = "INSERT INTO {$this->table} (student_id, copy_id, loan_date, due_date, status) 
                  VALUES (:student_id, :copy_id, :loan_date, :due_date, :status)";
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':student_id', $this->student_id);
        $stmt->bindParam(':copy_id', $this->copy_id);
        $stmt->bindParam(':loan_date', $this->loan_date);
        $stmt->bindParam(':due_date', $this->due_date);
        $stmt->bindParam(':status', $this->status);
        
        // Update book copy status
        $update_query = "UPDATE book_copies SET status = 'Borrowed' WHERE copy_id = :copy_id";
        $update_stmt = $this->conn->prepare($update_query);
        $update_stmt->bindParam(':copy_id', $this->copy_id);
        $update_stmt->execute();
        
        // Update available copies count
        $book_query = "UPDATE books b 
                      SET b.available_copies = b.available_copies - 1 
                      WHERE b.book_id = (SELECT book_id FROM book_copies WHERE copy_id = :copy_id)";
        $book_stmt = $this->conn->prepare($book_query);
        $book_stmt->bindParam(':copy_id', $this->copy_id);
        $book_stmt->execute();
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Return book
    public function return_book() {
        // Update loan status
        $query = "UPDATE {$this->table} SET status = 'Returned', returned_date = CURRENT_DATE() WHERE loan_id = :loan_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':loan_id', $this->loan_id);
        
        // Get copy_id
        $get_copy = "SELECT copy_id FROM {$this->table} WHERE loan_id = :loan_id";
        $copy_stmt = $this->conn->prepare($get_copy);
        $copy_stmt->bindParam(':loan_id', $this->loan_id);
        $copy_stmt->execute();
        $copy_row = $copy_stmt->fetch(PDO::FETCH_ASSOC);
        $copy_id = $copy_row['copy_id'];
        
        // Update book copy status
        $update_query = "UPDATE book_copies SET status = 'Available' WHERE copy_id = :copy_id";
        $update_stmt = $this->conn->prepare($update_query);
        $update_stmt->bindParam(':copy_id', $copy_id);
        $update_stmt->execute();
        
        // Update available copies count
        $book_query = "UPDATE books b 
                      SET b.available_copies = b.available_copies + 1 
                      WHERE b.book_id = (SELECT book_id FROM book_copies WHERE copy_id = :copy_id)";
        $book_stmt = $this->conn->prepare($book_query);
        $book_stmt->bindParam(':copy_id', $copy_id);
        $book_stmt->execute();
        
        // Add to loan history
        $history_query = "INSERT INTO loan_history (loan_id, student_id, copy_id, loan_date, due_date, returned_date, status)
                          SELECT loan_id, student_id, copy_id, loan_date, due_date, CURRENT_DATE(),
                          CASE WHEN CURRENT_DATE() > due_date THEN 'Late' ELSE 'On Time' END
                          FROM {$this->table} WHERE loan_id = :loan_id";
        $history_stmt = $this->conn->prepare($history_query);
        $history_stmt->bindParam(':loan_id', $this->loan_id);
        $history_stmt->execute();
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}