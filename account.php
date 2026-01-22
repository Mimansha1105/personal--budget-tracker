<?php
include("session.php");

class Database {
    private $host = "127.0.0.1:3306";
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

class Account {
    private $conn;
    public $accId;
    public $accountName;
    public $balance;

    public function __construct($conn, $accId = null, $accountName = "", $balance = 0) {
        $this->conn = $conn;
        $this->accId = $accId;
        $this->accountName = $accountName;
        $this->balance = $balance;
    }

    public function createAccount($userId) {
        $stmt = $this->conn->prepare("INSERT INTO account_table (user_id, account_name, balance) VALUES (?, ?, ?)");
        $stmt->bind_param("isd", $userId, $this->accountName, $this->balance);
        return $stmt->execute();
    }

    public function updateBalance($addedBalance) {
        $this->balance += $addedBalance;
        $stmt = $this->conn->prepare("UPDATE account_table SET balance = ? WHERE account_id = ?");
        $stmt->bind_param("di", $this->balance, $this->accId);
        return $stmt->execute();
    }

    public static function getAccountsByUser($conn, $userId) {
        $stmt = $conn->prepare("SELECT * FROM account_table WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result();
    }
}

$db = new Database();
$conn = $db->connect();

$message = "";
$messageType = "";

// =======================
// Handle Add Account
// =======================
if (isset($_POST['add'])) {
    $acc_name = trim($_POST['acc_name']);
    $balance = (float)$_POST['balance'];

    if ($acc_name !== "" && $balance >= 0) {
        $account = new Account($conn, null, $acc_name, $balance);
        if ($account->createAccount($userid)) {
            $message = "✔ Account <strong>$acc_name</strong> added successfully!";
            $messageType = "success";
        } else {
            $message = "❌ Error creating account.";
            $messageType = "danger";
        }
    } else {
        $message = "⚠ Please enter valid details.";
        $messageType = "warning";
    }
}

// =======================
// Handle Update Balance
// =======================
if (isset($_POST['update'])) {
    $accId = $_POST['acc_id'];
    $addedBalance = (float)$_POST['balance'];

    $stmt = $conn->prepare("SELECT * FROM account_table WHERE account_id=? AND user_id=?");
    $stmt->bind_param("ii", $accId, $userid);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $account = new Account($conn, $row['account_id'], $row['account_name'], $row['balance']);
        if ($account->updateBalance($addedBalance)) {
            $message = "✔ Balance updated for <strong>{$row['account_name']}</strong>.";
            $messageType = "success";
        } else {
            $message = "❌ Error updating balance.";
            $messageType = "danger";
        }
    }
}

$accounts = Account::getAccountsByUser($conn, $userid);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Accounts</title>
<link href="css/bootstrap.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
/* -------------------------------------
   SIDEBAR (same as dashboard)
--------------------------------------*/
body {
    background: #E8DCC8;
    font-family: "Segoe UI", sans-serif;
}

.sidebar {
    position: fixed;
    width: 260px;
    height: 100vh;
    background: #C9A77A;
    border-right: 2px solid #8a6b48;
    padding-top: 20px;
}

.sidebar .user {
    text-align: center;
    padding: 10px;
}

.sidebar img {
    width: 90px;
    border-radius: 50%;
    border: 3px solid #9a7b55;
}

.sidebar .user h5 { font-weight: bold; }

.sidebar-title {
    margin: 15px 20px 5px 20px;
    text-transform: uppercase;
    font-size: 13px;
    color: #5a4630;
    font-weight: bold;
}

.sidebar a {
    display: block;
    padding: 10px 22px;
    font-size: 16px;
    color: #2b2b2b;
    text-decoration: none;
}

.sidebar a:hover,
.sidebar a.active {
    background: #8a6b48;
    color: #fff;
}

/* -------------------------------------
   MAIN CONTENT
--------------------------------------*/
.main {
    margin-left: 260px;
    padding: 30px;
}

.main h2 {
    text-align: center;
    font-size: 32px;
    border-bottom: 2px solid black;
    padding-bottom: 10px;
    margin-bottom: 40px;
}

/* White box EXACT like screenshot */
.box {
    background: white;
    border: 2px solid black;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 40px;
    box-shadow: 3px 3px 10px rgba(0,0,0,0.25);
    transition: 0.25s ease;
}

.box:hover {
    transform: translateY(-6px);
}

/* Heading inside box */
.box-title {
    font-size: 23px;
    font-weight: 700;
    text-align: center;
    margin-bottom: 25px;
}

/* Table styling */
.table th {
    background: #C9A77A !important;
    border: 2px solid black !important;
    font-size: 16px;
}

.table td {
    border: 1px solid black !important;
    vertical-align: middle;
}

/* Buttons */
.btn-update {
    background: #0d6efd;
    color: white;
}

.btn-update:hover {
    background: #0a58ca;
}
/* -------------------------------------
   IMPROVED INPUT BOXES — Clean & Bigger
--------------------------------------*/
input.form-control {
    height: 50px !important;
    font-size: 16px;
    padding: 12px 15px;
    border-radius: 10px;
    border: 1.5px solid #b89a74;
}

/* On focus */
input.form-control:focus {
    border-color: #8a6b48;
    box-shadow: 0 0 6px rgba(138,107,72,0.45);
}

/* Labels spacing */
form label {
    font-size: 17px;
    margin-bottom: 6px;
}

/* Button spacing */
.btn-lg {
    margin-top: 10px;
}

/* -------------------------------------
   UPDATE BALANCE TABLE INPUT FIX
--------------------------------------*/
.table input.form-control {
    height: 45px !important;
    width: 80% !important;
    margin-right: 8px;
    font-size: 15px;
    border-radius: 8px;
}
/* --------------------------------------------------
   UNIVERSAL INPUT UPGRADE (for entire Accounts page)
---------------------------------------------------*/

/* Bigger, modern, clean input fields */
input.form-control {
    height: 52px !important;
    font-size: 16px;
    padding: 12px 16px;
    border-radius: 10px;
    border: 1.6px solid #b89a74;
    background: #fff;
    transition: 0.25s ease;
}

/* Input focus effect */
input.form-control:focus {
    border-color: #8a6b48;
    box-shadow: 0 0 6px rgba(138,107,72,0.4);
}

/* Spacing for labels */
form label {
    font-size: 17px;
    font-weight: 600;
    margin-bottom: 6px;
    color: #3b2b1e;
}

/* Larger button for Add Account */
.box form .btn {
    height: 50px;
    font-size: 17px;
    border-radius: 10px;
    margin-top: 8px;
}

/* --------------------------------------------------
   UPDATE BALANCE — Table Inputs
---------------------------------------------------*/
.table td form input.form-control {
    height: 46px !important;
    width: 85% !important;
    font-size: 15px;
    padding: 10px 14px;
    border-radius: 8px;
    margin-right: 10px;
}

/* Update button inside table */
.table td form .btn-update {
    height: 46px;
    padding: 0 20px;
    font-size: 15px;
    border-radius: 8px;
}

/* Align icons and titles better */
.box-title i {
    margin-right: 8px;
    font-size: 22px;
}

</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="user">
        <img src="uploads/default_profile.png">
        <h5><?php echo $username; ?></h5>
        <p><?php echo $useremail; ?></p>
    </div>

