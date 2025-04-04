# Marigold's Library Management System

A comprehensive library management system with both physical book management and e-book access capabilities.

## Database Schema

The system uses a MySQL database with the following structure:

### Core Tables

1. **roles** - User roles (Administrator, Librarian, etc.)
2. **users** - Staff/librarian accounts
3. **categories** - Book categories
4. **books** - Book information
5. **book_copies** - Individual copies of books
6. **students** - Library members
7. **active_loans** - Current book loans
8. **loan_history** - Historical loan records
9. **settings** - System configuration
10. **ebooks_categories** - E-book categories
11. **ebooks** - Digital book information

### Key Features Supported

- **User Management**: Role-based access control for staff
- **Book Management**: Track physical books, copies, and availability
- **Student Management**: Register and manage library members
- **Loan Processing**: Borrow and return books with due date tracking
- **E-books Access**: Digital library with categorized e-books
- **Settings Management**: Configure library parameters
- **Reporting**: Track loan history and generate reports

## Backend Development Guide

### Technology Stack Recommendation

- **Backend Framework**: Node.js with Express.js
- **Database**: MySQL (as defined in the schema)
- **Authentication**: JWT (JSON Web Tokens)
- **API Documentation**: Swagger/OpenAPI

### Implementation Steps

1. **Set Up Database**
   - Create a MySQL database
   - Run the `database_schema.sql` script to create tables and insert initial data

2. **Create API Endpoints**

   #### Authentication
   - POST /api/auth/login
   - POST /api/auth/logout
   - GET /api/auth/profile

   #### Books Management
   - GET /api/books - List all books
   - GET /api/books/:id - Get book details
   - POST /api/books - Add new book
   - PUT /api/books/:id - Update book
   - DELETE /api/books/:id - Delete book

   #### Students Management
   - GET /api/students - List all students
   - GET /api/students/:id - Get student details
   - POST /api/students - Register new student
   - PUT /api/students/:id - Update student
   - DELETE /api/students/:id - Delete student

   #### Loans Management
   - GET /api/loans - List active loans
   - POST /api/loans - Create new loan
   - PUT /api/loans/:id/return - Return a book
   - GET /api/loans/history - Get loan history

   #### E-books Management
   - GET /api/ebooks - List all e-books
   - GET /api/ebooks/categories - List e-book categories
   - GET /api/ebooks/:id - Get e-book details
   - POST /api/ebooks - Add new e-book
   - PUT /api/ebooks/:id - Update e-book
   - DELETE /api/ebooks/:id - Delete e-book

   #### Settings Management
   - GET /api/settings - Get all settings
   - PUT /api/settings/:key - Update a setting

   #### Reports
   - GET /api/reports/overdue - Get overdue books
   - GET /api/reports/popular - Get popular books
   - GET /api/reports/activity - Get recent activity

3. **Implement Authentication & Authorization**
   - JWT-based authentication
   - Role-based access control
   - Password hashing with bcrypt

4. **Connect Frontend to Backend**
   - Update frontend JavaScript to use fetch/axios for API calls
   - Implement proper error handling
   - Add loading states for better UX

5. **Add Data Validation**
   - Validate all inputs on both client and server
   - Implement proper error messages

6. **Implement Search Functionality**
   - Add search endpoints for books, students, and e-books
   - Implement filtering and pagination

## Security Considerations

- Use HTTPS for all API endpoints
- Implement rate limiting to prevent abuse
- Sanitize all user inputs
- Use parameterized queries to prevent SQL injection
- Implement proper session management
- Regular security audits

## Deployment

- Set up a production database
- Configure environment variables
- Set up a web server (Nginx/Apache)
- Use PM2 or similar for process management
- Implement proper logging
- Set up regular backups

## Next Steps

1. Set up your development environment
2. Create the database using the provided schema
3. Start implementing the backend API endpoints
4. Connect the frontend to the backend
5. Test thoroughly before deployment 