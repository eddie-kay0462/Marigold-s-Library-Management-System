<?php
class Book {
    private $conn;
    private $table = 'books';
    private $copies_table = 'book_copies';

    // Book properties
    public $id;
    public $book_id;
    public $isbn;
    public $title;
    public $author;
    public $category_id;
    public $publisher;
    public $publication_year;
    public $edition;
    public $total_copies;
    public $available_copies;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new book
    public function create() {
        try {
            $this->conn->beginTransaction();

            // Insert into books table
            $query = "INSERT INTO " . $this->table . "
                    (isbn, title, author, category_id, total_copies, available_copies)
                    VALUES 
                    (:isbn, :title, :author, :category_id, :total_copies, :total_copies)";

            $stmt = $this->conn->prepare($query);

            // Sanitize and bind data
            $stmt->bindParam(':isbn', htmlspecialchars(strip_tags($this->isbn)));
            $stmt->bindParam(':title', htmlspecialchars(strip_tags($this->title)));
            $stmt->bindParam(':author', htmlspecialchars(strip_tags($this->author)));
            $stmt->bindParam(':category_id', $this->category_id);
            $stmt->bindParam(':total_copies', $this->total_copies);

            if(!$stmt->execute()) {
                throw new Exception("Error creating book");
            }

            // Get the auto-generated book ID
            $book_id = $this->conn->lastInsertId();

            // Create book copies
            $copy_query = "INSERT INTO " . $this->copies_table . "
                        (book_id, copy_number, status)
                        VALUES (:book_id, :copy_number, 'Available')";
            
            $copy_stmt = $this->conn->prepare($copy_query);

            // Create the specified number of copies
            for($i = 1; $i <= $this->total_copies; $i++) {
                $copy_stmt->bindParam(':book_id', $book_id);
                $copy_stmt->bindParam(':copy_number', $i);
                if(!$copy_stmt->execute()) {
                    throw new Exception("Error creating book copy");
                }
            }

            $this->conn->commit();
            return true;

        } catch(Exception $e) {
            $this->conn->rollBack();
            error_log("Book Creation Error: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    // Read all books
    public function read() {
        try {
            $query = "SELECT b.*, c.category_name 
                    FROM " . $this->table . " b
                    LEFT JOIN categories c ON b.category_id = c.category_id
                    ORDER BY b.title";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Book Read Error: " . $e->getMessage());
            throw new Exception("Error reading books: " . $e->getMessage());
        }
    }

    // Read single book
    public function read_single($id) {
        try {
            $query = "SELECT b.*, c.category_name 
                    FROM " . $this->table . " b
                    LEFT JOIN categories c ON b.category_id = c.category_id
                    WHERE b.book_id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            
            $book = $stmt->fetch(PDO::FETCH_ASSOC);
            if($book) {
                // Get copies information
                $copies_query = "SELECT * FROM " . $this->copies_table . " WHERE book_id = ?";
                $copies_stmt = $this->conn->prepare($copies_query);
                $copies_stmt->execute([$id]);
                $book['copies'] = $copies_stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $book;
        } catch(PDOException $e) {
            error_log("Book Read Single Error: " . $e->getMessage());
            throw new Exception("Error reading book: " . $e->getMessage());
        }
    }

    // Update book
    public function update() {
        try {
            $this->conn->beginTransaction();

            $query = "UPDATE " . $this->table . "
                    SET isbn = :isbn, 
                        title = :title, 
                        author = :author, 
                        category_id = :category_id,
                        total_copies = :total_copies, 
                        available_copies = :available_copies,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE book_id = :book_id";

            $stmt = $this->conn->prepare($query);

            // Sanitize and bind data
            $stmt->bindParam(':book_id', $this->book_id);
            $stmt->bindParam(':isbn', htmlspecialchars(strip_tags($this->isbn)));
            $stmt->bindParam(':title', htmlspecialchars(strip_tags($this->title)));
            $stmt->bindParam(':author', htmlspecialchars(strip_tags($this->author)));
            $stmt->bindParam(':category_id', $this->category_id);
            $stmt->bindParam(':total_copies', $this->total_copies);
            $stmt->bindParam(':available_copies', $this->available_copies);

            if(!$stmt->execute()) {
                throw new Exception("Error updating book");
            }

            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            error_log("Book Update Error: " . $e->getMessage());
            throw new Exception("Error updating book: " . $e->getMessage());
        }
    }

    // Delete book
    public function delete($id) {
        try {
            $this->conn->beginTransaction();

            // First delete all copies
            $delete_copies = "DELETE FROM " . $this->copies_table . " WHERE book_id = ?";
            $copies_stmt = $this->conn->prepare($delete_copies);
            if(!$copies_stmt->execute([$id])) {
                throw new Exception("Error deleting book copies");
            }

            // Then delete the book
            $delete_book = "DELETE FROM " . $this->table . " WHERE book_id = ?";
            $book_stmt = $this->conn->prepare($delete_book);
            if(!$book_stmt->execute([$id])) {
                throw new Exception("Error deleting book");
            }

            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            error_log("Book Delete Error: " . $e->getMessage());
            throw new Exception("Error deleting book: " . $e->getMessage());
        }
    }
}