    <div class="sidebar-title">MANAGEMENT</div>
    <a href="index.php">Dashboard</a>
    <a href="account.php" class="active">Accounts</a>
    <a href="savings.php">Savings Goal</a>
    <a href="transaction.php">Add/Manage Expenses</a>
    <a href="report.php">Expense Report</a>
    <a href="budget.php">Budget</a>

    <div class="sidebar-title">SETTINGS</div>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
</div>

<!-- MAIN CONTENT -->
<div class="main">

    <h2>Manage Accounts</h2>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- ADD ACCOUNT BOX -->
    <div class="box">
        <h3 class="box-title"><i class="fa fa-wallet text-success"></i> Add new Account</h3>

        <form method="POST">
            <label><b>Account Name</b></label>
            <input type="text" name="acc_name" class="form-control mb-3" placeholder="e.g. Cash, Bank, Wallet" required>

            <label><b>Opening Balance</b></label>
            <input type="number" name="balance" class="form-control mb-3" placeholder="Enter amount" required>

            <button name="add" class="btn btn-success btn-lg btn-block">
                <i class="fa fa-plus-circle"></i> Add Account
            </button>
        </form>
    </div>

    <!-- UPDATE BALANCE BOX -->
    <div class="box">
        <h3 class="box-title"><i class="fa fa-pen-to-square text-primary"></i> Update Account Balance</h3>

        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Account Name</th>
                    <th>Current Balance</th>
                    <th>Add Amount</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($accounts->num_rows > 0): ?>
                    <?php while ($acc = $accounts->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $acc['account_name']; ?></td>
                            <td>₹<?php echo number_format($acc['balance'], 2); ?></td>
                            <td>
                                <form method="POST" class="d-flex justify-content-center">
                                    <input type="hidden" name="acc_id" value="<?php echo $acc['account_id']; ?>">
                                    <input type="number" name="balance" class="form-control w-50 mr-2" placeholder="Add ₹" required>
                                    <button type="submit" name="update" class="btn btn-update">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-muted">No accounts found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
