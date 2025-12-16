<?php
/**
 * Database Configuration
 * Soka Toto Muda Initiative Trust
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'sokatoto_db';
    private $username = 'root'; // Change this to your database username
    private $password = ''; // Change this to your database password
    private $charset = 'utf8mb4';
    public $conn;

    // Get database connection
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
            // Test connection
            $this->conn->query("SELECT 1");
            
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            
            // Display user-friendly error
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
                // Admin error page
                die("
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Database Connection Error</title>
                        <style>
                            body { font-family: Arial, sans-serif; background: #f8f9fa; padding: 50px; }
                            .error-container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
                            h1 { color: #dc3545; }
                            .code { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; }
                        </style>
                    </head>
                    <body>
                        <div class='error-container'>
                            <h1>⚠️ Database Connection Error</h1>
                            <p>Unable to connect to the database. Please check:</p>
                            <ol>
                                <li>Database server is running (MySQL in XAMPP)</li>
                                <li>Database <span class='code'>sokatoto_db</span> exists</li>
                                <li>Username and password are correct in <span class='code'>config/database.php</span></li>
                            </ol>
                            <div class='code'>
                                <strong>Database Details:</strong><br>
                                Host: localhost<br>
                                Database: sokatoto_db<br>
                                Username: root<br>
                                Password: [empty]
                            </div>
                            <p><strong>Error Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>
                            <a href='login.php'>← Back to Login</a>
                        </div>
                    </body>
                    </html>
                ");
            } else {
                // Public error page
                die("
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Site Maintenance - Soka Toto Muda Initiative Trust</title>
                        <style>
                            body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #0e0c5e 0%, #1a1a8a 100%); color: white; padding: 50px; text-align: center; }
                            .container { max-width: 600px; margin: 0 auto; }
                            h1 { font-size: 48px; margin-bottom: 20px; }
                            p { font-size: 18px; line-height: 1.6; margin-bottom: 30px; opacity: 0.9; }
                            .contact { background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; margin-top: 30px; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <h1>🔄 Site Maintenance</h1>
                            <p>We're currently performing maintenance on our website. Please check back soon.</p>
                            <p>For urgent inquiries, you can contact us directly:</p>
                            <div class='contact'>
                                <p><strong>Email:</strong> stmitrust@gmail.com</p>
                                <p><strong>Phone:</strong> +254 728 274304</p>
                            </div>
                            <p style='margin-top: 30px; font-size: 14px; opacity: 0.7;'>
                                Soka Toto Muda Initiative Trust
                            </p>
                        </div>
                    </body>
                    </html>
                ");
            }
        }

        return $this->conn;
    }

    // Execute a query with parameters
    public function executeQuery($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            error_log("Query Error: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e;
        }
    }

    // Get single row
    public function getRow($sql, $params = []) {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetch();
    }

    // Get all rows
    public function getAll($sql, $params = []) {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetchAll();
    }

    // Insert data and return last insert ID
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->executeQuery($sql, $data);
        
        return $this->conn->lastInsertId();
    }

    // Update data
    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge($data, $whereParams);
        
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->rowCount();
    }

    // Delete data
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->rowCount();
    }

    // Get site settings
    public function getSettings($category = null) {
        $sql = "SELECT setting_key, setting_value FROM admin_settings";
        $params = [];
        
        if ($category) {
            $sql .= " WHERE category = :category";
            $params[':category'] = $category;
        }
        
        $stmt = $this->executeQuery($sql, $params);
        $rows = $stmt->fetchAll();
        
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    }

    // Get single setting
    public function getSetting($key) {
        $sql = "SELECT setting_value FROM admin_settings WHERE setting_key = :key";
        $stmt = $this->executeQuery($sql, [':key' => $key]);
        $row = $stmt->fetch();
        
        return $row ? $row['setting_value'] : null;
    }

    // Update setting
    public function updateSetting($key, $value) {
        $sql = "UPDATE admin_settings SET setting_value = :value, updated_at = NOW() WHERE setting_key = :key";
        return $this->executeQuery($sql, [':value' => $value, ':key' => $key])->rowCount();
    }
}

// Create global database instance
try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Set timezone for database
    $pdo->exec("SET time_zone = '+03:00'");
    
    // Check if required tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        error_log("Warning: No tables found in database sokatoto_db");
    }
    
} catch(Exception $e) {
    // Error already handled in getConnection()
}
?>