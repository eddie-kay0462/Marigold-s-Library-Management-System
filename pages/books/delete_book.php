<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
   if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
       throw new Exception('Invalid request method');
   }

   if (!isset($_POST['book_id'])) {
       throw new Exception('Book ID is required');
   }

   $db = new Database();
   $conn = $db->getConnection();

   // First check if the book exists
   $stmt = $conn->prepare("SELECT book_id FROM books WHERE book_id = :book_id");
   $stmt->execute(['book_id' => $_POST['book_id']]);
   
   if (!$stmt->fetch()) {
       throw new Exception('Book not found');
   }

   // Check if book is referenced in active_loans table (both active and returned loans)
   $stmt = $conn->prepare("
       SELECT COUNT(*) as loan_count 
       FROM active_loans 
       WHERE book_id = :book_id
   ");
   $stmt->execute(['book_id' => $_POST['book_id']]);
   $result = $stmt->fetch(PDO::FETCH_ASSOC);

   if ($result['loan_count'] > 0) {
       throw new Exception('Cannot delete book: it has loan records in the system. All loan records must be removed before deletion.');
   }

   // Delete the book
   $stmt = $conn->prepare("DELETE FROM books WHERE book_id = :book_id");
   $stmt->execute(['book_id' => $_POST['book_id']]);

   echo json_encode([
       'success' => true,
       'message' => 'Book deleted successfully'
   ]);

} catch (Exception $e) {
   http_response_code(400);
   echo json_encode([
       'success' => false,
       'error' => $e->getMessage()
   ]);
}
