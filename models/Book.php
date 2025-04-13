<?php
require_once '../config/database.php';

class Book {
    private $conn;
    private $table = 'books';

    // Book properties
    public $book_id;
    public $isbn;
    public $title;
    public $author;
    public $category_id;
    public $publisher;
    public $publication_year;
    public $edition;
    public $description;
    public $cover_image;
    public $available_copies;
    public $total_copies;
    public $location;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all books
    public function read() {
        $query = "SELECT b.*, c.category_name FROM {$this->table} b
                  LEFT JOIN categories c ON b.category_id = c.category_id
                  ORDER BY b.title ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get single book
    public function read_single() {
        $query = "SELECT b.*, c.category_name FROM {$this->table} b
                  LEFT JOIN categories c ON b.category_id = c.category_id
                  WHERE b.book_id = :book_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':book_id', $this->book_id);
        $stmt->execute();
        return $stmt;
    }

    // Create book
    public function create() {
        $query = "INSERT INTO {$this->table} 
                  (isbn, title, author, category_id, publisher, publication_year, 
                   edition, description, cover_image, available_copies, total_copies, location) 
                  VALUES 
                  (:isbn, :title, :author, :category_id, :publisher, :publication_year, 
                   :edition, :description, :cover_image, :available_copies, :total_copies, :location)";
        $stmt = $this->conn->prepare($query);
        
        // Set default values if not provided
        $this->available_copies = $this->available_copies ?? 0;
        $this->total_copies = $this->total_copies ?? 0;
        
        // Bind parameters
        $stmt->bindParam(':isbn', $this->isbn);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':author', $this->author);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':publisher', $this->publisher);
        $stmt->bindParam(':publication_year', $this->publication_year);
        $stmt->bindParam(':edition', $this->edition);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':cover_image', $this->cover_image);
        $stmt->bindParam(':available_copies', $this->available_copies);
        $stmt->bindParam(':total_copies', $this->total_copies);
        $stmt->bindParam(':location', $this->location);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}