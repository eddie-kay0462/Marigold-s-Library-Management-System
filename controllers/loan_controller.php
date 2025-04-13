<?php
require_once '../config/config.php';
require_once '../models/Loan.php';
require_once '../config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('pages/login.html');
}

// Get active loans
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $database = new Database();
    $db = $database->connect();
    
    $loan = new Loan($db);
    $result = $loan->read_active();
    
    if ($result->rowCount() > 0) {
        $loans_arr = array();
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $loan_item = array(
                'id' => $row['loan_id'],
                'student' => $row['first_name'] . ' ' . $row['last_name'],
                'book' => $row['title'],
                'author' => $row['author'],
                'loan_date' => $row['loan_date'],
                'due_date' => $row['due_date'],
                'status' => $row['status']
            );
            
            array_push($loans_arr, $loan_item);
        }
        
        echo json_encode($loans_arr);
    } else {
        echo json_encode(array('message' => 'No active loans found'));
    }
}

// Create new loan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow'])) {
    $database = new Database();
    $db = $database->connect();
    
    $loan = new Loan($db);
    $loan->student_id = $_POST['student_id'];
    $loan->copy_id = $_POST['copy_id'];
    $loan->loan_date = date('Y-m-d');
    $loan->due_date = $_POST['due_date'];
    $loan->status = 'Active';
    
    if ($loan->create()) {
        echo json_encode(array('message' => 'Book borrowed successfully'));
    } else {
        echo json_encode(array('message' => 'Failed to borrow book'));
    }
}

// Return book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return'])) {
    $database = new Database();
    $db = $database->connect();
    
    $loan = new Loan($db);
    $loan->loan_id = $_POST['loan_id'];
    
    if ($loan->return_book()) {
        echo json_encode(array('message' => 'Book returned successfully'));
    } else {
        echo json_encode(array('message' => 'Failed to return book'));
    }
}