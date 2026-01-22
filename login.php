<?php
require_once('config.php');
require_once('user.php');

if (!isset($con)) {
    die("Database connection (\$con) is not available.");
}

$user = new User($con);
$errorMsg = "";

// Redirect if logged in
if ($user->isLoggedIn()) {
    header("Location: index.php");
    exit(0);
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_button'])) {
    $email = trim(strtolower($_POST['user_email'] ?? ''));
    $password = $_POST['user_pass'] ?? '';

    if (empty($email) || empty($password)) {
        $errorMsg = "Email and password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Please enter a valid email address.";
    } else {
        if ($user->login($email, $password)) {
            header("Location: index.php");
            exit(0);
        } else {
            $errorMsg = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login | Personal Budget Tracker</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* -----------------------------------------
   SAME BACKGROUND & COLOR SCHEME
------------------------------------------*/
body {
    margin: 0;
    padding: 0;
    background: url('img1.jpg') no-repeat center center fixed;
    background-size: cover;
    font-family: 'Poppins', sans-serif;

    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* -----------------------------------------
   CLEAN BASIC LOGIN BOX
------------------------------------------*/
.login-form {
    width: 360px;
    background: #ffffffee; /* clean white with slight transparency */
    padding: 28px 25px;
    border-radius: 12px;
    border: 1px solid #e5d5c5;
    box-shadow: 0 6px 15px rgba(0,0,0,0.15); /* soft shadow for pop */
}

/* -----------------------------------------
   HEADINGS
------------------------------------------*/
.login-form h2 {
    text-align: center;
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 6px;
    color: #4b3424; /* same brown theme */
}

.hint-text {
    text-align: center;
    font-size: 14px;
    color: #6c5438;
    margin-bottom: 20px;
}

/* -----------------------------------------
   INPUT FIELDS
------------------------------------------*/
.form-control {
    width: 100%;
    height: 45px;
    border-radius: 8px;
    border: 1px solid #c8b49e;
    padding: 10px;
    font-size: 15px;
    background: #fff;
    transition: 0.25s ease;
    margin-bottom: 16px;
}

.form-control:focus {
    outline: none;
    border-color: #a67847;
    box-shadow: 0 0 5px rgba(166,120,71,0.35);
}

/* Password icon */
.position-relative i {
    position: absolute;
    right: 12px;
    top: 14px;
    color: #a67847;
    cursor: pointer;
}

/* -----------------------------------------
   LOGIN BUTTON — simple + gold theme
------------------------------------------*/
.btn {
    width: 100%;
    height: 45px;
    border-radius: 8px;
    border: none;
    background-color: #c89b67;  /* warm gold */
    color: white;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 5px;
    transition: 0.3s ease;
}

.btn:hover {
    background-color: #b48755;
}

/* -----------------------------------------
   ERROR MESSAGE
------------------------------------------*/
.error-msg {
    color: #b12626;
    text-align: center;
    font-weight: 600;
    margin-bottom: 12px;
}

/* -----------------------------------------
   LINK
------------------------------------------*/
.text-center a {
    color: #a67847;
    font-weight: 600;
    text-decoration: none;
}

.text-center a:hover {
    text-decoration: underline;
}

</style>
</head>

<body>

<div class="login-card">
    <h2><i class="fa-solid fa-wallet"></i>Personal Budget Tracker</h2>
    <p>Login to continue</p>

    <?php if (!empty($errorMsg)): ?>
        <p class="error-msg"><?= htmlspecialchars($errorMsg) ?></p>
    <?php endif; ?>

    <form method="POST" autocomplete="off">

        <input type="email" name="user_email" class="form-control" placeholder="Email Address" required>

        <div class="password-wrapper">
            <input type="password" name="user_pass" id="password" class="form-control" placeholder="Password" required>
            <i class="fa-solid fa-eye" onclick="togglePassword()" id="toggleIcon"></i>
        </div>

        <button type="submit" name="login_button" value="1" class="btn">Login</button>

        <div class="register-link">
            <p>Don't have an account?  
            <a href="register.php">Register here</a></p>
        </div>
    </form>
</div>

<script>
function togglePassword() {
    let passField = document.getElementById('password');
    let icon = document.getElementById('toggleIcon');

    if (passField.type === "password") {
        passField.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        passField.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
    }
}
</script>

</body>
</html>
