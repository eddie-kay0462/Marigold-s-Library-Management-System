-- Marigold's Library Management System Database Schema

-- Drop tables if they exist (for clean installation)
DROP TABLE IF EXISTS loan_history;
DROP TABLE IF EXISTS active_loans;
DROP TABLE IF EXISTS book_copies;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS ebooks;
DROP TABLE IF EXISTS ebooks_categories;

-- Create roles table
CREATE TABLE roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create users table (for staff/librarians)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE RESTRICT
);

-- Create categories table
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create books table
CREATE TABLE books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    category_id INT NOT NULL,
    publisher VARCHAR(100),
    publication_year INT,
    edition VARCHAR(20),
    description TEXT,
    cover_image VARCHAR(255),
    total_copies INT NOT NULL DEFAULT 0,
    available_copies INT NOT NULL DEFAULT 0,
    location VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT
);

-- Create book_copies table (for tracking individual copies)
CREATE TABLE book_copies (
    copy_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    copy_number INT NOT NULL,
    status ENUM('Available', 'Borrowed', 'Lost', 'Damaged', 'Under Repair') DEFAULT 'Available',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    UNIQUE KEY unique_book_copy (book_id, copy_number)
);

-- Create students table
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    student_number VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    date_of_birth DATE,
    registration_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create active_loans table
CREATE TABLE active_loans (
    loan_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    copy_id INT NOT NULL,
    loan_date DATE NOT NULL,
    due_date DATE NOT NULL,
    returned_date DATE NULL,
    status ENUM('Active', 'Returned', 'Overdue') DEFAULT 'Active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE RESTRICT,
    FOREIGN KEY (copy_id) REFERENCES book_copies(copy_id) ON DELETE RESTRICT
);

-- Create loan_history table
CREATE TABLE loan_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    student_id INT NOT NULL,
    copy_id INT NOT NULL,
    loan_date DATE NOT NULL,
    due_date DATE NOT NULL,
    returned_date DATE NOT NULL,
    status ENUM('On Time', 'Late', 'Damaged') NOT NULL,
    late_fee DECIMAL(10, 2) DEFAULT 0.00,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (loan_id) REFERENCES active_loans(loan_id) ON DELETE RESTRICT,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE RESTRICT,
    FOREIGN KEY (copy_id) REFERENCES book_copies(copy_id) ON DELETE RESTRICT
);

-- Create settings table
CREATE TABLE settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create ebooks_categories table
CREATE TABLE ebooks_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create ebooks table
CREATE TABLE ebooks (
    ebook_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    description TEXT,
    file_url VARCHAR(255) NOT NULL,
    cover_image VARCHAR(255),
    icon VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES ebooks_categories(category_id) ON DELETE RESTRICT
);

-- Insert default roles
INSERT INTO roles (role_name, description) VALUES
('Administrator', 'Full system access with all permissions'),
('Librarian', 'Can manage books, loans, and students'),
('Manager', 'Can view reports and manage staff'),
('Assistant', 'Can process loans and returns'),
('Staff', 'Basic access to view information');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('library_name', 'Marigold''s Library', 'Name of the library'),
('library_address', '123 Book Street, Reading, CA 90210', 'Physical address of the library'),
('library_phone', '(555) 123-4567', 'Contact phone number'),
('library_email', 'contact@marigoldlibrary.com', 'Contact email address'),
('loan_duration', '14', 'Default loan duration in days'),
('max_books', '5', 'Maximum number of books a student can borrow'),
('late_fee', '0.50', 'Late fee per day in dollars');

-- Insert default categories
INSERT INTO categories (category_name, description) VALUES
('Fiction', 'Fictional literature and novels'),
('Non-Fiction', 'Non-fictional books and educational materials'),
('Science Fiction', 'Science fiction and fantasy books'),
('Fantasy', 'Fantasy literature and novels'),
('Romance', 'Romance novels and literature'),
('Mystery', 'Mystery and detective novels'),
('Biography', 'Biographical books and memoirs'),
('History', 'Historical books and accounts'),
('Science', 'Scientific books and publications'),
('Technology', 'Books about technology and computing');

-- Insert default ebooks categories
INSERT INTO ebooks_categories (category_name, description, icon) VALUES
('Academic Books', 'Educational and academic resources', 'fa-book-open'),
('Literature', 'Fictional and non-fictional literature', 'fa-book'),
('Technology', 'Books about technology and computing', 'fa-laptop-code');

-- Create indexes for better performance
CREATE INDEX idx_books_isbn ON books(isbn);
CREATE INDEX idx_books_title ON books(title);
CREATE INDEX idx_books_author ON books(author);
CREATE INDEX idx_students_email ON students(email);
CREATE INDEX idx_students_student_number ON students(student_number);
CREATE INDEX idx_active_loans_status ON active_loans(status);
CREATE INDEX idx_loan_history_student ON loan_history(student_id);
CREATE INDEX idx_loan_history_copy ON loan_history(copy_id); 