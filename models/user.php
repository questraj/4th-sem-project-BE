<?php
require_once __DIR__ . '/../config/db.php';

class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Register a new user
    public function register($email, $password, $first_name = null, $middle_name = null, $last_name = null) {
        // Check if email already exists
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ["success" => false, "message" => "Email already registered"];
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $this->conn->prepare("
            INSERT INTO users (email, password, first_name, middle_name, last_name) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssss", $email, $hashedPassword, $first_name, $middle_name, $last_name);
        if ($stmt->execute()) {
            return ["success" => true, "user_id" => $this->conn->insert_id];
        } else {
            return ["success" => false, "message" => "Registration failed"];
        }
    }

    // Login user
    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            // Successful login
            return ["success" => true, "user" => $user];
        } else {
            return ["success" => false, "message" => "Invalid email or password"];
        }
    }

    // Fetch user info by ID
    public function getUserById($user_id) {
        $stmt = $this->conn->prepare("SELECT id, email, first_name, middle_name, last_name, created_at FROM users WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Update user info
    public function updateUser($user_id, $first_name, $middle_name, $last_name, $email = null) {
        if ($email) {
            // Check if new email is taken
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email=? AND id<>?");
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                return ["success" => false, "message" => "Email already in use"];
            }
        }

        $stmt = $this->conn->prepare("
            UPDATE users 
            SET first_name=?, middle_name=?, last_name=?, email=COALESCE(?, email)
            WHERE id=?
        ");
        $stmt->bind_param("ssssi", $first_name, $middle_name, $last_name, $email, $user_id);

        if ($stmt->execute()) {
            return ["success" => true, "message" => "User updated successfully"];
        } else {
            return ["success" => false, "message" => "Failed to update user"];
        }
    }

    // Change password
    public function changePassword($user_id, $old_password, $new_password) {
        $stmt = $this->conn->prepare("SELECT password FROM users WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user || !password_verify($old_password, $user['password'])) {
            return ["success" => false, "message" => "Old password is incorrect"];
        }

        $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $new_hashed, $user_id);

        if ($stmt->execute()) {
            return ["success" => true, "message" => "Password changed successfully"];
        } else {
            return ["success" => false, "message" => "Failed to change password"];
        }
    }
}
?>
