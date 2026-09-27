<?php
require_once __DIR__ . '/../core/db_class.php';

class CustomerClass extends Database {

    // Task 3 — check if an email is already registered
    public function emailExists($email) {
        $stmt = $this->conn->prepare('SELECT customer_email FROM customer WHERE customer_email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    // Task 3 — insert a new customer, password hashed with bcrypt
    public function addCustomer($name, $email, $pass, $country, $city, $contact) {
        $hash = password_hash($pass, PASSWORD_BCRYPT);

        $stmt = $this->conn->prepare(
            'INSERT INTO customer (customer_name, customer_email, customer_pass, customer_country, customer_city, customer_contact, user_role)
             VALUES (?, ?, ?, ?, ?, ?, 2)'
        );
        $stmt->bind_param('ssssss', $name, $email, $hash, $country, $city, $contact);
        $success = $stmt->execute();
        $newId = $this->conn->insert_id;
        $stmt->close();

        return $success ? $newId : false;
    }

    // Task 4 — fetch one customer row by email
    public function getCustomerByEmail($email) {
        $stmt = $this->conn->prepare('SELECT * FROM customer WHERE customer_email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row ?: false;
    }

    // Task 4 — verify credentials, return the customer row on success
    public function login($email, $pass) {
        $row = $this->getCustomerByEmail($email);

        if ($row && password_verify($pass, $row['customer_pass'])) {
            return $row;
        }

        return false;
    }
}
