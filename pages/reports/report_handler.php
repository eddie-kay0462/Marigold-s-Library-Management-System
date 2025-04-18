<?php
// Include database connection
require_once '../../config/database.php';

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

// Set headers
header('Content-Type: application/json');

try {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $response = ['status' => 'error', 'message' => 'Invalid action'];

    switch ($action) {
        case 'get_loan_stats':
            // Get total loans
            $totalQuery = "SELECT COUNT(*) as total FROM active_loans";
            $totalStmt = $conn->query($totalQuery);
            $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Get active loans
            $activeQuery = "SELECT COUNT(*) as active FROM active_loans WHERE status = 'Active'";
            $activeStmt = $conn->query($activeQuery);
            $active = $activeStmt->fetch(PDO::FETCH_ASSOC)['active'];

            // Get overdue loans
            $overdueQuery = "SELECT COUNT(*) as overdue FROM active_loans WHERE due_date < CURDATE() AND status = 'Active'";
            $overdueStmt = $conn->query($overdueQuery);
            $overdue = $overdueStmt->fetch(PDO::FETCH_ASSOC)['overdue'];

            // Get returned loans
            $returnedQuery = "SELECT COUNT(*) as returned FROM active_loans WHERE status = 'Returned'";
            $returnedStmt = $conn->query($returnedQuery);
            $returned = $returnedStmt->fetch(PDO::FETCH_ASSOC)['returned'];

            $response = [
                'status' => 'success',
                'data' => [
                    'total_loans' => $total,
                    'active_loans' => $active,
                    'overdue_loans' => $overdue,
                    'returned_loans' => $returned
                ]
            ];
            break;

        case 'get_overdue_stats':
            $query = "SELECT 
                        CASE 
                            WHEN DATEDIFF(CURDATE(), due_date) BETWEEN 1 AND 7 THEN '1-7 days'
                            WHEN DATEDIFF(CURDATE(), due_date) BETWEEN 8 AND 14 THEN '8-14 days'
                            WHEN DATEDIFF(CURDATE(), due_date) BETWEEN 15 AND 30 THEN '15-30 days'
                            ELSE 'Over 30 days'
                        END as overdue_range,
                        COUNT(*) as count
                    FROM active_loans 
                    WHERE due_date < CURDATE() AND status = 'Active'
                    GROUP BY overdue_range";
            
            $stmt = $conn->query($query);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response = [
                'status' => 'success',
                'data' => $data
            ];
            break;

        case 'get_monthly_loans':
            $query = "SELECT 
                        DATE_FORMAT(loan_date, '%M %Y') as month,
                        COUNT(*) as loan_count
                    FROM active_loans
                    WHERE loan_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                    GROUP BY month
                    ORDER BY loan_date ASC";
            
            $stmt = $conn->query($query);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response = [
                'status' => 'success',
                'data' => $data
            ];
            break;

        case 'get_popular_books':
            $query = "SELECT 
                        b.title,
                        COUNT(*) as borrow_count
                    FROM active_loans l
                    JOIN books b ON l.book_id = b.book_id
                    GROUP BY b.book_id, b.title
                    ORDER BY borrow_count DESC
                    LIMIT 10";
            
            $stmt = $conn->query($query);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response = [
                'status' => 'success',
                'data' => $data
            ];
            break;

        case 'get_loan_reports':
            $status = isset($_GET['status']) ? $_GET['status'] : 'all';
            $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
            $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
            $studentId = isset($_GET['student_id']) ? $_GET['student_id'] : '';

            $query = "SELECT 
                        l.loan_id,
                        b.title,
                        s.student_number,
                        l.loan_date,
                        l.due_date,
                        l.returned_date,
                        l.status
                    FROM active_loans l
                    JOIN books b ON l.book_id = b.book_id
                    JOIN students s ON l.student_id = s.student_id
                    WHERE 1=1";

            if ($status !== 'all') {
                $query .= " AND l.status = :status";
            }
            if ($startDate) {
                $query .= " AND l.loan_date >= :start_date";
            }
            if ($endDate) {
                $query .= " AND l.loan_date <= :end_date";
            }
            if ($studentId) {
                $query .= " AND l.student_id = :student_id";
            }

            $query .= " ORDER BY l.loan_date DESC";
            
            $stmt = $conn->prepare($query);
            
            if ($status !== 'all') {
                $stmt->bindParam(':status', $status);
            }
            if ($startDate) {
                $stmt->bindParam(':start_date', $startDate);
            }
            if ($endDate) {
                $stmt->bindParam(':end_date', $endDate);
            }
            if ($studentId) {
                $stmt->bindParam(':student_id', $studentId);
            }

            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response = [
                'status' => 'success',
                'data' => $data
            ];
            break;
    }

} catch (PDOException $e) {
    $response = [
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ];
}

echo json_encode($response);
?>
