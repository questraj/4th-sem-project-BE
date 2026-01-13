<?php
require_once __DIR__ . '/../config/db.php';

class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Updated register function with Bank Details
    public function register($email, $password, $first_name, $middle_name, $last_name, $bank_name = '', $bank_account_no = '') {
        // Check if email exists
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ["success" => false, "message" => "Email already registered"];
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Updated INSERT statement
        $stmt = $this->conn->prepare("
            INSERT INTO users (email, password, first_name, middle_name, last_name, bank_name, bank_account_no) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssss", $email, $hashedPassword, $first_name, $middle_name, $last_name, $bank_name, $bank_account_no);
        
        if ($stmt->execute()) {
            return ["success" => true, "message" => "Registration successful"];
        }
        return ["success" => false, "message" => "Registration failed"];
    }

    // ... (Keep login function as is) ...
    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            return ["success" => true, "user" => $user];
        }
        return ["success" => false, "message" => "Invalid email or password"];
    }
}
?>