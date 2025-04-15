<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli("localhost", "root", "", "marigold_db");

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => "Connection failed: " . $conn->connect_error
    ]));
}

try {
    // Get staff count (users where is_active = 1)
    $staffQuery = "SELECT COUNT(*) as count FROM users WHERE is_active = 1";
    $staffResult = $conn->query($staffQuery);
    if (!$staffResult) {
        throw new Exception("Staff query failed: " . $conn->error);
    }
    $staffRow = $staffResult->fetch_assoc();
    $staffCount = intval($staffRow['count']); // Convert to integer explicitly

    // Get student count
    $studentQuery = "SELECT COUNT(*) as count FROM students WHERE is_active = 1";
    $studentResult = $conn->query($studentQuery);
    if (!$studentResult) {
        throw new Exception("Student query failed: " . $conn->error);
    }
    $studentCount = $studentResult->fetch_assoc()['count'];

    // Get books count
    $bookQuery = "SELECT COUNT(*) as count FROM books";
    $bookResult = $conn->query($bookQuery);
    if (!$bookResult) {
        throw new Exception("Book query failed: " . $conn->error);
    }
    $bookCount = $bookResult->fetch_assoc()['count'];

    // Get raw users data for debugging
    $usersQuery = "SELECT * FROM users";
    $usersResult = $conn->query($usersQuery);
    if (!$usersResult) {
        throw new Exception("Users query failed: " . $conn->error);
    }
    $users = [];
    while ($row = $usersResult->fetch_assoc()) {
        $users[] = $row;
    }

    // Calculate total active members
    $activeCount = $staffCount + $studentCount;

    echo json_encode([
        'success' => true,
        'active_members' => $activeCount,
        'students' => $studentCount,
        'staff' => $staffCount,
        'books' => $bookCount,
        'debug' => [
            'raw_users' => $users,
            'staff_query' => $staffQuery,
            'staff_count' => $staffCount,
            'staff_row' => $staffRow
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching dashboard statistics: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?> 