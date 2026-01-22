<?php
// session.php
include("config.php"); // Assuming this file provides the $con database connection
// NOTE: Your previous User class already includes session_start() with a check.
// If you are only using this UserSession class for session handling, you need session_start() here.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class UserSession {
    private $con;
    private $userId;
    private $email;
    private $firstName;
    private $lastName;

    public function __construct($con) {
        $this->con = $con;
        $this->checkSession();
    }

    // Check if session exists and load data, or redirect
    private function checkSession() {
        // ⭐️ CRITICAL FIX 1: Check for 'user_id' or 'useremail' (keys set by login.php)
        if (!isset($_SESSION["user_id"])) {
            header("Location: login.php");
            exit();
        }

        // Load data directly from session if available (set during successful login)
        if (isset($_SESSION['user_id']) && isset($_SESSION['firstname']) && isset($_SESSION['lastname']) && isset($_SESSION['useremail'])) {
            $this->userId = $_SESSION['user_id'];
            $this->firstName = $_SESSION['firstname'];
            $this->lastName = $_SESSION['lastname'];
            $this->email = $_SESSION['useremail'];

        } else {
            // Fallback: If some session data is missing, fetch it from the database using the ID/Email
            
            // ⭐️ CRITICAL FIX 2: Use the correct session key 'useremail'
            $this->email = $_SESSION["useremail"] ?? null; 
            
            if ($this->email) {
                 $this->fetchUserData();
            } else {
                // If the email key is also missing, force logout/redirect
                header("Location: logout.php"); 
                exit();
            }
        }
    }

    // Fallback function to fetch user data from DB if session is incomplete
    private function fetchUserData() {
        // Using $this->con, which is a mysqli object from config.php
        $stmt = $this->con->prepare("SELECT user_id, firstname, lastname, email FROM users WHERE email = ?");
        
        if (!$stmt) {
             // Handle prepared statement error
             // die("Prepare failed: " . $this->con->error);
             header("Location: logout.php"); 
             exit();
        }

        $stmt->bind_param("s", $this->email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->userId = $row['user_id'];
            $this->firstName = $row['firstname'];
            $this->lastName = $row['lastname'];
            $this->email = $row['email'];
            
            // Re-populate session in case it was incomplete
            $_SESSION['user_id'] = $this->userId;
            $_SESSION['firstname'] = $this->firstName;
            $_SESSION['lastname'] = $this->lastName;
            $_SESSION['useremail'] = $this->email;
            
        } else {
            // User not found, clear session and redirect
            session_unset();
            session_destroy();
            header("Location: login.php");
            exit();
        }
    }

    // Getters (no changes needed here)
    public function getUserId() {
        return $this->userId;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function getUserName() {
        return $this->firstName . " " . $this->lastName;
    }

    public function getUserEmail() {
        return $this->email;
    }
}

// Initialize session object
// This ensures the session check runs and redirects if the user isn't logged in
$userSession = new UserSession($con);

// Make variables available for other pages (like index.php)
$userid = $userSession->getUserId();
$firstname = $userSession->getFirstName();
$lastname = $userSession->getLastName();
$username = $userSession->getUserName();
$useremail = $userSession->getUserEmail();
?>