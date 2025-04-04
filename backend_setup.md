# Backend Setup Guide for Marigold's Library Management System

This guide will help you set up the PHP backend environment for the Library Management System.

## Prerequisites

- PHP (v7.4 or higher)
- MySQL (v8 or higher)
- Apache or Nginx web server
- Composer (PHP package manager)
- Git

## Step 1: Project Setup

1. Create a new directory for your backend:

```bash
mkdir marigold-library-backend
cd marigold-library-backend
```

2. Initialize a new Composer project:

```bash
composer init
```

3. Install the necessary dependencies:

```bash
composer require slim/slim slim/psr7 php-di/php-di vlucas/phpdotenv firebase/php-jwt monolog/monolog
```

## Step 2: Project Structure

Create the following directory structure:

```
marigold-library-backend/
├── config/                 # Configuration files
│   ├── database.php        # Database connection
│   └── config.php          # App configuration
├── controllers/            # Request handlers
│   ├── AuthController.php
│   ├── BookController.php
│   ├── StudentController.php
│   ├── LoanController.php
│   ├── EbookController.php
│   ├── SettingController.php
│   └── ReportController.php
├── middleware/             # Custom middleware
│   ├── AuthMiddleware.php  # Authentication middleware
│   ├── ErrorHandler.php    # Error handling middleware
│   └── Validator.php       # Request validation
├── models/                 # Database models
│   ├── User.php
│   ├── Role.php
│   ├── Book.php
│   ├── Category.php
│   ├── BookCopy.php
│   ├── Student.php
│   ├── Loan.php
│   ├── LoanHistory.php
│   ├── Setting.php
│   ├── Ebook.php
│   └── EbookCategory.php
├── routes/                 # API routes
│   ├── auth.php
│   ├── books.php
│   ├── students.php
│   ├── loans.php
│   ├── ebooks.php
│   ├── settings.php
│   └── reports.php
├── utils/                  # Utility functions
│   ├── Logger.php          # Logging utility
│   └── Helpers.php         # Helper functions
├── public/                 # Public directory (web root)
│   └── index.php           # Entry point
├── .env                    # Environment variables
├── .gitignore              # Git ignore file
└── composer.json           # Composer configuration
```

## Step 3: Environment Configuration

Create a `.env` file in the root directory:

```
# Server Configuration
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=your_password
DB_NAME=marigold_library
DB_PORT=3306

# JWT Configuration
JWT_SECRET=your_jwt_secret_key
JWT_EXPIRES_IN=86400

# Logging
LOG_LEVEL=info
```

Create a `.gitignore` file:

```
# Dependencies
/vendor/
composer.lock

# Environment variables
.env
.env.local
.env.development.local
.env.test.local
.env.production.local

# Logs
/logs/
*.log

# IDE files
.idea/
.vscode/
*.swp
*.swo

# OS files
.DS_Store
Thumbs.db
```

## Step 4: Database Configuration

Create `config/database.php`:

```php
<?php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $conn;

    public function __construct() {
        $this->host = $_ENV['DB_HOST'];
        $this->db_name = $_ENV['DB_NAME'];
        $this->username = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASSWORD'];
        $this->port = $_ENV['DB_PORT'];
    }

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};port={$this->port};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }
}
```

## Step 5: Application Setup

Create `public/index.php`:

```php
<?php

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Create Container
$container = new Container();
AppFactory::setContainer($container);

// Create App
$app = AppFactory::create();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Add routes
require __DIR__ . '/../routes/auth.php';
require __DIR__ . '/../routes/books.php';
require __DIR__ . '/../routes/students.php';
require __DIR__ . '/../routes/loans.php';
require __DIR__ . '/../routes/ebooks.php';
require __DIR__ . '/../routes/settings.php';
require __DIR__ . '/../routes/reports.php';

// Run app
$app->run();
```

## Step 6: Create a Basic Model

Create `models/User.php` as an example:

