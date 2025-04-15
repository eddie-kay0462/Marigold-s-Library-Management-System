<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

// Database connection
require_once('../config/database.php');
$database = new Database();
$conn = $database->getConnection();

// Get counts from database
$staffCount = 0;
$studentCount = 0;
$activeCount = 0;
$bookCount = 0;

try {
    // Count staff (users who are not students)
    $staffQuery = "SELECT COUNT(*) as count FROM users WHERE role_id != 5 AND is_active = 1";
    $staffStmt = $conn->query($staffQuery);
    $staffCount = $staffStmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Count students
    $studentQuery = "SELECT COUNT(*) as count FROM students WHERE is_active = 1";
    $studentStmt = $conn->query($studentQuery);
    $studentCount = $studentStmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Count books
    $bookQuery = "SELECT COUNT(*) as count FROM books";
    $bookStmt = $conn->query($bookQuery);
    $bookCount = $bookStmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Count all active members (staff + students)
    $activeCount = $staffCount + $studentCount;

} catch (Exception $e) {
    // Log error silently
    error_log("Error fetching dashboard counts: " . $e->getMessage());
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/dashboard.js"></script>
    <style>
        /* Additional styles for the dashboard */
        .dashboard-container {
            display: flex;
            min-height: calc(100vh - 80px);
            margin-top: 80px;
            flex-direction: row;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
        }
        
        .sidebar {
            width: 250px;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: sticky; /* Change from fixed to sticky */
            top: 80px; /* Position it right below the header */
            height: calc(100vh - 80px); /* Adjust height to account for header */
            overflow-y: auto; /* Allow scrolling if content exceeds height */
            transition: all 0.3s ease; /* Smooth transition for responsive changes */
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
                </ul>
            </nav>
        </div>
    </header>

    <div class="dashboard-container">
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
                <li><a href="#settings"><i class="fas fa-cog"></i> Settings</a></li>
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
                        <i class="fas fa-users fa-2x" style="color: #2196F3; margin-bottom: 15px;"></i>
                        <h3>Active Members</h3>
                        <div class="number"><?php echo $activeCount; ?></div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-user-graduate fa-2x" style="color: #FF9800; margin-bottom: 15px;"></i>
                        <h3>Students</h3>
                        <div class="number"><?php echo $studentCount; ?></div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-users-cog fa-2x" style="color: #4CAF50; margin-bottom: 15px;"></i>
                        <h3>Staff</h3>
                        <div class="number"><?php echo $staffCount; ?></div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-book fa-2x" style="color: #f44336; margin-bottom: 15px;"></i>
                        <h3>Total Books</h3>
                        <div class="number"><?php echo $bookCount; ?></div>
                    </div>
                </div>
                
                <div class="card">
                    <h2><i class="fas fa-history" style="color: #4CAF50; margin-right: 10px;"></i>Recent Activities</h2>
                    <table>
                        <thead>
                            <tr>
                                <th><i class="far fa-calendar-alt" style="margin-right: 5px;"></i>Date</th>
                                <th><i class="fas fa-tasks" style="margin-right: 5px;"></i>Activity</th>
                                <th><i class="fas fa-user" style="margin-right: 5px;"></i>Member</th>
                                <th><i class="fas fa-book" style="margin-right: 5px;"></i>Book</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2023-06-15</td>
                                <td>Book Borrowed</td>
                                <td>John Smith</td>
                                <td>The Great Gatsby</td>
                            </tr>
                            <tr>
                                <td>2023-06-14</td>
                                <td>Book Returned</td>
                                <td>Emily Johnson</td>
                                <td>To Kill a Mockingbird</td>
                            </tr>
                            <tr>
                                <td>2023-06-14</td>
                                <td>New Member</td>
                                <td>Michael Brown</td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>2023-06-13</td>
                                <td>Book Added</td>
                                <td>-</td>
                                <td>The Hobbit</td>
                            </tr>
                            <tr>
                                <td>2023-06-12</td>
                                <td>Book Borrowed</td>
                                <td>Sarah Wilson</td>
                                <td>Pride and Prejudice</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Books Section -->
            <section id="books" class="dashboard-section">
                <div class="dashboard-header">
                    <h1>Books Management</h1>
                    <div>
                        <a href="#" class="btn btn-primary" id="add-book-btn">
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
                                <th><i class="fas fa-cogs" style="margin-right: 5px;"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>B001</td>
                                <td>978-0743273565</td>
                                <td>The Great Gatsby</td>
                                <td>F. Scott Fitzgerald</td>
                                <td>Fiction</td>
                                <td>3</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning" onclick="editBook('B001')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteBook('B001')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-info" onclick="viewBook('B001')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>B002</td>
                                <td>978-0446310789</td>
                                <td>To Kill a Mockingbird</td>
                                <td>Harper Lee</td>
                                <td>Fiction</td>
                                <td>2</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning" onclick="editBook('B002')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteBook('B002')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-info" onclick="viewBook('B002')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>B003</td>
                                <td>978-0451524935</td>
                                <td>1984</td>
                                <td>George Orwell</td>
                                <td>Science Fiction</td>
                                <td>4</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning" onclick="editBook('B003')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteBook('B003')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-info" onclick="viewBook('B003')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Students Section -->
            <section id="students" class="dashboard-section">
                <div class="dashboard-header">
                    <h1>Student Management</h1>
                    <div>
                        <a href="#" class="btn btn-primary" id="add-student-btn"><i class="material-icons-round">add</i> Register New Student</a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="form-group">
                        <input type="text" id="student-search" placeholder="Search students...">
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>🎓 Student ID</th>
                                <th>🏫 Name</th>
                                <th>📚 Borrowed Books</th>
                                <th>✏ Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>S001</td>
                                <td>John Smith</td>
                                <td>2</td>
                                <td>
                                    <button type="button" class="btn btn-secondary" onclick="editStudent('${student.id}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="deleteStudent('${student.id}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="viewStudent('${student.id}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>S002</td>
                                <td>Emily Johnson</td>
                                <td>1</td>
                                <td>
                                    <button type="button" class="btn btn-secondary" onclick="editStudent('${student.id}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="deleteStudent('${student.id}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="viewStudent('${student.id}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>S003</td>
                                <td>Michael Brown</td>
                                <td>0</td>
                                <td>
                                    <button type="button" class="btn btn-secondary" onclick="editStudent('${student.id}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="deleteStudent('${student.id}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="viewStudent('${student.id}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>S004</td>
                                <td>Sarah Wilson</td>
                                <td>3</td>
                                <td>
                                    <button type="button" class="btn btn-secondary" onclick="editStudent('${student.id}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="deleteStudent('${student.id}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="viewStudent('${student.id}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>S005</td>
                                <td>David Lee</td>
                                <td>1</td>
                                <td>
                                    <button type="button" class="btn btn-secondary" onclick="editStudent('${student.id}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="deleteStudent('${student.id}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="viewStudent('${student.id}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Loans Section -->
            <section id="loans" class="dashboard-section">
                <div class="dashboard-header">
                    <h1>Borrow & Return Books</h1>
                </div>
                
                <!-- Borrow Section -->
                <div class="card">
                    <h2>5️⃣ Borrow Books</h2>
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
                    <h2>Return Books</h2>
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
                        <tbody>
                            <tr>
                                <td>B002</td>
                                <td>To Kill a Mockingbird</td>
                                <td>John Smith</td>
                                <td>2023-06-15</td>
                                <td>2023-06-29</td>
                                <td><span class="status-badge status-active">Active</span></td>
                                <td>
                                    <button type="button" class="btn btn-primary" onclick="returnBook('${book.id}')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>B004</td>
                                <td>Pride and Prejudice</td>
                                <td>Sarah Wilson</td>
                                <td>2023-06-12</td>
                                <td>2023-06-26</td>
                                <td><span class="status-badge status-active">Active</span></td>
                                <td>
                                    <button type="button" class="btn btn-primary" onclick="returnBook('${book.id}')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>B003</td>
                                <td>1984</td>
                                <td>David Lee</td>
                                <td>2023-06-10</td>
                                <td>2023-06-24</td>
                                <td><span class="status-badge status-overdue">Overdue</span></td>
                                <td>
                                    <button type="button" class="btn btn-primary" onclick="returnBook('${book.id}')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>B005</td>
                                <td>The Hobbit</td>
                                <td>Michael Brown</td>
                                <td>2023-06-05</td>
                                <td>2023-06-19</td>
                                <td><span class="status-badge status-overdue">Overdue</span></td>
                                <td>
                                    <button type="button" class="btn btn-primary" onclick="returnBook('${book.id}')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Returns Section -->
            <section id="returns" class="dashboard-section">
                <div class="dashboard-header">
                    <h1>Return History</h1>
                </div>
                
                <div class="card">
                    <div class="form-group">
                        <input type="text" id="return-history-search" placeholder="Search return history...">
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>Return ID</th>
                                <th>Book</th>
                                <th>Student</th>
                                <th>Loan Date</th>
                                <th>Return Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>R001</td>
                                <td>To Kill a Mockingbird</td>
                                <td>Emily Johnson</td>
                                <td>2023-05-30</td>
                                <td>2023-06-14</td>
                                <td><span class="status-badge status-success">On Time</span></td>
                            </tr>
                            <tr>
                                <td>R002</td>
                                <td>The Catcher in the Rye</td>
                                <td>David Lee</td>
                                <td>2023-05-25</td>
                                <td>2023-06-10</td>
                                <td><span class="status-badge status-success">On Time</span></td>
                            </tr>
                            <tr>
                                <td>R003</td>
                                <td>Lord of the Flies</td>
                                <td>Sarah Wilson</td>
                                <td>2023-05-20</td>
                                <td>2023-06-08</td>
                                <td><span class="status-badge status-warning">Late</span></td>
                            </tr>
                            <tr>
                                <td>R004</td>
                                <td>Animal Farm</td>
                                <td>John Smith</td>
                                <td>2023-05-15</td>
                                <td>2023-06-05</td>
                                <td><span class="status-badge status-warning">Late</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Reports Section -->
            <section id="reports" class="dashboard-section">
                <div class="dashboard-header">
                    <h1>Reports</h1>
                </div>
                
                <div class="stats-container">
                    <div class="card">
                        <h2><i class="fas fa-chart-pie" style="color: #4CAF50; margin-right: 10px;"></i>Books by Category</h2>
                        <div style="height: 200px; background-color: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                            Chart Placeholder
                        </div>
                    </div>
                    <div class="stat-card">
                        <h2><i class="fas fa-chart-line" style="color: #4CAF50; margin-right: 10px;"></i>Monthly Loans</h2>
                        <div style="height: 200px; background-color: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                            Chart Placeholder
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <h2><i class="fas fa-star" style="color: #4CAF50; margin-right: 10px;"></i>Popular Books</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Times Borrowed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>To Kill a Mockingbird</td>
                                <td>Harper Lee</td>
                                <td>32</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>1984</td>
                                <td>George Orwell</td>
                                <td>28</td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>The Great Gatsby</td>
                                <td>F. Scott Fitzgerald</td>
                                <td>25</td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>Pride and Prejudice</td>
                                <td>Jane Austen</td>
                                <td>23</td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td>The Hobbit</td>
                                <td>J.R.R. Tolkien</td>
                                <td>21</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Settings Section -->
            <section id="settings" class="dashboard-section">
                <div class="dashboard-header">
                    <h1>Settings</h1>
                </div>
                
                <div class="card">
                    <h2>Library Information</h2>
                    <form>
                        <div class="form-group">
                            <label for="library-name">Library Name</label>
                            <input type="text" id="library-name" value="Marigold's Library">
                        </div>
                        <div class="form-group">
                            <label for="library-address">Address</label>
                            <input type="text" id="library-address" value="123 Book Street, Reading, CA 90210">
                        </div>
                        <div class="form-group">
                            <label for="library-phone">Phone</label>
                            <input type="text" id="library-phone" value="(555) 123-4567">
                        </div>
                        <div class="form-group">
                            <label for="library-email">Email</label>
                            <input type="email" id="library-email" value="contact@marigoldlibrary.com">
                        </div>
                        <button type="submit" class="btn">Save Changes</button>
                    </form>
                </div>
                
                <div class="card">
                    <h2><i class="fas fa-cog" style="color: #4CAF50; margin-right: 10px;"></i>Loan Settings</h2>
                    <form>
                        <div class="form-group">
                            <label for="loan-duration">Default Loan Duration (days)</label>
                            <input type="number" id="loan-duration" value="14">
                        </div>
                        <div class="form-group">
                            <label for="max-books">Maximum Books Per Member</label>
                            <input type="number" id="max-books" value="5">
                        </div>
                        <div class="form-group">
                            <label for="late-fee">Late Fee (per day)</label>
                            <input type="text" id="late-fee" value="$0.50">
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-save" style="margin-right: 5px;"></i>Save Changes
                        </button>
                    </form>
                </div>
            </section>

            <!-- Users Section (Admin Only) -->
            <section id="users" class="dashboard-section">
                <div class="dashboard-header">
                    <h1>User Management</h1>
                    <div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="form-group">
                        <input type="text" id="user-search" placeholder="Search users by name, email, or role...">
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>👤 User ID</th>
                                <th>👨‍💼 Name</th>
                                <th>🔑 Role</th>
                                <th>📧 Email</th>
                                <th>⚙ Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>U001</td>
                                <td>Admin User</td>
                                <td>Administrator</td>
                                <td>admin@marigoldlibrary.com</td>
                                <td>
                                    <button type="button" class="btn btn-secondary" onclick="editUser('${user.id}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="deleteUser('${user.id}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="viewUser('${user.id}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>U002</td>
                                <td>Jane Librarian</td>
                                <td>Librarian</td>
                                <td>jane@marigoldlibrary.com</td>
                                <td>
                                    <button type="button" class="btn btn-secondary" onclick="editUser('${user.id}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="deleteUser('${user.id}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="viewUser('${user.id}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>U003</td>
                                <td>John Assistant</td>
                                <td>Assistant</td>
                                <td>john@marigoldlibrary.com</td>
                                <td>
                                    <button type="button" class="btn btn-secondary" onclick="editUser('${user.id}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="deleteUser('${user.id}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="viewUser('${user.id}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>U004</td>
                                <td>Sarah Manager</td>
                                <td>Manager</td>
                                <td>sarah@marigoldlibrary.com</td>
                                <td>
                                    <button type="button" class="btn btn-secondary" onclick="editUser('${user.id}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="deleteUser('${user.id}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="viewUser('${user.id}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>U005</td>
                                <td>Michael Staff</td>
                                <td>Staff</td>
                                <td>michael@marigoldlibrary.com</td>
                                <td>
                                    <button type="button" class="btn btn-secondary" onclick="editUser('${user.id}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="deleteUser('${user.id}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="viewUser('${user.id}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <!-- Book Form Modal -->
    <div class="modal fade" id="bookFormModal" tabindex="-1" aria-labelledby="bookFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookFormModalLabel">Add New Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="bookForm" class="needs-validation" novalidate>
                        <div class="form-group mb-3">
                            <label for="book_id" class="form-label">Book ID</label>
                            <input type="text" class="form-control" id="book_id" name="book_id" required>
                            <div class="invalid-feedback">Please enter a book ID.</div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="isbn" class="form-label">ISBN</label>
                            <input type="text" class="form-control" id="isbn" name="isbn" required>
                            <div class="invalid-feedback">Please enter an ISBN.</div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                            <div class="invalid-feedback">Please enter a title.</div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="author" class="form-label">Author</label>
                            <input type="text" class="form-control" id="author" name="author" required>
                            <div class="invalid-feedback">Please enter an author.</div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-control" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Fiction">Fiction</option>
                                <option value="Non-Fiction">Non-Fiction</option>
                                <option value="Science Fiction">Science Fiction</option>
                                <option value="Fantasy">Fantasy</option>
                                <option value="Mystery">Mystery</option>
                                <option value="Romance">Romance</option>
                                <option value="Thriller">Thriller</option>
                                <option value="Horror">Horror</option>
                                <option value="Biography">Biography</option>
                                <option value="History">History</option>
                                <option value="Science">Science</option>
                                <option value="Technology">Technology</option>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="copies" class="form-label">Available Copies</label>
                            <input type="number" class="form-control" id="copies" name="copies" min="0" required>
                            <div class="invalid-feedback">Please enter the number of copies.</div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveBookBtn">Save Book</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>