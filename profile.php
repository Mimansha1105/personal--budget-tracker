<?php
include("session.php");
require("config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fname = $_POST['firstname'];
    $lname = $_POST['lastname'];

    $update = $con->prepare("UPDATE users SET firstname=?, lastname=? WHERE user_id=?");
    $update->bind_param("ssi", $fname, $lname, $userid);

    if ($update->execute()) {
        echo "<script>alert('Profile updated successfully'); window.location='profile.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Profile</title>
<link rel="stylesheet" href="css/bootstrap.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

/* ============================
GLOBAL
============================ */
body {
    margin: 0;
    background: #E8DCC8;
    font-family: "Segoe UI", sans-serif;
}

/* ============================
SIDEBAR (Same as all other pages)
============================ */
.sidebar {
    width: 260px;
    height: 100vh;
    background: #C9A77A;
    border-right: 2px solid #8a6b48;
    position: fixed;
    padding-top: 20px;
}

.sidebar .user {
    text-align: center;
}

.sidebar img {
    width: 90px;
    border-radius: 50%;
    border: 3px solid #9a7b55;
    margin-bottom: 8px;
}

.sidebar-title {
    margin: 15px 20px 5px;
    color: #5a4630;
    font-weight: bold;
    text-transform: uppercase;
    font-size: 13px;
}

.sidebar a {
    display: block;
    color: #2b2b2b;
    padding: 10px 22px;
    font-size: 16px;
    text-decoration: none;
}

.sidebar a:hover,
.sidebar a.active {
    background: #8a6b48;
    color: white;
}

/* ============================
MAIN CONTENT
============================ */
.main {
    margin-left: 260px;
    padding: 30px;
}

.main h2 {
    font-size: 32px;
    text-align: center;
    border-bottom: 2px solid black;
    padding-bottom: 12px;
    margin-bottom: 35px;
}

/* ============================
INPUT BOXES (Updated only)
============================ */
.form-control {
    height: 48px;
    background: #fff;
    border: 2px solid #8a6b48 !important;
    border-radius: 10px;
    font-size: 16px;
    padding-left: 12px;
    color: #3a2d1a;
}

.form-control:focus {
    border-color: #000 !important;
    box-shadow: 0 0 6px rgba(0,0,0,0.25);
}

/* Label */
label {
    font-weight: 600;
    color: #3a2d1a;
    margin-bottom: 6px;
    font-size: 16px;
}

/* Save Button */
.btn-save {
    background: #2b8f3a;
    color: #fff;
    font-size: 18px;
    padding: 10px 28px;
    border-radius: 10px;
    border: none;
    font-weight: bold;
}

.btn-save:hover {
    background: #1f742e;
}

.box {
    background: white;
    border: 2px solid black;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 3px 3px 10px rgba(0,0,0,0.25);
    max-width: 650px;
    margin: auto;
}

</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="user">
        <img src="uploads/default_profile.png">
        <h5><?php echo $username ?></h5>
        <p><?php echo $useremail ?></p>
    </div>

    <div class="sidebar-title">MANAGEMENT</div>
    <a href="index.php">Dashboard</a>
    <a href="account.php">Accounts</a>
    <a href="savings.php">Savings Goal</a>
    <a href="transaction.php">Add/Manage Expenses</a>
    <a href="report.php">Expense Report</a>
    <a href="budget.php">Budget</a>

    <div class="sidebar-title">SETTINGS</div>
    <a class="active" href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
</div>

<!-- MAIN CONTENT -->
<div class="main">

    <h2>Update Profile</h2>

    <div class="box">
        <h4><i class="fa fa-user-pen"></i> Edit Profile Information</h4>

        <form method="POST">

            <label>First Name</label>
            <input type="text" name="firstname" class="form-control" value="<?php echo $username; ?>">

            <br>

            <label>Last Name</label>
            <input type="text" name="lastname" class="form-control">

            <br>

            <label>Email (cannot change)</label>
            <input type="email" class="form-control" value="<?php echo $useremail; ?>" disabled>

            <br>

            <button class="btn-save">Save Changes</button>

        </form>
    </div>

</div>

</body>
</html>
