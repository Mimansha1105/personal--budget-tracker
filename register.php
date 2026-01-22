<?php
require('config.php');
require('User.php');

$user = new User($con);

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['firstname'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        $errorMsg = "Password and confirmation password do not match.";
    } else {
        if ($user->register($firstname, $lastname, $email, $password)) {
            $successMsg = "✅ Registration successful! Redirecting to login...";
            header("refresh:2; url=login.php");
            exit;
        } else {
            $errorMsg = "❌ Registration failed. Email may already exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Register | Personal Budget Tracker</title>
<link href="css/bootstrap.css" rel="stylesheet">
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

<div class="signup-form">
  <?php if($successMsg) echo "<p class='text-success'>$successMsg</p>"; ?>
  <?php if($errorMsg) echo "<p class='text-danger'>$errorMsg</p>"; ?>

  <form method="POST" autocomplete="off">
    <h2><i class="fa-solid fa-user-plus me-2 text-warning"></i> Create Account</h2>
    <div class="form-group row mb-3">
      <div class="col"><input type="text" class="form-control" name="firstname" placeholder="First Name" required></div>
      <div class="col"><input type="text" class="form-control" name="lastname" placeholder="Last Name" required></div>
    </div>
    <div class="form-group row mb-3">
      <div class="col">
        <input type="email" class="form-control" name="email" placeholder="Email" required>
      </div>
    </div>
    <div class="form-group row mb-3">
      <div class="col position-relative">
        <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
        <span class="position-absolute" style="top:12px; right:15px; cursor:pointer;" onclick="togglePassword('password','eyeIcon1')">
          <i id="eyeIcon1" class="fa-solid fa-eye"></i>
        </span>
      </div>
    </div>
    <div class="form-group row mb-4">
      <div class="col position-relative">
        <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
        <span class="position-absolute" style="top:12px; right:15px; cursor:pointer;" onclick="togglePassword('confirm_password','eyeIcon2')">
          <i id="eyeIcon2" class="fa-solid fa-eye"></i>
        </span>
      </div>
    </div>
    <div class="form-group row mb-3">
      <div class="col">
        <button type="submit" class="btn btn-lg btn-block w-100">Register</button>
      </div>
    </div>
  </form>
  <div class="text-center mt-2">Already have an account? <a href="login.php">Login here</a></div>
</div>

<script>
function togglePassword(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(iconId);
  if(input.type === 'password') {
      input.type = 'text';
      icon.classList.replace('fa-eye','fa-eye-slash');
  } else {
      input.type = 'password';
      icon.classList.replace('fa-eye-slash','fa-eye');
  }
}
</script>

<script src="js/bootstrap.min.js"></script>
</body>
</html>
