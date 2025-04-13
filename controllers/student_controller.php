<?php
require_once '../config/config.php';
require_once '../models/Student.php';
require_once '../config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('pages/login.html');
}

// Get all students
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $database = new Database();
    $db = $database->connect();
    
    $student = new Student($db);
    $result = $student->read();
    
    if ($result->rowCount() > 0) {
        $students_arr = array();
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $student_item = array(
                'id' => $row['student_id'],
                'student_number' => $row['student_number'],
                'name' => $row['first_name'] . ' ' . $row['last_name'],
                'email' => $row['email']
            );
            
            array_push($students_arr, $student_item);
        }
        
        echo json_encode($students_arr);
    } else {
        echo json_encode(array('message' => 'No students found'));
    }
}

// Add new student
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->connect();
    
    $student = new Student($db);
    $student->student_number = $_POST['student_id'];
    $student->first_name = $_POST['first_name'];
    $student->last_name = $_POST['last_name'];
    $student->email = $_POST['email'];
    
    if ($student->create()) {
        echo json_encode(array('message' => 'Student added successfully'));
    } else {
        echo json_encode(array('message' => 'Failed to add student'));
    }
}