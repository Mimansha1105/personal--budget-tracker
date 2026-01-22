<?php
declare(strict_types=1);

// --------------------------
// SIMPLE PASSWORD HELPERS
// --------------------------
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

// SIMPLE password validation (minimum 6 chars only)
function validatePassword(string $password): array {
    $errors = [];

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    return $errors;
}

class User {
    private $con;
    private $user_id;
    private $firstname;
    private $lastname;
    private $email;

    public function __construct($con) {
        $this->con = $con;
        
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['user_id'])) {
            $this->user_id = $_SESSION['user_id'];
            $this->firstname = $_SESSION['firstname'] ?? null;
            $this->lastname = $_SESSION['lastname'] ?? null;
            $this->email = $_SESSION['useremail'] ?? null;
        }
    }

    // --------------------------
    // LOGIN
    // --------------------------
    public function login(string $email, string $password): bool {

        $stmt = $this->con->prepare(
            "SELECT user_id, firstname, lastname, email, `password`
             FROM users WHERE email = ? LIMIT 1"
        );
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return false; // email not found
        }

        $row = $result->fetch_assoc();
        $stmt->close();

        if (verifyPassword($password, $row['password'])) {

            session_regenerate_id(true);

            $this->user_id = $row['user_id'];
            $this->firstname = $row['firstname'];
            $this->lastname = $row['lastname'];
            $this->email = $row['email'];

            $_SESSION['user_id'] = $this->user_id;
            $_SESSION['firstname'] = $this->firstname;
            $_SESSION['lastname'] = $this->lastname;
            $_SESSION['useremail'] = $this->email;

            return true;
        }

        return false; // wrong password
    }

    // --------------------------
    // LOGOUT
    // --------------------------
    public function logout() {
        session_unset();
        session_destroy();
    }

    // --------------------------
    // REGISTER (FIXED)
    // --------------------------
    public function register(string $firstname, string $lastname, string $email, string $password): bool {

        // 1. Check if email exists
        $checkStmt = $this->con->prepare(
            "SELECT user_id FROM users WHERE email = ? LIMIT 1"
        );
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $checkStmt->close();
            return false; // email exists
        }
        $checkStmt->close();

        // 2. Simple password validation
        $errors = validatePassword($password);
        if (!empty($errors)) {
            return false; // password too short
        }

        // 3. Hash password
        $passwordHash = hashPassword($password);

        // 4. Insert user
        $insertStmt = $this->con->prepare(
            "INSERT INTO users (firstname, lastname, email, `password`)
             VALUES (?, ?, ?, ?)"
        );

        $insertStmt->bind_param(
            "ssss",
            $firstname,
            $lastname,
            $email,
            $passwordHash
        );

        $success = $insertStmt->execute();
        $insertStmt->close();

        return $success;
    }

    // --------------------------
    // GET PROFILE
    // --------------------------
    public function getProfile() {
        if (!$this->isLoggedIn()) return null;
        
        $stmt = $this->con->prepare(
            "SELECT user_id, firstname, lastname, email FROM users WHERE user_id = ?"
        );
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row;
    }

    // --------------------------
    // CHECK LOGIN STATE
    // --------------------------
    public function isLoggedIn(): bool {
        return isset($this->user_id);
    }

    // Getters
    public function getUserId(): ?int { return $this->user_id; }
    public function getEmail(): ?string { return $this->email; }
}