```php
<?php

class User {
    private $conn;
    private $table = 'users';

    public $user_id;
    public $role_id;
    public $username;
    public $password;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $address;
    public $is_active;
    public $last_login;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get user by ID
    public function getById($id) {
        $query = "SELECT u.*, r.role_name 
                  FROM {$this->table} u
                  JOIN roles r ON u.role_id = r.role_id
                  WHERE u.user_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    // Get user by username
    public function getByUsername($username) {
        $query = "SELECT u.*, r.role_name 
                  FROM {$this->table} u
                  JOIN roles r ON u.role_id = r.role_id
                  WHERE u.username = :username";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    // Create user
    public function create() {
        $query = "INSERT INTO {$this->table} 
                  (role_id, username, password, first_name, last_name, email, phone, address, is_active)
                  VALUES 
                  (:role_id, :username, :password, :first_name, :last_name, :email, :phone, :address, :is_active)";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash password
        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Bind parameters
        $stmt->bindParam(':role_id', $this->role_id);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':is_active', $this->is_active);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    // Update user
    public function update() {
        $query = "UPDATE {$this->table} 
                  SET role_id = :role_id, 
                      username = :username, 
                      first_name = :first_name, 
                      last_name = :last_name, 
                      email = :email, 
                      phone = :phone, 
                      address = :address, 
                      is_active = :is_active
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':role_id', $this->role_id);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':user_id', $this->user_id);
        
        return $stmt->execute();
    }

    // Update password
    public function updatePassword() {
        $query = "UPDATE {$this->table} 
                  SET password = :password
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash password
        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Bind parameters
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':user_id', $this->user_id);
        
        return $stmt->execute();
    }

    // Update last login
    public function updateLastLogin() {
        $query = "UPDATE {$this->table} 
                  SET last_login = NOW()
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        
        return $stmt->execute();
    }

    // Verify password
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }
}
```

## Step 7: Create a Basic Controller

Create `controllers/AuthController.php` as an example:

```php
<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController {
    private $db;
    private $user;

    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
    }

    // Login
    public function login(Request $request, Response $response) {
        $data = $request->getParsedBody();
        
        // Validate input
        if (!isset($data['username']) || !isset($data['password'])) {
            $response->getBody()->write(json_encode([
                'message' => 'Username and password are required'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        $username = $data['username'];
        $password = $data['password'];
        
        // Get user by username
        $userData = $this->user->getByUsername($username);
        
        // Check if user exists
        if (!$userData) {
            $response->getBody()->write(json_encode([
                'message' => 'Invalid credentials'
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        
        // Check if password is correct
        if (!password_verify($password, $userData['password'])) {
            $response->getBody()->write(json_encode([
                'message' => 'Invalid credentials'
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        
        // Check if user is active
        if (!$userData['is_active']) {
            $response->getBody()->write(json_encode([
                'message' => 'Account is deactivated'
            ]));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }
        
        // Update last login
        $this->user->user_id = $userData['user_id'];
        $this->user->updateLastLogin();
        
        // Generate JWT token
        $token = JWT::encode([
            'id' => $userData['user_id'],
            'username' => $userData['username'],
            'role' => $userData['role_name'],
            'iat' => time(),
            'exp' => time() + $_ENV['JWT_EXPIRES_IN']
        ], $_ENV['JWT_SECRET'], 'HS256');
        
        // Send response
        $response->getBody()->write(json_encode([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $userData['user_id'],
                'username' => $userData['username'],
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'email' => $userData['email'],
                'role' => $userData['role_name']
            ]
        ]));
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Get user profile
    public function getProfile(Request $request, Response $response) {
        $userId = $request->getAttribute('user_id');
        
        $userData = $this->user->getById($userId);
        
        if (!$userData) {
            $response->getBody()->write(json_encode([
                'message' => 'User not found'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        // Remove password from response
        unset($userData['password']);
        
        $response->getBody()->write(json_encode([
            'user' => $userData
        ]));
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}
```

## Step 8: Create a Basic Route

Create `routes/auth.php`:

```php
<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

// Auth routes
$app->group('/api/auth', function (RouteCollectorProxy $group) {
    // Login route
    $group->post('/login', function (Request $request, Response $response) {
        $authController = new AuthController($this->get('db'));
        return $authController->login($request, $response);
    });
    
    // Get profile route
    $group->get('/profile', function (Request $request, Response $response) {
        $authController = new AuthController($this->get('db'));
        return $authController->getProfile($request, $response);
    })->add(new AuthMiddleware($this->get('db')));
});
```

## Step 9: Create Authentication Middleware

Create `middleware/AuthMiddleware.php`:

