<?php
// ====================
// 1. REPLACE your existing db.php completely with this:
// ====================

if (!defined('DB_HOST')) {
    require_once __DIR__ . '/config.php';
}

class DBConnection {
    private static $pdo = null;
    
    public static function get() {
        if (self::$pdo === null) {
            try {
                $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => false,
                    PDO::ATTR_TIMEOUT => 10,
                ];
                
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                self::$pdo->exec("SET time_zone = '+05:30'");
                
                // Auto-cleanup on script end
                register_shutdown_function(function() {
                    DBConnection::$pdo = null;
                });
                
            } catch (PDOException $e) {
                if (ENVIRONMENT === 'development') {
                    die('Database Connection Error: ' . $e->getMessage());
                } else {
                    error_log('Database Connection Error: ' . $e->getMessage());
                    die('A database error occurred. Please try again later.');
                }
            }
        }
        return self::$pdo;
    }
}

// Backward compatibility
$pdo = DBConnection::get();
$GLOBALS['pdo'] = $pdo;