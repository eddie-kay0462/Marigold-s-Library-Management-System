<?php
// Include necessary files
require_once '../config/database.php';
session_start();

// Basic session check
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to access the dashboard.";
    header("Location: login.php");
    exit();
}

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

// Get counts for dashboard
try {
    // Count total books
    $bookQuery = "SELECT COUNT(*) as total_books FROM books";
    $bookStmt = $conn->prepare($bookQuery);
    $bookStmt->execute();
    $totalBooks = $bookStmt->fetch(PDO::FETCH_ASSOC)['total_books'];
    
    // Count total students
    $studentQuery = "SELECT COUNT(*) as total_students FROM students";
    $studentStmt = $conn->prepare($studentQuery);
    $studentStmt->execute();
    $totalStudents = $studentStmt->fetch(PDO::FETCH_ASSOC)['total_students'];
    
    // Count active loans
    $activeLoansQuery = "SELECT COUNT(*) as active_loans FROM active_loans WHERE status = 'Active' OR status != 'Returned'";
    $activeLoansStmt = $conn->prepare($activeLoansQuery);
    $activeLoansStmt->execute();
    $activeLoans = $activeLoansStmt->fetch(PDO::FETCH_ASSOC)['active_loans'];
    
    // Count overdue loans
    $overdueLoansQuery = "SELECT COUNT(*) as overdue_loans FROM active_loans WHERE due_date <= CURDATE() AND status != 'Returned'";
    $overdueLoansStmt = $conn->prepare($overdueLoansQuery);
    $overdueLoansStmt->execute();
    $overdueLoans = $overdueLoansStmt->fetch(PDO::FETCH_ASSOC)['overdue_loans'];
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marigold's Library Management System</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Additional styles for the dashboard */
        .dashboard-container {
            display: flex;
            min-height: calc(100vh - 80px);
            margin-top: 0; /* Remove the margin-top */
            flex-direction: row;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
        }
        
        .sidebar {
            width: 250px;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 80px;
            height: calc(100vh - 80px);
            overflow-y: auto;
            transition: all 0.3s ease;
            border-radius: 0 10px 10px 0;
            border-right: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .sidebar-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .sidebar-header img {
            max-width: 120px;
            margin-bottom: 10px;
            transition: transform 0.3s ease;
        }
        
        .sidebar-header img:hover {
            transform: scale(1.05);
        }
        
        .sidebar-header h3 {
            color: #333;
            font-weight: 600;
            margin: 0;
            font-size: 1.2rem;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
            position: relative;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #555;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }
        
        .sidebar-menu a i {
            margin-right: 12px; /* Add margin to the right of icons */
            font-size: 1.1rem; /* Slightly increase icon size */
            width: 20px; /* Fixed width for icons to align them */
            text-align: center; /* Center the icons */
        }
        
        .sidebar-menu a:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: #4CAF50;
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(76, 175, 80, 0.08);
            color: #4CAF50;
            padding-left: 18px;
        }
        
        .sidebar-menu a:hover:before, .sidebar-menu a.active:before {
            transform: scaleY(1);
        }
        
        .sidebar-menu a.active {
            font-weight: 600;
        }
        
        .content-area {
            flex: 1;
            padding: 20px;
            background-color: #fff;
            /* Remove the margin-left since we're using sticky positioning */
            transition: all 0.3s ease; /* Smooth transition for responsive changes */
        }
        
        .dashboard-section {
            display: none;
        }
        
        .dashboard-section.active {
            display: block;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .card {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #4CAF50, #45a049, #4CAF50);
            background-size: 200% 100%;
            animation: gradientMove 3s linear infinite;
        }
        
        @keyframes gradientMove {
            0% { background-position: 100% 0; }
            100% { background-position: -100% 0; }
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .card h2 {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card h2 i {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.8rem;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:nth-child(1) {
            background: linear-gradient(135deg, #ffffff 0%, #e8f5e9 100%);
        }
        
        .stat-card:nth-child(2) {
            background: linear-gradient(135deg, #ffffff 0%, #e3f2fd 100%);
        }
        
        .stat-card:nth-child(3) {
            background: linear-gradient(135deg, #ffffff 0%, #fff3e0 100%);
        }
        
        .stat-card:nth-child(4) {
            background: linear-gradient(135deg, #ffffff 0%, #ffebee 100%);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0.3));
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card:hover::before {
            opacity: 1;
        }
        
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 20px;
            display: inline-block;
            transition: transform 0.3s ease;
            background: linear-gradient(45deg, #4CAF50, #45a049);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }
        
        .stat-card:nth-child(1) i {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-card:nth-child(2) i {
            background: linear-gradient(45deg, #2196F3, #1976D2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-card:nth-child(3) i {
            background: linear-gradient(45deg, #FF9800, #F57C00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-card:nth-child(4) i {
            background: linear-gradient(45deg, #f44336, #d32f2f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-card:hover i {
            transform: scale(1.1) rotate(5deg);
        }
        
        .stat-card h3 {
            color: #2c3e50;
            font-size: 1.1rem;
            margin: 15px 0;
            font-weight: 500;
        }
        
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
            background: linear-gradient(45deg, #2c3e50, #34495e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }
        
        .stat-card:nth-child(1) .number {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-card:nth-child(2) .number {
            background: linear-gradient(45deg, #2196F3, #1976D2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-card:nth-child(3) .number {
            background: linear-gradient(45deg, #FF9800, #F57C00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-card:nth-child(4) .number {
            background: linear-gradient(45deg, #f44336, #d32f2f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        table th {
            background: linear-gradient(145deg, #f8f9fa, #ffffff);
            color: #2c3e50;
            font-weight: 600;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
        }
        
        table td {
            padding: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            color: #555;
            transition: all 0.3s ease;
        }
        
        table tr:hover td {
            background: linear-gradient(90deg, rgba(76, 175, 80, 0.05), rgba(76, 175, 80, 0.1));
        }
        
        .btn {
            border: none;
            border-radius: 12px;
            padding: 12px 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
            color: white;
            margin: 10px 0;
        }
        
        /* Primary action buttons (Add, Save, Confirm) */
        .btn-primary {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.2);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.3);
        }
        
        /* Delete/Remove buttons */
        .btn-danger {
            background: linear-gradient(45deg, #f44336, #d32f2f);
            box-shadow: 0 4px 15px rgba(244, 67, 54, 0.2);
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(244, 67, 54, 0.3);
        }
        
        /* Edit/Update buttons */
        .btn-warning {
            background: linear-gradient(45deg, #FF9800, #F57C00);
            box-shadow: 0 4px 15px rgba(255, 152, 0, 0.2);
        }
        
        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 152, 0, 0.3);
        }
        
        /* View/Details buttons */
        .btn-info {
            background: linear-gradient(45deg, #2196F3, #1976D2);
            box-shadow: 0 4px 15px rgba(33, 150, 243, 0.2);
        }
        
        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(33, 150, 243, 0.3);
        }
        
        /* Return/Back buttons */
        .btn-secondary {
            background: linear-gradient(45deg, #607D8B, #455A64);
            box-shadow: 0 4px 15px rgba(96, 125, 139, 0.2);
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(96, 125, 139, 0.3);
        }
        
        /* Shine effect for all buttons */
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            background-color: #fff;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }
        
        /* Responsive styles */
        @media (max-width: 1024px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr); /* 2 columns for tablets */
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column; /* Stack sidebar and content on mobile */
            }
            
            .sidebar {
                width: 100%; /* Full width on mobile */
                height: auto; /* Auto height on mobile */
                position: relative; /* Change to relative for mobile */
                top: 0; /* Reset top position */
                max-height: 300px; /* Limit height on mobile */
            }
            
            .content-area {
                width: 100%; /* Full width on mobile */
            }
            
            .stats-container {
                grid-template-columns: 1fr; /* 1 column for mobile */
            }
            
            table {
                display: block; /* Make tables scrollable on mobile */
                overflow-x: auto; /* Allow horizontal scrolling */
            }
            
            .dashboard-header {
                flex-direction: column; /* Stack header elements on mobile */
                align-items: flex-start;
            }
            
            .dashboard-header h1 {
                margin-bottom: 10px;
            }
            
            .btn {
                margin-bottom: 5px; /* Add space between stacked buttons */
            }
            
            .modal-content {
                width: 90%;
                margin: 20% auto;
            }
        }
        
        @media (max-width: 480px) {
            .card {
                padding: 15px; /* Reduce padding on small screens */
            }
            
            .form-group input, .form-group select, .form-group textarea {
                font-size: 16px; /* Prevent zoom on iOS */
            }
            
            .stat-card .number {
                font-size: 2rem; /* Smaller font size on mobile */
            }
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
        }
        
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 50%;
            max-width: 600px;
            position: relative;
        }
        
        .close-modal {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close-modal:hover {
            color: #f44336;
        }
        
        /* Search results styling */
        .search-container {
            position: relative;
        }
        
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 100;
            display: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .search-result-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        
        .search-result-item:last-child {
            border-bottom: none;
        }
        
        .search-result-item:hover {
            background-color: #f5f5f5;
        }
        
        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-active {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .status-overdue {
            background-color: #ffebee;
            color: #d32f2f;
        }
        
        .status-success {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        
        .status-warning {
            background-color: #fff8e1;
            color: #f57c00;
        }
        
        /* Add styles for the E-books link in the header */
        .main-nav .ebook-link {
            background: linear-gradient(45deg, #FF6B6B, #FF8E53);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .main-nav .ebook-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
            color: white; /* Ensure text stays white on hover */
        }
        
        .main-nav .ebook-link i {
            font-size: 1.1rem;
        }
        
        /* Make the E-books link the first item in the navigation */
        .main-nav ul {
            display: flex;
            align-items: center;
        }
        
        .main-nav ul li:first-child {
            order: 2;
        }
        
        .main-nav ul li:nth-child(2) {
            order: 3;
        }
        
        .main-nav ul li:nth-child(3) {
            order: 1;
        }
        
        .main-nav ul li:nth-child(4) {
            order: 4;
        }
        
        /* Add styles for the logout link */
        .main-nav .logout-link {
            background: linear-gradient(45deg, #f44336, #d32f2f);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(244, 67, 54, 0.3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .main-nav .logout-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(244, 67, 54, 0.4);
            color: white;
        }
        
        .main-nav .logout-link i {
            font-size: 1.1rem;
        }

        /* Reports section specific styles */
        .reports-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .chart-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .chart-card h3 {
            color: #2c3e50;
            font-size: 1.2rem;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chart-card h3 i {
            color: #4CAF50;
        }
        
        .chart-container {
            height: 250px;
            position: relative;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-right: 5px;
        }
        
        .badge-success {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        
        .badge-danger {
            background-color: #ffebee;
            color: #d32f2f;
        }
        
        .badge-info {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .filter-form {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }
        
        .filter-form .row {
            display: flex;
            flex-wrap: wrap;
            margin: -10px;
        }
        
        .filter-form .col {
            flex: 1;
            padding: 10px;
            min-width: 200px;
        }
        
        .filter-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .export-btn {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 8px 15px;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(76, 175, 80, 0.2);
        }
        
        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(76, 175, 80, 0.3);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .card-header h3 {
            margin: 0;
            font-size: 1.2rem;
            color: #2c3e50;
        }
        
        .card-tools {
            display: flex;
            gap: 10px;
        }
        
        .info-box {
            display: flex;
            min-height: 80px;
            background: #fff;
            width: 100%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .info-box-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            font-size: 1.8rem;
            background: rgba(0,0,0,0.1);
            color: #fff;
        }
        
        .info-box-content {
            padding: 15px;
            flex: 1;
        }
        
        .info-box-text {
            display: block;
            font-size: 1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 5px;
        }
        
        .info-box-number {
            display: block;
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .bg-info {
            background-color: #17a2b8 !important;
        }
        
        .bg-success {
            background-color: #28a745 !important;
        }
        
        .bg-warning {
            background-color: #ffc107 !important;
        }
        
        .bg-danger {
            background-color: #dc3545 !important;
        }
        
        .elevation-1 {
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }
        
        .col-12 {
            flex: 0 0 100%;
            max-width: 100%;
        }
        
        .col-md-3 {
            flex: 0 0 25%;
            max-width: 25%;
        }
        
        .col-md-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }
        
        @media (max-width: 768px) {
            .col-md-3, .col-md-4 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
        
        .mb-3 {
            margin-bottom: 1rem !important;
        }
        
        .mt-4 {
            margin-top: 1.5rem !important;
        }
        
        .mb-4 {
            margin-bottom: 1.5rem !important;
        }
        
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table-bordered {
            border: 1px solid #dee2e6;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0,0,0,.05);
        }
        
        .btn-sm {
            padding: .25rem .5rem;
            font-size: .875rem;
            line-height: 1.5;
            border-radius: .2rem;
        }
        
        .btn-success {
            color: #fff;
            background-color: #28a745;
            border-color: #28a745;
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .form-control {
            display: block;
            width: 100%;
            height: calc(1.5em + .75rem + 2px);
            padding: .375rem .75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: .25rem;
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        }
        
        .form-control:focus {
            color: #495057;
            background-color: #fff;
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        /* Reports Section Styles */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }
        
        .col-md-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
            padding-right: 15px;
            padding-left: 15px;
            margin-bottom: 30px;
        }
        
        .card-header {
            padding: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            background: transparent;
        }
        
        .card-title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .card-tools {
            float: right;
        }
        
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #2c3e50;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 12px;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }
        
        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .table-bordered {
            border: 1px solid #dee2e6;
        }
        
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
        }
        
        .mt-4 {
            margin-top: 2rem;
        }
        
        .mb-4 {
            margin-bottom: 2rem;
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .form-control {
            display: block;
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            color: #495057;
            background-color: #fff;
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        /* Chart container styles */
        .chart-container {
            position: relative;
            margin: auto;
            height: 250px;
            width: 100%;
        }
        
        canvas {
            max-width: 100%;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <div class="logo">
                <img src="../assets/images/logo.png" alt="Marigold Library Logo">
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="#catalog">Catalog</a></li>
                    <li><a href="../pages/ebooks.php" class="ebook-link"><i class="fas fa-tablet-alt"></i> Access E-Books</a></li>
                    <li><a href="../pages/contact.php">Contact</a></li>
                    <li><a href="auth/logout_handler.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="dashboard-container">
        <?php 
        if(isset($_SESSION['success'])) {
            $successMessage = $_SESSION['success'];
            unset($_SESSION['success']);
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    showSuccess('" . htmlspecialchars($successMessage, ENT_QUOTES) . "');
                });
            </script>";
        }
        ?>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/images/logo.png" alt="Marigold Library">
                <h3>Library Management</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="#overview" class="active"><i class="fas fa-home"></i> Overview</a></li>
                <li><a href="#books"><i class="fas fa-book"></i> Books</a></li>
                <li><a href="#students"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="#loans"><i class="fas fa-exchange-alt"></i> Loans</a></li>
                <li><a href="#reports"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="#users"><i class="fas fa-users"></i> Users</a></li>
            </ul>
        </div>

        <!-- Main Content Area -->
        <div class="content-area">
            <!-- Overview Section -->
            <section id="overview" class="dashboard-section active">
                <div class="dashboard-header">
                    <h1>Dashboard Overview</h1>
                    <div>
                        <span id="current-date"></span>
                    </div>
                </div>
                
                <div class="stats-container">
                    <div class="stat-card">
                        <i class="fas fa-book-open fa-2x" style="color: #4CAF50; margin-bottom: 15px;"></i>
                        <h3>Total Books</h3>
                        <div class="number"><?php echo $totalBooks; ?></div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-users fa-2x" style="color: #2196F3; margin-bottom: 15px;"></i>
                        <h3>Active Members</h3>
                        <div class="number"><?php echo $totalStudents; ?></div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-hand-holding-book fa-2x" style="color: #FF9800; margin-bottom: 15px;"></i>
                        <h3>Books Loaned</h3>
                        <div class="number"><?php echo $activeLoans; ?></div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-exclamation-circle fa-2x" style="color: #f44336; margin-bottom: 15px;"></i>
                        <h3>Overdue</h3>
                        <div class="number"><?php echo $overdueLoans; ?></div>
                    </div>
                </div>
            </section>

            <!-- Books Section -->
            <section id="books" class="dashboard-section">
                <div class="dashboard-header">
                    <h1>Books Management</h1>
                    <div>
                        <a href="books/add_book.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Book
                        </a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="form-group">
                        <input type="text" id="book-search" placeholder="Search books by title, author, or category...">
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th><i class="fas fa-fingerprint" style="margin-right: 5px;"></i>Book ID</th>
                                <th><i class="fas fa-barcode" style="margin-right: 5px;"></i>ISBN</th>
                                <th><i class="fas fa-book-open" style="margin-right: 5px;"></i>Title</th>
                                <th><i class="fas fa-pen-fancy" style="margin-right: 5px;"></i>Author</th>
                                <th><i class="fas fa-folder" style="margin-right: 5px;"></i>Category</th>
                                <th><i class="fas fa-book-reader" style="margin-right: 5px;"></i>Available Copies</th>
                                <th><i class="fas fa-book-reader" style="margin-right: 5px;"></i>Total Copies</th>
                                <th><i class="fas fa-cogs" style="margin-right: 5px;"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="books-table-body">
                            <!-- Books will be loaded dynamically via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Students Section -->
            <section id="students" class="dashboard-section">
                <div class="dashboard-header">
                    <h1>Student Management</h1>
                    <div>
                        <button class="btn btn-primary" id="add-student-btn">
                            <i class="fas fa-user-plus"></i> Register New Student
                        </button>
                    </div>
                </div>
                
                <div class="card">
                    <div class="form-group">
                        <input type="text" id="student-search" placeholder="Search students by name, email, or student number...">
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>Student Number</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Students will be loaded dynamically via JavaScript -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Student Form Modal -->
                <div id="student-form-modal" class="modal">
                    <div class="modal-content">
                        <span class="close-modal">&times;</span>
                        <h2 id="student-form-title">Register New Student</h2>
                        <form id="student-form">
                            <input type="hidden" id="student-id" name="student_id">
                            
                            <div class="form-group">
                                <label for="student-number">Student Number</label>
                                <input type="text" id="student-number" name="student_number" required>
                                <span class="error-message" id="student-number-error"></span>
                            </div>
                            
                            <div class="form-group">
                                <label for="first-name">First Name</label>
                                <input type="text" id="first-name" name="first_name" required>
                                <span class="error-message" id="first-name-error"></span>
                            </div>
                            
                            <div class="form-group">
                                <label for="last-name">Last Name</label>
                                <input type="text" id="last-name" name="last_name" required>
                                <span class="error-message" id="last-name-error"></span>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" required>
                                <span class="error-message" id="email-error"></span>
                            </div>
                            
                            <div class="form-group">
                                <label for="date-of-birth">Date of Birth</label>
                                <input type="date" id="date-of-birth" name="date_of_birth" required>
                                <span class="error-message" id="date-of-birth-error"></span>
                            </div>
                            
                            <div class="form-group">
                                <label for="registration-date">Registration Date</label>
                                <input type="date" id="registration-date" name="registration_date" required>
                                <span class="error-message" id="registration-date-error"></span>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Student
                            </button>
                        </form>
                    </div>
                </div>
            </section>

            <!-- Loans Section -->
            <section id="loans" class="dashboard-section">
                <div class="dashboard-header">
                    <h1>Borrow & Return Books</h1>
                </div>
                
                <!-- Borrow Section -->
                <div class="card">
                    <h2><i class="fas fa-book-reader"></i> Borrow Books</h2>
                    <form id="borrow-form">
                        <div class="form-group">
                            <label for="borrow-student">Search Student</label>
                            <div class="search-container">
                                <input type="text" id="borrow-student" placeholder="Search student by name or ID...">
                                <div id="student-search-results" class="search-results"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="borrow-book">Search Book</label>
                            <div class="search-container">
                                <input type="text" id="borrow-book" placeholder="Search book by title or ISBN...">
                                <div id="book-search-results" class="search-results"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="due-date">Due Date</label>
                            <input type="date" id="due-date" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" id="confirm-borrow-btn">
                            <i class="fas fa-check" style="margin-right: 5px;"></i>Confirm Borrow
                        </button>
                    </form>
                </div>
                
                <!-- Return Section -->
                <div class="card">
                    <h2><i class="fas fa-exchange-alt"></i> Active Loans</h2>
                    <div class="form-group">
                        <input type="text" id="return-search" placeholder="Search borrowed books by student or book title...">
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>Book ID</th>
                                <th>Title</th>
                                <th>Student</th>
                                <th>Borrow Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="active-loans-table">
                            <!-- Active loans will be loaded dynamically via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Reports Section -->
            <section id="reports" class="dashboard-section">
                <div class="dashboard-header">
                    <h1>Reports</h1>
                </div>
                
                <!-- Loan Statistics Cards -->
                <div id="loan-stats-container"></div>
                
                <!-- Charts Row -->
                <div class="row">
                    <div class="col-md-4">
                    <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Overdue Loans</h3>
                        </div>
                            <div class="card-body">
                                <canvas id="overdue-chart" height="250"></canvas>
                    </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Monthly Loans</h3>
                </div>
                            <div class="card-body">
                                <canvas id="monthly-loans-chart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Popular Books</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="popular-books-chart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Users Section -->
            <section id="users" class="dashboard-section">
                <div class="dashboard-header">
                    <h1>User Management</h1>
                </div>
                
                <div class="card">
                    <div class="form-group">
                        <input type="text" id="user-search" placeholder="Search users by name or email...">
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th><i class="fas fa-id-badge"></i> User ID</th>
                                <th><i class="fas fa-user"></i> Name</th>
                                <th><i class="fas fa-envelope"></i> Email</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body">
                            <!-- Users will be loaded dynamically via JavaScript -->
                        </tbody>
                    </table>
                </div>
                
                <!-- User Form Modal -->
                <div id="user-modal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2 id="modal-title">User Details</h2>
                        <form id="user-form">
                            <input type="hidden" id="user-id">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password">
                                <small>Leave blank to keep existing password when editing</small>
                            </div>
                            <div class="form-group">
                                <label for="confirm-password">Confirm Password</label>
                                <input type="password" id="confirm-password">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/dashboard.js"></script>
    <script src="../assets/js/validation.js"></script>
    <script src="../assets/js/messages.js"></script>
    <script src="../assets/js/books.js"></script>
    <script src="../assets/js/students.js"></script>
    <script src="../assets/js/loans.js"></script>
    <script src="../assets/js/users.js"></script>
    <script src="../assets/js/reports.js"></script>
</body>
</html>

