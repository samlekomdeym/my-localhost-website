<?php
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

// Database connection function
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            logMessage('ERROR', 'Database connection failed: ' . $e->getMessage());
            die('Database connection failed. Please try again later.');
        }
    }
    
    return $pdo;
}

// Fetch single record
function fetchOne($query, $params = []) {
    try {
        $db = getDB();
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        logMessage('ERROR', 'Query error in fetchOne: ' . $e->getMessage());
        return false;
    }
}

// Fetch multiple records
function fetchAll($query, $params = []) {
    try {
        $db = getDB();
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        logMessage('ERROR', 'Query error in fetchAll: ' . $e->getMessage());
        return [];
    }
}

// Execute query (INSERT, UPDATE, DELETE)
function executeQuery($query, $params = []) {
    try {
        $db = getDB();
        $stmt = $db->prepare($query);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        logMessage('ERROR', 'Query error in executeQuery: ' . $e->getMessage());
        return false;
    }
}

// Get last insert ID
function getLastInsertId() {
    try {
        $db = getDB();
        return $db->lastInsertId();
    } catch (PDOException $e) {
        logMessage('ERROR', 'Error getting last insert ID: ' . $e->getMessage());
        return false;
    }
}

// Count records
function countRecords($table, $where = '', $params = []) {
    try {
        $query = "SELECT COUNT(*) as total FROM $table";
        if (!empty($where)) {
            $query .= " WHERE $where";
        }
        
        $result = fetchOne($query, $params);
        return $result ? $result['total'] : 0;
    } catch (Exception $e) {
        logMessage('ERROR', 'Error counting records: ' . $e->getMessage());
        return 0;
    }
}

// Begin transaction
function beginTransaction() {
    try {
        $db = getDB();
        return $db->beginTransaction();
    } catch (PDOException $e) {
        logMessage('ERROR', 'Error beginning transaction: ' . $e->getMessage());
        return false;
    }
}

// Commit transaction
function commitTransaction() {
    try {
        $db = getDB();
        return $db->commit();
    } catch (PDOException $e) {
        logMessage('ERROR', 'Error committing transaction: ' . $e->getMessage());
        return false;
    }
}

// Rollback transaction
function rollbackTransaction() {
    try {
        $db = getDB();
        return $db->rollback();
    } catch (PDOException $e) {
        logMessage('ERROR', 'Error rolling back transaction: ' . $e->getMessage());
        return false;
    }
}

// Test database connection
function testDatabaseConnection() {
    try {
        $db = getDB();
        $stmt = $db->query('SELECT 1');
        return $stmt !== false;
    } catch (Exception $e) {
        return false;
    }
}
?>
