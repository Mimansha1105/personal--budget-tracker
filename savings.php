<?php
include("session.php");
require('config.php');

// ---------------- SavingGoal Class ----------------
class SavingGoal {
    private $con;
    private $userId;

    public function __construct($con, $userId) {
        $this->con = $con;
        $this->userId = $userId;
    }

    // Add new goal
    public function addSavings($amount, $targetDate) {
        $amount = (float)$amount;
        $targetDate = mysqli_real_escape_string($this->con, $targetDate);

        // ---- Total Balance (Accounts) ----
        $stmt = $this->con->prepare("SELECT SUM(balance) AS bal FROM account_table WHERE user_id = ?");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $totalBalance = isset($row['bal']) ? (float)$row['bal'] : 0;
        $stmt->close();

        // ---- Total Expenses ----
        $stmt = $this->con->prepare("SELECT SUM(expense) AS total FROM transaction_table WHERE user_id = ?");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $row2 = $stmt->get_result()->fetch_assoc();
        $totalExpenses = isset($row2['total']) ? (float)$row2['total'] : 0;
        $stmt->close();

        // ---- REAL Available Balance ----
        $available = $totalBalance - $totalExpenses;
        if ($available < 0) $available = 0;

        if ($amount <= 0) {
            return ["status" => false, "msg" => "⚠ Please enter a valid target amount."];
        }

        // Prevent setting a goal larger than available balance
        if ($amount > $available) {
            return ["status" => false, 
                    "msg" => "❌ You cannot set a savings goal more than your available balance (₹".number_format($available,2).")."];
        }

        // Insert goal
        $stmt = $this->con->prepare("INSERT INTO savings_goals (user_id, amount, targetdate) VALUES (?, ?, ?)");
        $stmt->bind_param("ids", $this->userId, $amount, $targetDate);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) return ["status" => true, "msg" => "✅ Savings goal added successfully!"];
        return ["status" => false, "msg" => "❌ Database error: ".$this->con->error];
    }

    // Fetch goals
    public function getAllGoals() {
        $stmt = $this->con->prepare("SELECT * FROM savings_goals WHERE user_id = ? ORDER BY targetdate ASC");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();
        return $rows;
    }

    // Total account balance
    public function getTotalBalance() {
        $stmt = $this->con->prepare("SELECT SUM(balance) AS bal FROM account_table WHERE user_id = ?");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return isset($row['bal']) ? (float)$row['bal'] : 0;
    }

    // Total expenses
    public function getTotalExpenses() {
        $stmt = $this->con->prepare("SELECT SUM(expense) AS total FROM transaction_table WHERE user_id = ?");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return isset($row['total']) ? (float)$row['total'] : 0;
    }
}

// Create instance
$savingGoal = new SavingGoal($con, $userid);

// Default form values
$amount = "";
$targetdate = date("Y-m-d");
$message = "";
$messageType = "";

// ---------------------- ADD GOAL HANDLER ----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $amount = $_POST['amount'];
    $targetdate = $_POST['targetdate'];

    $result = $savingGoal->addSavings($amount, $targetdate);

    $message = $result['msg'];
    $messageType = $result['status'] ? "success" : "danger";

    if ($result['status']) {
        $_SESSION['savings_msg'] = $message;
        $_SESSION['savings_msg_type'] = $messageType;
        header("Location: savings.php");
        exit();
    }
}

// Flash message
if (isset($_SESSION['savings_msg'])) {
    $message = $_SESSION['savings_msg'];
    $messageType = $_SESSION['savings_msg_type'];
    unset($_SESSION['savings_msg'], $_SESSION['savings_msg_type']);
}

// Fetch goals and financial status
$allGoals = $savingGoal->getAllGoals();
$totalBalance = $savingGoal->getTotalBalance();
$totalExpenses = $savingGoal->getTotalExpenses();

// REAL available balance
$availableBalance = $totalBalance - $totalExpenses;
if ($availableBalance < 0) $availableBalance = 0;

// -------- Correct popup logic --------
$goalReachedNow = false;
$goalFailedNow  = false;

// Only check on GET (not after adding)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    foreach ($allGoals as $g) {
        $goalAmount = (float)$g['amount'];

        if ($availableBalance >= $goalAmount) {
            $goalReachedNow = true;
        } else {
            $goalFailedNow = true;
        }
    }
}
?>


