<?php
class Database {
    private $connection;
    private $host;
    private $user;
    private $pass;
    private $db;

    public function __construct() {
        $this->host = DB_HOST;
        $this->user = DB_USER;
        $this->pass = DB_PASS;
        $this->db = DB_NAME;
        $this->connect();
    }

    private function connect() {
        $this->connection = new mysqli($this->host, $this->user, $this->pass, $this->db);

        if ($this->connection->connect_error) {
            die("Database connection failed: " . $this->connection->connect_error);
        }

        $this->connection->set_charset("utf8");
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql) {
        $result = $this->connection->query($sql);

        if (!$result) {
            die("Query error: " . $this->connection->error);
        }

        return $result;
    }

    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }

    public function executePrepared($sql, $types = '', ...$params) {
        $stmt = $this->prepare($sql);

        if ($types !== '' && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt;
    }

    public function escape($data) {
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
