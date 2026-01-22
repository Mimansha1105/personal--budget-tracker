<?php
include("session.php");
require("config.php");

// ========== TRANSACTION CLASS ==========
class Transaction {
    private $conn;
    private $id;
    private $date;
    private $amount;
    private $category;

    public function __construct($conn, $id = null, $date = null, $amount = 0, $category = "") {
        $this->conn = $conn;
        $this->id = $id;
        $this->date = $date;
        $this->amount = $amount;
        $this->category = $category;
    }

    public function add($userId) {
        $stmt = $this->conn->prepare(
            "INSERT INTO transaction_table (user_id, expense, expensedate, expensecategory)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("idss", $userId, $this->amount, $this->date, $this->category);
        return $stmt->execute();
    }

    public function update($userId) {
        $stmt = $this->conn->prepare(
            "UPDATE transaction_table
             SET expense=?, expensedate=?, expensecategory=?
             WHERE user_id=? AND expense_id=?"
        );
        $stmt->bind_param("dssii", $this->amount, $this->date, $this->category, $userId, $this->id);
        return $stmt->execute();
    }

    public function delete($userId) {
        $stmt = $this->conn->prepare(
            "DELETE FROM transaction_table WHERE user_id=? AND expense_id=?"
        );
        $stmt->bind_param("ii", $userId, $this->id);
        return $stmt->execute();
    }

    public static function all($conn, $userId) {
        $stmt = $conn->prepare("SELECT * FROM transaction_table WHERE user_id=? ORDER BY expensedate DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    public static function totalSpent($conn, $userId) {
        $r = mysqli_query($conn, "SELECT SUM(expense) AS t FROM transaction_table WHERE user_id='$userId'");
        $row = mysqli_fetch_assoc($r);
        return $row['t'] ?? 0;
    }

    public static function totalBudget($conn, $userId) {
        $r = mysqli_query($conn, "SELECT SUM(amount) AS t FROM budget_table WHERE user_id='$userId'");
        $row = mysqli_fetch_assoc($r);
        return $row['t'] ?? 0;
    }
}

// BASIC VALUES
$totalBudget = Transaction::totalBudget($con, $userid);
$totalSpent = Transaction::totalSpent($con, $userid);
$remaining = $totalBudget - $totalSpent;

// ============================= ADD TRANSACTION =============================
if (isset($_POST['add'])) {
    $expenseamount = $_POST['expenseamount'];
    $expensedate = $_POST['expensedate'];
    $expensecategory = $_POST['expensecategory'];

    if ($totalBudget > 0 && ($totalSpent + $expenseamount) > $totalBudget) {
        echo "<script>
            if (confirm('⚠ This expense exceeds your total budget. Continue?')) {
                window.location='transaction.php?confirm_add=1&amount=$expenseamount&date=$expensedate&category=$expensecategory';
            }
        </script>";
        exit();
    }

    $t = new Transaction($con, null, $expensedate, $expenseamount, $expensecategory);
    $t->add($userid);

    echo "<script>alert('Expense added successfully!'); window.location='transaction.php';</script>";
    exit();
}

if (isset($_GET['confirm_add'])) {
    $t = new Transaction($con, null, $_GET['date'], $_GET['amount'], $_GET['category']);
    $t->add($userid);
    echo "<script>alert('Expense added (Budget exceeded)!'); window.location='transaction.php';</script>";
    exit();
}

// ============================= DELETE =============================
if (isset($_GET['delete'])) {
    $t = new Transaction($con, $_GET['delete']);
    $t->delete($userid);
    echo "<script>alert('🗑 Deleted!'); window.location='transaction.php';</script>";
    exit();
}

// ============================= EDIT MODE =============================
$editMode = false;
$editData = null;

if (isset($_GET['edit'])) {
    $editMode = true;
    $id = $_GET['edit'];

    $res = mysqli_query($con, "SELECT * FROM transaction_table WHERE expense_id='$id'");
    $editData = mysqli_fetch_assoc($res);
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Manage Transactions</title>
<link rel="stylesheet" href="css/bootstrap.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body {
    background: #E8DCC8;
    font-family: "Segoe UI", sans-serif;
}

/* SIDEBAR */
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
    margin: 15px 20px 5px;
    font-weight: bold;
    font-size: 13px;
    text-transform: uppercase;
    color: #5a4630;
}

.sidebar a {
    padding: 10px 22px;
    font-size: 16px;
    display: block;
    color: #2b2b2b;
    text-decoration: none;
}

.sidebar a:hover,
.sidebar a.active {
    background: #8a6b48;
    color: white;
}

/* MAIN */
.main {
    margin-left: 260px;
    padding: 30px;
    padding-right: 60px;
}

.main h2 {
    font-size: 32px;
    text-align: center;
    border-bottom: 2px solid black;
    margin-bottom: 25px;
    padding-bottom: 10px;
}

/* SUMMARY BAR */
.summary-container {
    display: flex;
    justify-content: space-between;
    gap: 25px;
    background: #fff;
    border: 2px solid #000;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 35px;
    box-shadow: 3px 3px 10px rgba(0,0,0,0.25);
}

.sum-card {
    flex: 1;
    background: #fff;
    border-radius: 15px;
    border: 2px solid #000;
    padding: 20px;
    text-align: center;
}

.sum-card i { font-size: 32px; }

/* BOXES */
.box {
    width: 95%;
    margin: auto;
    background: #fff;
    border: 2px solid black;
    border-radius: 15px;
    padding: 35px;
    margin-bottom: 40px;
    box-shadow: 3px 3px 10px rgba(0,0,0,0.25);
}

.box .form-control,
.box select {
    height: 48px;
    border-radius: 10px;
    border: 2px solid #8a6b48;
}

/* ADD BUTTON */
.btn-add {
    background: #5A3F2E;
    color: white;
    padding: 10px 28px;
    border-radius: 10px;
    border: none;
    font-size: 17px;
    font-weight: 600;
    cursor: pointer;
}
.btn-add:hover {
    background: #3c2a1d;
    transform: translateY(-3px);
}

/* TABLE */
.table th {
    background: #d3b286 !important;
    color: white;
    border: 1px solid black;
}
.table td {
    border: 1px solid black !important;
}

/* FIX ACTION COLUMN SPACING */
.action-buttons {
    display: flex;
    justify-content: center;
    gap: 10px;
}

/* EDIT BUTTON */
.btn-edit {
    background: #d4a54e;
    padding: 6px 16px;
    color: black;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
}
.btn-edit:hover {
    background: #b98933;
    color: white;
}

/* DELETE BUTTON */
.btn-delete {
    background: #c74d4d;
    padding: 6px 16px;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
}
.btn-delete:hover {
    background: #9f1f1f;
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
    <a class="active" href="transaction.php">Add/Manage Expenses</a>
    <a href="report.php">Expense Report</a>
    <a href="budget.php">Budget</a>

    <div class="sidebar-title">SETTINGS</div>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
</div>

<!-- MAIN -->
<div class="main">

    <h2>Manage Transactions</h2>

    <!-- SUMMARY -->
    <div class="summary-container">
        <div class="sum-card">
            <i class="fa fa-wallet"></i>
            <h5>Total Budget</h5>
            <p>₹<?php echo number_format($totalBudget,2); ?></p>
        </div>

        <div class="sum-card">
            <i class="fa fa-money-bill-wave"></i>
            <h5>Total Spent</h5>
            <p>₹<?php echo number_format($totalSpent,2); ?></p>
        </div>

        <div class="sum-card">
            <i class="fa fa-piggy-bank"></i>
            <h5>Remaining</h5>
            <p>₹<?php echo number_format($remaining,2); ?></p>
        </div>
    </div>

    <!-- ADD EXPENSE -->
    <div class="box">
        <h4 class="text-center mb-4"><i class="fa fa-plus-circle text-success"></i> Add a New Expense</h4>

        <form method="POST">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label><b>Amount (₹)</b></label>
                    <input type="number" name="expenseamount" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label><b>Date</b></label>
                    <input type="date" name="expensedate" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="col-md-4">
                    <label><b>Category</b></label>
                    <select name="expensecategory" class="form-control" required>
                        <?php
                        $c = mysqli_query($con, "SELECT * FROM category_table");
                        while ($row = mysqli_fetch_assoc($c)) {
                            echo "<option>{$row['category_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="text-center">
                <button class="btn-add" name="add">Add Transaction</button>
            </div>
        </form>
    </div>

    <!-- TRANSACTIONS TABLE -->
    <div class="box">
        <h4 class="text-center mb-3"><i class="fa fa-list text-success"></i> Your Transactions</h4>

        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount (₹)</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php
                $rows = Transaction::all($con, $userid);
                if ($rows->num_rows == 0) {
                    echo "<tr><td colspan='4' class='text-muted'>No transactions found.</td></tr>";
                }

                while ($r = $rows->fetch_assoc()) {
                    echo "
                    <tr>
                        <td>{$r['expensedate']}</td>
                        <td>₹".number_format($r['expense'],2)."</td>
                        <td>{$r['expensecategory']}</td>
                        <td>
                            <div class='action-buttons'>
                                <a href='transaction.php?edit={$r['expense_id']}' class='btn-edit'>Edit</a>
                                <a href='transaction.php?delete={$r['expense_id']}' class='btn-delete'>Delete</a>
                            </div>
                        </td>
                    </tr>";
                }
                ?>
                </tbody>

            </table>
        </div>
    </div>

</div>

</body>
</html>
