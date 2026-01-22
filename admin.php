<?php
session_start();
include("config.php");

// --- Database Class ---
class Database {
    private $host ="127.0.0.1:3306";
    private $user = "root";
    private $pass = "";
    private $dbname = "dailyexpense";
    private $conn;

    public function connect() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
        if ($this->conn->connect_error) {
            die("DB Connection failed: " . $this->conn->connect_error);
        }
        return $this->conn;
    }
}

// --- User Class ---
class User {
    public $userId;
    public $firstname;
    public $lastname;
    public $email;
    private $conn;

    public function __construct($conn, $email) {
        $this->conn = $conn;
        $stmt = $conn->prepare("SELECT user_id, firstname, lastname, email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $this->userId = $row['user_id'];
            $this->firstname = $row['firstname'];
            $this->lastname = $row['lastname'];
            $this->email = $row['email'];
        }
    }

    public function login($email, $password) {
        $passwordHash = md5($password);
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email=? AND password=?");
        $stmt->bind_param("ss", $email, $passwordHash);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows === 1;
    }

    public function updateProfile($firstname, $lastname) {
        $stmt = $this->conn->prepare("UPDATE users SET firstname=?, lastname=? WHERE user_id=?");
        $stmt->bind_param("ssi", $firstname, $lastname, $this->userId);
        return $stmt->execute();
    }

    public function viewReports() {
        // Example: Fetch all expenses for this user
        $stmt = $this->conn->prepare("SELECT * FROM transaction_table WHERE user_id=?");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        return $stmt->get_result();
    }
}

// --- Admin Class ---
class Admin {
    public $adminId;
    private $conn;

    public function __construct($conn, $adminId) {
        $this->conn = $conn;
        $this->adminId = $adminId;
    }

    public function manageUsers() {
        $result = $this->conn->query("SELECT user_id, firstname, lastname, email FROM users");
        return $result;
    }

    public function manageBudget() {
        $result = $this->conn->query("SELECT * FROM transaction_table");
        return $result;
    }

    public function generateReport() {
        // Example: total expenses per user
        $result = $this->conn->query("SELECT user_id, SUM(expense) AS total_expense FROM transaction_table GROUP BY user_id");
        return $result;
    }
}

// --- Initialize DB and Current User/Admin ---
$db = new Database();
$conn = $db->connect();

if (isset($_SESSION['email'])) {
    $currentUser = new User($conn, $_SESSION['email']);
} elseif (isset($_SESSION['admin_id'])) {
    $currentAdmin = new Admin($conn, $_SESSION['admin_id']);
} else {
    header("Location: login.php");
    exit();
}

// --- Example Usage ---
// User updating profile
if (isset($_POST['update_profile']) && isset($currentUser)) {
    $currentUser->updateProfile($_POST['firstname'], $_POST['lastname']);
}

// Admin generating report
if (isset($_GET['admin_report']) && isset($currentAdmin)) {
    $report = $currentAdmin->generateReport();
    while ($row = $report->fetch_assoc()) {
        echo "UserID: {$row['user_id']} Total Expenses: {$row['total_expense']}<br>";
    }
}
?>