```php
<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response {
        $response = new Response();
        
        // Get token from header
        $authHeader = $request->getHeaderLine('Authorization');
        if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $response->getBody()->write(json_encode([
                'message' => 'Authentication required'
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        
        $token = $matches[1];
        
        try {
            // Verify token
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            
            // Check if user exists
            $user = new User($this->db);
            $userData = $user->getById($decoded->id);
            
            if (!$userData) {
                $response->getBody()->write(json_encode([
                    'message' => 'User not found'
                ]));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }
            
            // Check if user is active
            if (!$userData['is_active']) {
                $response->getBody()->write(json_encode([
                    'message' => 'Account is deactivated'
                ]));
                return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
            }
            
            // Add user data to request attributes
            $request = $request->withAttribute('user_id', $decoded->id);
            $request = $request->withAttribute('username', $decoded->username);
            $request = $request->withAttribute('role', $decoded->role);
            
            return $handler->handle($request);
        } catch (\Firebase\JWT\ExpiredException $e) {
            $response->getBody()->write(json_encode([
                'message' => 'Token expired'
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'message' => 'Invalid token'
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    }
}
```

## Step 10: Create Error Handler Middleware

Create `middleware/ErrorHandler.php`:

```php
<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpMethodNotAllowedException;

class ErrorHandler {
    public function __invoke(
        Request $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): Response {
        $response = new Response();
        $statusCode = 500;
        $message = 'Internal Server Error';
        
        // Handle specific exceptions
        if ($exception instanceof HttpNotFoundException) {
            $statusCode = 404;
            $message = 'Route not found';
        } elseif ($exception instanceof HttpMethodNotAllowedException) {
            $statusCode = 405;
            $message = 'Method not allowed';
        } elseif ($exception instanceof PDOException) {
            $statusCode = 500;
            $message = 'Database error';
        }
        
        // Log error if needed
        if ($logErrors) {
            // Add your logging logic here
        }
        
        $response->getBody()->write(json_encode([
            'message' => $message,
            'error' => $_ENV['APP_DEBUG'] ? $exception->getMessage() : null
        ]));
        
        return $response
            ->withStatus($statusCode)
            ->withHeader('Content-Type', 'application/json');
    }
}
```

## Step 11: Create Validation Middleware

Create `middleware/Validator.php`:

```php
<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class Validator {
    private $rules;

    public function __construct($rules) {
        $this->rules = $rules;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response {
        $data = $request->getParsedBody();
        $errors = [];
        
        foreach ($this->rules as $field => $rule) {
            if (isset($rule['required']) && $rule['required'] && empty($data[$field])) {
                $errors[$field] = $rule['message'] ?? "{$field} is required";
            }
            
            if (!empty($data[$field])) {
                if (isset($rule['email']) && $rule['email'] && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = $rule['message'] ?? "{$field} must be a valid email";
                }
                
                if (isset($rule['min']) && strlen($data[$field]) < $rule['min']) {
                    $errors[$field] = $rule['message'] ?? "{$field} must be at least {$rule['min']} characters";
                }
                
                if (isset($rule['max']) && strlen($data[$field]) > $rule['max']) {
                    $errors[$field] = $rule['message'] ?? "{$field} must be at most {$rule['max']} characters";
                }
            }
        }
        
        if (!empty($errors)) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                'errors' => $errors
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        
        return $handler->handle($request);
    }
}
```

## Step 12: Update composer.json

Update your `composer.json` file to include these scripts:

```json
{
    "name": "marigold/library-management-system",
    "description": "Backend for Marigold's Library Management System",
    "type": "project",
    "require": {
        "php": ">=7.4",
        "slim/slim": "^4.9",
        "slim/psr7": "^1.5",
        "php-di/php-di": "^6.4",
        "vlucas/phpdotenv": "^5.4",
        "firebase/php-jwt": "^6.3",
        "monolog/monolog": "^2.5"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
    "scripts": {
        "start": "php -S localhost:8000 -t public",
        "test": "phpunit"
    }
}
```

## Step 13: Run the Application

1. Create the MySQL database:

```sql
CREATE DATABASE marigold_library;
```

2. Run the database schema script:

```bash
mysql -u root -p marigold_library < database_schema.sql
```

3. Start the development server:

```bash
composer start
```

## Next Steps

1. Create the remaining models based on the database schema
2. Implement the controllers for each resource
3. Set up the routes for each resource
4. Test the API endpoints using Postman or similar tools
5. Connect the frontend to the backend API

This setup provides a solid foundation for building the PHP backend of your Library Management System. You can expand upon this structure as you implement the remaining functionality. 