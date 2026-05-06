<?php
/*
 * Database helper class.
 * It centralizes the MySQL connection and exposes small helper methods used by
 * the User, Book, and Borrowing classes.
 */

class Database {
    private $connection;
    private $host;
    private $user;
    private $pass;
    private $db;

    public function __construct() {
        // Load connection details from config/config.php constants.
        $this->host = DB_HOST;
        $this->user = DB_USER;
        $this->pass = DB_PASS;
        $this->db = DB_NAME;
        $this->connect();
    }

    private function connect() {
        // Create one mysqli connection for this Database object.
        $this->connection = new mysqli($this->host, $this->user, $this->pass, $this->db);

        if ($this->connection->connect_error) {
            die("Database connection failed: " . $this->connection->connect_error);
        }

        // UTF-8 supports normal text data for titles, names, and descriptions.
        $this->connection->set_charset("utf8");
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql) {
        // Used only for fixed SQL statements that do not include user input.
        $result = $this->connection->query($sql);

        if (!$result) {
            die("Query error: " . $this->connection->error);
        }

        return $result;
    }

    public function prepare($sql) {
        // Prepared statements protect user input from SQL injection.
        return $this->connection->prepare($sql);
    }

    public function executePrepared($sql, $types = '', ...$params) {
        // Convenience wrapper for prepare + bind_param + execute.
        $stmt = $this->prepare($sql);

        if ($types !== '' && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt;
    }

    public function escape($data) {
        // Escapes special characters before building LIKE search terms.
        return $this->connection->real_escape_string($data);
    }

    public function lastInsertId() {
        return $this->connection->insert_id;
    }

    public function affectedRows() {
        return $this->connection->affected_rows;
    }

    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
?>
