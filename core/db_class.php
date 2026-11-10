<?php
class Database {
    protected $conn;

    public function __construct() {
        require_once __DIR__ . '/db_cred.php';

        // DB_PORT is only used for local XAMPP setups (e.g. port 3307).
        // On the live server it's left blank, so we skip passing it —
        // mysqli requires an int here, and passing an empty string
        // causes a fatal error instead of just using the default port.
        if (defined('DB_PORT') && DB_PORT !== '' && DB_PORT !== null) {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int) DB_PORT);
        } else {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        }

        if ($this->conn->connect_error) {
            error_log($this->conn->connect_error);
            die('Connection failed.');
        }
    }
}
?>
