<?php
/**
 * Database Configuration and Connection
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'logistics_db';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        // Reuse existing connection to preserve session state (e.g., lastInsertId)
        if ($this->conn instanceof PDO) {
            return $this->conn;
        }
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
    
    public function query($sql, $params = []) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $conn = $this->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($data);
        return $conn->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setParts);
        
        // Convert WHERE clause to use named placeholders if it uses positional ones
        $whereClause = $where;
        $finalParams = $data;
        
        if (!empty($whereParams)) {
            // Check if WHERE clause uses positional placeholders (?)
            if (strpos($where, '?') !== false) {
                // Convert positional to named placeholders
                $whereClause = '';
                $paramIndex = 0;
                $whereLength = strlen($where);
                
                for ($i = 0; $i < $whereLength; $i++) {
                    if ($where[$i] === '?') {
                        $paramName = ':where_' . $paramIndex;
                        $whereClause .= $paramName;
                        // PDO expects parameter keys without the colon prefix
                        $finalParams['where_' . $paramIndex] = $whereParams[$paramIndex];
                        $paramIndex++;
                    } else {
                        $whereClause .= $where[$i];
                    }
                }
            } else {
                // WHERE clause already uses named placeholders
                $finalParams = array_merge($data, $whereParams);
            }
        }
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$whereClause}";
        
        $stmt = $this->query($sql, $finalParams);
        return $stmt->rowCount();
    }
    
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
}
?>
