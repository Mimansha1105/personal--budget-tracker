<?php
include("session.php");
require_once 'config.php';

// =====================
// CLASS DEFINITIONS
// =====================

class TransactionManager {
    private $conn, $userId;
    public function __construct($conn, $userId) { $this->conn = $conn; $this->userId = $userId; }
    private function getSum($qAdd='') {
        $q = "SELECT SUM(expense) AS total FROM transaction_table WHERE user_id='$this->userId' $qAdd";
        $r = mysqli_query($this->conn, $q);
        $row = mysqli_fetch_assoc($r);
        return $row['total'] ?? 0;
    }
    public function getTodayExpense(){ return $this->getSum("AND expensedate=CURDATE()"); }
    public function getWeeklyExpense(){ return $this->getSum("AND expensedate>=DATE_SUB(CURDATE(), INTERVAL 7 DAY)"); }
    public function getMonthlyExpense(){ return $this->getSum("AND expensedate>=DATE_SUB(CURDATE(), INTERVAL 30 DAY)"); }
    public function getYearlyExpense(){ return $this->getSum("AND expensedate>=DATE_SUB(CURDATE(), INTERVAL 1 YEAR)"); }
    public function getTotalExpense(){ return $this->getSum(); }
}

class AccountManager {
    private $conn, $userId;
    public function __construct($conn, $userId) { $this->conn = $conn; $this->userId = $userId; }
    public function getAvailableBalance(){
        $r = mysqli_query($this->conn, "SELECT balance FROM account_table WHERE user_id='$this->userId'");
        $row = mysqli_fetch_assoc($r);
        return $row['balance'] ?? 0;
    }
}

class BudgetManager {
    private $conn, $userId;
    public function __construct($conn, $userId) { $this->conn = $conn; $this->userId = $userId; }
    public function getTotalBudget(){
        $r = mysqli_query($this->conn,"SELECT SUM(amount) AS total FROM budget_table WHERE user_id='$this->userId'");
        $row = mysqli_fetch_assoc($r);
        return $row['total'] ?? 0;
    }
}

class SavingGoalManager {
    private $conn, $userId;
    public function __construct($conn, $userId){ $this->conn=$conn; $this->userId=$userId; }
    public function getTotalSavings(){
        $r=mysqli_query($this->conn,"SELECT SUM(amount) AS total FROM savings_goals WHERE user_id='$this->userId'");
        $row=mysqli_fetch_assoc($r);
        return $row['total'] ?? 0;
    }
}

// FETCHING DATA
$tm = new TransactionManager($con,$userid);
$am = new AccountManager($con,$userid);
$bm = new BudgetManager($con,$userid);
$sm = new SavingGoalManager($con,$userid);

$today  = $tm->getTodayExpense();
$week   = $tm->getWeeklyExpense();
$month  = $tm->getMonthlyExpense();
$year   = $tm->getYearlyExpense();
$total  = $tm->getTotalExpense();
$bal    = $am->getAvailableBalance();
$budget = $bm->getTotalBudget();
$save   = $sm->getTotalSavings();

$avail  = $bal - $total;

?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>
<link href="css/bootstrap.css" rel="stylesheet">

<style>
body {
    margin: 0;
    background: #E8DCC8;
    font-family: "Segoe UI", sans-serif;
}

/* Sidebar */
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
    margin-bottom: 10px;
}

.sidebar .user h5 { 
    font-weight: bold; 
    color: #333; 
}

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
    color: #2b2b2b;
    font-size: 16px;
    text-decoration: none;
}

.sidebar a.active,
.sidebar a:hover {
    background: #8a6b48;
    color: #fff;
}

/* Dashboard Container */
.main {
    margin-left: 260px;
    padding: 30px;
}

/* Heading like screenshot */
.main h2 {
    font-size: 32px;
    border-bottom: 2px solid #000;
    width: 100%;
    padding-bottom: 8px;
    margin-bottom: 30px;
}

/* ROW alignment + spacing */
.row {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 25px; /* spacing between boxes */
}

/* Cards EXACT like screenshot + hover animation */
.card-box {
    background: white;
    border: 2px solid #333;
    box-shadow: 2px 2px 5px rgba(0,0,0,0.25);
    border-radius: 6px;
    padding: 20px;
    text-align: center;

    width: 230px;
    height: 150px;
    display: flex;
    flex-direction: column;
    justify-content: center;

    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

/* Hover uplift effect */
.card-box:hover {
    transform: translateY(-8px);
    box-shadow: 4px 4px 12px rgba(0, 0, 0, 0.35);
}

.card-box h5 {
    font-size: 18px;
    margin-bottom: 10px;
    font-weight: bold;
}

.card-box p {
    font-size: 22px;
    font-weight: bold;
    margin: 0;
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

    <div class="sidebar-title">Management</div>
    <a href="index.php" class="active">Dashboard</a>
    <a href="account.php">Accounts</a>
    <a href="savings.php">Savings Goal</a>
    <a href="transaction.php">Add/Manage Expenses</a>
    <a href="report.php">Expense Report</a>
    <a href="budget.php">Budget</a>

    <div class="sidebar-title">Settings</div>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
</div>

<!-- MAIN CONTENT -->
<div class="main">

    <h2>Dashboard</h2>

    <h4><strong>Full-Expense Report</strong></h4><br>

    <div class="row">

        <?php
        $cards = [
            ["Saving goal", $save],
            ["Today's Expense", $today],
            ["Last 7Day's Expense", $week],
            ["Last 30Day's Expense", $month],
            ["Current Year Expense", $year],
            ["Total Expense", $total],
            ["Available Balance", $avail],
            ["Budget", $budget]
        ];

        foreach ($cards as $c) {
            echo '
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4 d-flex justify-content-center">
                <div class="card-box">
                    <h5>'.$c[0].'</h5>
                    <p>₹'.$c[1].'</p>
                </div>
            </div>';
        }
        ?>

    </div>

</div>

</body>
</html>