<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Savings Goals</title>
<link href="css/bootstrap.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
/* === page layout & sidebar (matches other pages) === */
body {
  margin:0;
  background:#E8DCC8;
  font-family:"Segoe UI",sans-serif;
}

/* fixed left sidebar */
.sidebar {
  position: fixed;
  width: 260px;
  height: 100vh;
  background: #C9A77A;
  border-right: 2px solid #8a6b48;
  padding-top: 20px;
}

.sidebar .user {
  text-align:center;
  padding:10px;
  color:#333;
}

.sidebar img { width:90px; border-radius:50%; border:3px solid #9a7b55; margin-bottom:10px; }
.sidebar .user h5{ font-weight:bold; margin:0; }
.sidebar .user p{ font-size:14px; color:#5c4a35; margin:4px 0 0 0; }

.sidebar-title{ margin:15px 20px 5px 20px; text-transform:uppercase; font-size:13px; color:#5a4630; font-weight:bold; }

.sidebar a { display:block; padding:10px 22px; color:#2b2b2b; font-size:16px; text-decoration:none; }
.sidebar a:hover, .sidebar a.active { background:#8a6b48; color:#fff; }

/* main content */
.main{
  margin-left:260px;
  padding:30px;
}

.main h2{
  text-align:center;
  font-size:32px;
  border-bottom:2px solid #000;
  padding-bottom:8px;
  margin-bottom:30px;
}

/* inner white box exactly like screenshot */
.card-box {
  max-width: 900px;
  margin: 0 auto 30px auto;
  background: #fff;
  border: 2px solid #000;
  border-radius: 10px;
  padding: 25px 30px;
  box-shadow: 3px 3px 10px rgba(0,0,0,0.25);
}

.card-box h4 {
  font-weight:700;
  font-size:22px;
  text-align:center;
  margin-bottom:20px;
}

.form-row {
  display:flex;
  gap: 20px;
  align-items:flex-end;
  justify-content:center;
  flex-wrap:wrap;
}

/* labels align left inside each column */
.form-col { flex:0 0 45%; min-width:220px; }

.form-col label { font-weight:600; }

/* Add button styling centered */
.btn-add {
  background: #3a9a43;
  border-color: #3a9a43;
  color: #fff;
  padding: 10px 24px;
  border-radius:6px;
}

/* table styles */
.table thead th {
  background:#d3b286;
  color:#fff;
  font-weight:600;
  border:1px solid #8a6b48;
}

.table, .table th, .table td {
  border:1px solid #8a6b48 !important;
}

.table td { vertical-align: middle; }

/* status badge */
.status-reached {
  display:inline-flex;
  align-items:center;
  gap:8px;
  color:#2a6b2a;
  font-weight:600;
}

/* responsive tweaks */
@media (max-width:768px){
  .form-col { flex: 1 1 100%; }
  .card-box { padding:18px; margin: 0 12px 30px; }
  .main { padding:18px; }
}
/* ------------------------------------------------------------
   UPGRADED INPUT STYLING FOR SAVINGS PAGE (Matching Accounts UI)
------------------------------------------------------------- */

/* Bigger, cleaner form fields */
.card-box input.form-control {
    height: 52px;
    font-size: 16px;
    padding: 12px 16px;
    border-radius: 10px;
    border: 1.8px solid #b89a74;
    background: #fff;
    transition: 0.25s ease;
}

/* Focus effect */
.card-box input.form-control:focus {
    border-color: #8a6b48;
    box-shadow: 0 0 6px rgba(138,107,72,0.35);
}

/* Label styling */
.card-box label {
    font-size: 17px;
    font-weight: 600;
    color: #3b2b1e;
    margin-bottom: 6px;
}

/* Add Goal button */
.btn-add {
    background: #6daa67 !important;
    border-color: #6daa67 !important;
    font-size: 17px;
    padding: 12px 24px;
    border-radius: 8px;
    width: 180px;
}

.btn-add:hover {
    background: #589a55 !important;
}

/* Table improvements */
.table td, .table th {
    padding: 14px !important;
    font-size: 15px;
}

.table thead th {
    background: #c9a77a !important;
    color: #fff !important;
    font-size: 16px;
}

/* Status badge style improvements */
.status-reached {
    background: #d6f3d6;
    padding: 6px 12px;
    border-radius: 8px;
    color: #267326;
}

.status-reached i {
    font-size: 18px;
}

/* No goals row */
.table td.text-muted {
    font-size: 16px;
    padding: 18px;
}

/* Responsive tweaks */
@media (max-width: 768px) {
    .card-box input.form-control {
        width: 100%;
    }
}

</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <div class="user">
    <img src="uploads/default_profile.png" alt="profile">
    <h5><?php echo htmlspecialchars($username); ?></h5>
    <p><?php echo htmlspecialchars($useremail); ?></p>
  </div>

  <div class="sidebar-title">MANAGEMENT</div>
  <a href="index.php">Dashboard</a>
  <a href="account.php">Accounts</a>
  <a href="savings.php" class="active">Savings Goal</a>
  <a href="transaction.php">Add/Manage Expenses</a>
  <a href="report.php">Expense Report</a>
  <a href="budget.php">Budget</a>

  <div class="sidebar-title">SETTINGS</div>
  <a href="profile.php">Profile</a>
  <a href="logout.php">Logout</a>
</div>

<!-- MAIN -->
<div class="main">
  <h2>Savings Goals</h2>

  <?php if (!empty($message)): ?>
    <div style="max-width:900px;margin:0 auto 18px;">
      <div class="alert alert-<?php echo ($messageType==='success')?'success':'danger'; ?> text-center" role="alert">
        <?php echo $message; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="card-box">
    <h4><i class="fa-solid fa-piggy-bank text-success" style="margin-right:10px;"></i> Add a New Saving Goal</h4>

    <form method="post" class="mb-3">
      <div class="form-row">
        <div class="form-col">
          <label for="amount">Target Amount (₹)</label>
          <input id="amount" name="amount" type="number" step="0.01" class="form-control" placeholder="Enter amount" value="<?php echo htmlspecialchars($amount); ?>" required>
        </div>

        <div class="form-col">
          <label for="targetdate">Target Date</label>
          <input id="targetdate" name="targetdate" type="date" class="form-control" value="<?php echo htmlspecialchars($targetdate); ?>" required>
        </div>
      </div>

      <div style="text-align:center; margin-top:18px;">
        <button type="submit" name="add" class="btn btn-add">
          <i class="fa-solid fa-circle-plus"></i> Add Goal
        </button>
      </div>
    </form>

    <hr style="margin:28px 0; border-color:#eee;">

    <h4 style="text-align:center; margin-bottom:18px;"><i class="fa-solid fa-list-check text-success" style="margin-right:10px;"></i> Your Savings Goals</h4>

    <div class="table-responsive">
      <table class="table table-bordered text-center">
        <thead>
          <tr>
            <th>Goal ID</th>
            <th>Target Amount</th>
            <th>Target Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($allGoals)): ?>
            <tr><td colspan="4" class="text-muted">No goals yet. Add one above!</td></tr>
          <?php else: ?>
            <?php foreach ($allGoals as $g): 
                $reached = ($totalBalance >= (float)$g['amount']);
            ?>
              <tr>
                <td><?php echo htmlspecialchars($g['goal_id'] ?? $g['goalid'] ?? ''); ?></td>
                <td>₹<?php echo number_format((float)$g['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($g['targetdate']); ?></td>
                <td>
                 <?php
$goalAmount = (float)$g['amount'];

// status conditions
if ($availableBalance >= $goalAmount) {
    // goal met
    echo '<span class="status-reached">
            <i class="fa-solid fa-square-check" style="color:#2a6b2a;"></i> Reached
          </span>';
} else {
    // goal failed
    echo '<span style="background:#f8d7da; padding:6px 12px; border-radius:8px; color:#842029; font-weight:600;">
            <i class="fa-solid fa-circle-xmark"></i> Failed
          </span>';
}
?>

                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<?php if ($goalReachedNow): ?>
<script>alert("🎉 Congratulations! A savings goal has been reached!");</script>
<?php endif; ?>

<?php if ($goalFailedNow): ?>
<script>alert("❌ Sorry! Your savings goal cannot be met with the current available balance.");</script>
<?php endif; ?>


<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
