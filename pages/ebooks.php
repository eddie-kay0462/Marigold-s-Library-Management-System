<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Books - Margold Montessori Library</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/ebooks.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #4CAF50;
            --primary-dark: #45a049;
            --secondary-color: #2196F3;
            --accent-color: #FF9800;
            --text-dark: #2c3e50;
            --text-light: #666;
            --bg-light: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        header {
            background: var(--white);
            box-shadow: var(--shadow);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo img {
            height: 50px;
            margin-right: 15px;
        }
        
        .logo h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }
        
        nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        nav ul li {
            margin-left: 25px;
        }
        
        nav ul li a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            transition: var(--transition);
            padding: 8px 0;
            position: relative;
        }
        
        nav ul li a:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: var(--transition);
        }
        
        nav ul li a:hover {
            color: var(--primary-color);
        }
        
        nav ul li a:hover:after {
            width: 100%;
        }
        
        .ebooks-container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 0 20px;
        }
        
        .ebooks-header {
            text-align: center;
            margin-bottom: 50px;
            padding: 40px 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 20px;
            color: var(--white);
            box-shadow: var(--shadow);
        }
        
        .ebooks-header h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .ebooks-header h1 i {
            margin-right: 15px;
            font-size: 2.8rem;
        }
        
        .ebooks-header p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.9;
        }
        
        .category-section {
            background: var(--white);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: var(--shadow);
        }
        
        .category-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
        }
        
        .category-header i {
            font-size: 2rem;
            margin-right: 15px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .category-header h2 {
            font-size: 1.8rem;
            margin: 0;
        }
        
        .ebooks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .ebook-card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow);
            padding: 25px;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .ebook-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color), var(--primary-color));
            background-size: 200% 100%;
            animation: gradientMove 3s linear infinite;
        }
        
        @keyframes gradientMove {
            0% { background-position: 100% 0; }
            100% { background-position: -100% 0; }
        }
        
        .ebook-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .ebook-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }
        
        .ebook-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .ebook-description {
            color: var(--text-light);
            margin-bottom: 20px;
            flex-grow: 1;
        }
        
        .ebook-link {
            display: inline-flex;
            align-items: center;
            color: var(--primary-color);
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            margin-top: auto;
        }
        
        .ebook-link i {
            margin-left: 5px;
            transition: var(--transition);
        }
        
        .ebook-link:hover i {
            transform: translateX(5px);
        }
        
        .back-to-dashboard {
            display: inline-flex;
            align-items: center;
            padding: 12px 25px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            margin-bottom: 30px;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.2);
        }
        
        .back-to-dashboard i {
            margin-right: 8px;
        }
        
        .back-to-dashboard:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.3);
        }
        
        footer {
            background: var(--white);
            padding: 40px 0 20px;
            margin-top: 60px;
            box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .footer-section h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 1.2rem;
        }
        
        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-section ul li {
            margin-bottom: 10px;
        }
        
        .footer-section ul li a {
            color: var(--text-light);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .footer-section ul li a:hover {
            color: var(--primary-color);
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            margin-top: 30px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .logo {
                margin-bottom: 15px;
                justify-content: center;
            }
            
            nav ul {
                justify-content: center;
            }
            
            nav ul li {
                margin: 0 10px;
            }
            
            .ebooks-grid {
                grid-template-columns: 1fr;
            }
            
            .ebooks-header h1 {
                font-size: 2rem;
            }
            
            .category-section {
                padding: 20px;
            }
        }

        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .content-wrapper {
            flex: 1;
            padding: 20px;
        }

        .page-footer {
            background-color: var(--white);
            text-align: center;
            padding: 1.5rem;
            margin-top: auto;
            border-top: 1px solid var(--gray-medium);
            width: 100%;
            position: relative;
            bottom: 0;
        }

        .page-footer p {
            color: var(--text-dark);
            margin: 0;
            font-size: 0.9rem;
        }

        main {
            flex: 1 0 auto;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <header class="header">
            <div class="nav-container">
                <div class="logo">
                    <a href="../index.php">
                        <img src="../assets/images/logo.png" alt="Margold Library Logo">
                    </a>
                </div>
                <nav class="main-nav">
                    <ul>
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="../pages/dashboard.php">Dashboard</a></li>
                        <li><a href="../pages/about.php">About</a></li>
                        <li><a href="../pages/contact.php">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <div class="content-wrapper">
            <div class="ebooks-container">
                <a href="../pages/dashboard.php" class="back-to-dashboard">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>

                <div class="ebooks-header">
                    <h1><i class="fas fa-tablet-alt"></i> Digital Library</h1>
                    <p>Access our collection of e-books and digital resources</p>
                </div>

                <div class="category-section">
                    <div class="category-header">
                        <i class="fas fa-book-open"></i>
                        <h2>Academic Books</h2>
                    </div>
                    <div class="ebooks-grid">
                        <a href="https://drive.google.com/file/d/your-file-id/view" target="_blank" class="ebook-card">
                            <div class="ebook-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h3 class="ebook-title">Introduction to Computer Science</h3>
                            <p class="ebook-description">A comprehensive guide to computer science fundamentals, algorithms, and programming concepts.</p>
                            <span class="ebook-link">Access Book <i class="fas fa-external-link-alt"></i></span>
                        </a>

                        <a href="https://drive.google.com/file/d/your-file-id/view" target="_blank" class="ebook-card">
                            <div class="ebook-icon">
                                <i class="fas fa-flask"></i>
                            </div>
                            <h3 class="ebook-title">Physics Principles</h3>
                            <p class="ebook-description">Explore the fundamental principles of physics with detailed explanations and examples.</p>
                            <span class="ebook-link">Access Book <i class="fas fa-external-link-alt"></i></span>
                        </a>

                        <a href="https://drive.google.com/file/d/your-file-id/view" target="_blank" class="ebook-card">
                            <div class="ebook-icon">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <h3 class="ebook-title">Advanced Mathematics</h3>
                            <p class="ebook-description">A comprehensive guide to advanced mathematical concepts and problem-solving techniques.</p>
                            <span class="ebook-link">Access Book <i class="fas fa-external-link-alt"></i></span>
                        </a>
                    </div>
                </div>

                <div class="category-section">
                    <div class="category-header">
                        <i class="fas fa-book"></i>
                        <h2>Literature</h2>
                    </div>
                    <div class="ebooks-grid">
                        <a href="https://drive.google.com/file/d/your-file-id/view" target="_blank" class="ebook-card">
                            <div class="ebook-icon">
                                <i class="fas fa-feather-alt"></i>
                            </div>
                            <h3 class="ebook-title">Classic Novels Collection</h3>
                            <p class="ebook-description">A collection of timeless literary masterpieces from renowned authors around the world.</p>
                            <span class="ebook-link">Access Book <i class="fas fa-external-link-alt"></i></span>
                        </a>

                        <a href="https://drive.google.com/file/d/your-file-id/view" target="_blank" class="ebook-card">
                            <div class="ebook-icon">
                                <i class="fas fa-pen"></i>
                            </div>
                            <h3 class="ebook-title">Poetry Anthology</h3>
                            <p class="ebook-description">A beautiful collection of poems from various poets and literary movements.</p>
                            <span class="ebook-link">Access Book <i class="fas fa-external-link-alt"></i></span>
                        </a>

                        <a href="https://drive.google.com/file/d/your-file-id/view" target="_blank" class="ebook-card">
                            <div class="ebook-icon">
                                <i class="fas fa-theater-masks"></i>
                            </div>
                            <h3 class="ebook-title">Drama Collection</h3>
                            <p class="ebook-description">A collection of classic plays and dramatic works from renowned playwrights.</p>
                            <span class="ebook-link">Access Book <i class="fas fa-external-link-alt"></i></span>
                        </a>
                    </div>
                </div>

                <div class="category-section">
                    <div class="category-header">
                        <i class="fas fa-laptop-code"></i>
                        <h2>Technology</h2>
                    </div>
                    <div class="ebooks-grid">
                        <a href="https://drive.google.com/file/d/your-file-id/view" target="_blank" class="ebook-card">
                            <div class="ebook-icon">
                                <i class="fas fa-code"></i>
                            </div>
                            <h3 class="ebook-title">Web Development Guide</h3>
                            <p class="ebook-description">Learn modern web development techniques and best practices.</p>
                            <span class="ebook-link">Access Book <i class="fas fa-external-link-alt"></i></span>
                        </a>

                        <a href="https://drive.google.com/file/d/your-file-id/view" target="_blank" class="ebook-card">
                            <div class="ebook-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <h3 class="ebook-title">Mobile App Development</h3>
                            <p class="ebook-description">A comprehensive guide to developing mobile applications for iOS and Android.</p>
                            <span class="ebook-link">Access Book <i class="fas fa-external-link-alt"></i></span>
                        </a>

                        <a href="https://drive.google.com/file/d/your-file-id/view" target="_blank" class="ebook-card">
                            <div class="ebook-icon">
                                <i class="fas fa-database"></i>
                            </div>
                            <h3 class="ebook-title">Database Management</h3>
                            <p class="ebook-description">Learn about database design, SQL, and database management systems.</p>
                            <span class="ebook-link">Access Book <i class="fas fa-external-link-alt"></i></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <footer class="page-footer">
            <p>© 2025 Margold Montessori School Library. All rights reserved.</p>
        </footer>
    </div>
    
    <script src="../assets/js/ebooks.js"></script>
</body>
</html> 