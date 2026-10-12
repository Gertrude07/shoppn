<?php
require_once __DIR__ . '/../classes/CustomerClass.php';

class CustomerController {
    protected $model;

    public function __construct() {
        $this->model = new CustomerClass();
    }

    // Task 3
    public function register($data) {
        if ($this->model->emailExists($data['email'])) {
            return ['success' => false, 'error' => 'Email already registered.'];
        }

        $newId = $this->model->addCustomer(
            $data['name'],
            $data['email'],
            $data['pass'],
            $data['country'],
            $data['city'],
            $data['contact']
        );

        if ($newId === false) {
            return ['success' => false, 'error' => 'Registration failed. Please try again.'];
        }

        return [
            'success'       => true,
            'customer_id'   => $newId,
            'customer_name' => $data['name'],
            'user_role'     => 2,
        ];
    }

    // Task 4
    public function login($email, $pass) {
        $row = $this->model->login($email, $pass);

        if ($row === false) {
            return ['success' => false, 'error' => 'Invalid email or password.'];
        }

        return ['success' => true, 'customer' => $row];
    }

    // Account — change the customer's name and email
    public function updateProfile($id, $name, $email) {
        if ($this->model->emailTakenByOther($email, $id)) {
            return ['success' => false, 'error' => 'That email is already used by another account.'];
        }

        if (!$this->model->updateProfile($id, $name, $email)) {
            return ['success' => false, 'error' => 'Unable to update your details. Please try again.'];
        }

        return ['success' => true];
    }

    // Account — change password after checking the current one
    public function changePassword($id, $currentPass, $newPass) {
        $row = $this->model->getCustomerById($id);

        if (!$row || !password_verify($currentPass, $row['customer_pass'])) {
            return ['success' => false, 'error' => 'Your current password is incorrect.'];
        }

        if (!$this->model->updatePassword($id, $newPass)) {
            return ['success' => false, 'error' => 'Unable to change your password. Please try again.'];
        }

        return ['success' => true];
    }
}
