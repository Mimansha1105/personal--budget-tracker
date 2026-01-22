<?php
include("session.php");

// ----------------- Budget Class -----------------
class Budget {
    private $budgetId;
    private $amount;
    private $conn;

    public function __construct($conn, $budgetId = null, $amount = 0) {
        $this->conn = $conn;
        $this->budgetId = $budgetId;
        $this->amount = $amount;
    }

    public function setBudget($userId) {
        $stmt = $this->conn->prepare("INSERT INTO budget_table (user_id, amount) VALUES (?, ?)");
        $stmt->bind_param("id", $userId, $this->amount);
        return $stmt->execute();
    }

    public function updateBudget($userId) {
        $stmt = $this->conn->prepare("UPDATE budget_table SET amount=? WHERE user_id=? AND budget_id=?");
        $stmt->bind_param("dii", $this->amount, $userId, $this->budgetId);
        return $stmt->execute();
    }

    public function deleteBudget($userId) {
        $stmt = $this->conn->prepare("DELETE FROM budget_table WHERE user_id=? AND budget_id=?");
        $stmt->bind_param("ii", $userId, $this->budgetId);
        return $stmt->execute();
    }

    public static function getBudgets($conn, $userId) {
        $stmt = $conn->prepare("SELECT * FROM budget_table WHERE user_id=? ORDER BY budget_id DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    public static function getBudget($conn, $userId, $budgetId) {
        $stmt = $conn->prepare("SELECT * FROM budget_table WHERE user_id=? AND budget_id=?");
        $stmt->bind_param("ii", $userId, $budgetId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}

// ----------------- Main Logic -----------------
$update = false;
$amount = "";
$budgetId = null;

// Add Budget (with balance check)
if (isset($_POST['add'])) {
    $budgetAmount = (float)$_POST['bud_amount'];

    $query = mysqli_query($con, "SELECT SUM(balance) AS total_balance FROM account_table WHERE user_id='$userid'");
    $row = mysqli_fetch_assoc($query);
    $totalBalance = $row['total_balance'] ?? 0;

    if ($budgetAmount > $totalBalance) {
        echo "<script>alert('❌ Budget cannot exceed available balance (₹$totalBalance).'); window.location.href='budget.php';</script>";
        exit();
    }

    $budget = new Budget($con, null, $budgetAmount);
    if ($budget->setBudget($userid)) {
        echo "<script>alert('✅ Budget added!'); window.location.href='budget.php';</script>";
        exit();
    } else {
        echo "<script>alert('❌ Failed to add budget.'); window.location.href='budget.php';</script>";
        exit();
    }
}

// Update
if (isset($_POST['update'])) {
    $budgetId = $_GET['edit'];
    $budgetAmount = (float)$_POST['bud_amount'];

    $query = mysqli_query($con, "SELECT SUM(balance) AS total_balance FROM account_table WHERE user_id='$userid'");
    $row = mysqli_fetch_assoc($query);
    $totalBalance = $row['total_balance'] ?? 0;

    if ($budgetAmount > $totalBalance) {
        echo "<script>alert('❌ Budget cannot exceed balance (₹$totalBalance).'); window.location.href='budget.php';</script>";
        exit();
    }

    $budget = new Budget($con, $budgetId, $budgetAmount);
    if ($budget->updateBudget($userid)) {
        echo "<script>alert('✅ Budget updated!'); window.location.href='budget.php';</script>";
        exit();
    } else {
        echo "<script>alert('❌ Failed to update budget.'); window.location.href='budget.php';</script>";
        exit();
    }
}

// Delete
if (isset($_GET['delete'])) {
    $budgetId = $_GET['delete'];
    $budget = new Budget($con, $budgetId);
    if ($budget->deleteBudget($userid)) {
        echo "<script>alert('🗑️ Budget deleted!'); window.location.href='budget.php';</script>";
        exit();
    } else {
        echo "<script>alert('❌ Failed to delete budget.'); window.location.href='budget.php';</script>";
        exit();
    }
}

// Edit
if (isset($_GET['edit'])) {
    $update = true;
    $budgetId = $_GET['edit'];
    $record = Budget::getBudget($con, $userid, $budgetId);
    if ($record) $amount = $record['amount'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Personal Budget Tracker - Budget</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="css/bootstrap.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
/* ====== Page base ====== */
:root{
  --sidebar-w:260px;
  --cream:#E8DCC8;
  --sidebar:#C9A77A;
  --accent:#8a6b48;
  --panel-bg:#fff;
  --panel-border:#000;
  --panel-radius:10px;
}

*{box-sizing:border-box}
body{
  margin:0;
  font-family:"Segoe UI", Roboto, sans-serif;
  background:var(--cream);
  color:#2f2f2f;
}

/* ====== Sidebar ====== */
.sidebar{
  position:fixed;
  left:0;
  top:0;
  width:var(--sidebar-w);
  height:100vh;
  background:var(--sidebar);
  border-right:2px solid var(--accent);
  padding-top:18px;
}
.sidebar .user{ text-align:center; padding:18px 10px;}
.sidebar .user img{ width:110px; height:110px; border-radius:50%; border:3px solid #9a7b55; display:block; margin:0 auto 8px;}
.sidebar .user h5{ margin:6px 0 2px; font-size:18px; font-weight:600;}
.sidebar .user p{ margin:0; font-size:13px; color:#5c4a35;}

.sidebar .list{ margin-top:14px; padding:0;}
.sidebar .list a{ display:block; padding:12px 20px; color:#2b2b2b; text-decoration:none; font-weight:500; border-bottom:1px solid rgba(0,0,0,0.08);}
.sidebar .list a:hover{ background:var(--accent); color:#fff;}
.sidebar .list a.active{ background:var(--accent); color:#fff; font-weight:700;}

/* ====== Main content ====== */
.main{
  margin-left:var(--sidebar-w);
  padding:26px 40px;
}

/* header */
.header{
  background: #F3E7D9;
  padding:22px;
  text-align:center;
  border-bottom:2px solid #000;
  margin-bottom:30px;
}
.header h1{ margin:0; font-size:32px; font-weight:700; color:#2e2115; letter-spacing:0.3px; }

/* ====== Boxes container (side by side) ====== */
.boxes{
  display:flex;
  gap:28px;
  align-items:flex-start;
  /* make boxes centered with left/right margins similar to screenshot */
  justify-content:center;
}

/* Each box style */
.box{
  flex:1 1 0;
  min-width:300px;
  background:var(--panel-bg);
  border:2px solid var(--panel-border);
  border-radius:var(--panel-radius);
  padding:28px;
  box-shadow: 2px 2px 8px rgba(0,0,0,0.18);
}

/* We want left box slightly narrower like screenshot */
.box-left{ max-width:48%; }
.box-right{ max-width:48%; }

/* Title inside box */
.box h4{
  text-align:center;
  margin:0 0 18px;
  font-size:20px;
  font-weight:700;
}

/* Form styles */
.form-row{
  display:flex;
  align-items:center;
  gap:18px;
  margin-bottom:18px;
  flex-wrap:wrap;
}
.form-row label{
  width:220px;
  font-weight:700;
  color:#2a2317;
}
.form-row .control{
  flex:1;
  min-width:160px;
}
.form-control{
  width:100%;
  height:44px;
  padding:8px 12px;
  border-radius:6px;
  border:2px solid #8E6F49;
  font-size:15px;
}
.form-control:focus{ outline:none; border-color:#000; box-shadow:0 0 6px rgba(0,0,0,0.18); }

/* Button */
.btn-green{
  background:#4DA34D;
  color:white;
  border:none;
  padding:10px 26px;
  border-radius:8px;
  font-weight:700;
  font-size:16px;
  cursor:pointer;
}
.btn-green:hover{ background:#3b8a3b; transform:translateY(-2px); }

/* Table styles inside right box */
.table-wrap{ margin-top:6px; }
.table{
  width:100%;
  border-collapse:collapse;
  margin-top:12px;
}
.table thead th{
  background:#D3B286;
  color:#fff;
  padding:12px;
  border:1px solid #8E6F49;
  font-weight:700;
}
.table tbody td{
  padding:12px;
  border:1px solid #8E6F49;
  background:#fffaf5;
  text-align:center;
}

/* small action buttons */
.action-btn{ padding:6px 10px; border-radius:6px; font-weight:600; font-size:14px; text-decoration:none; display:inline-block; }
.btn-edit{ background:#f0b429; color:#000; border:0; }
.btn-delete{ background:#e04b4b; color:#fff; border:0; }

/* spacing bottom between sections */
.section-gap{ margin-top:34px; }

/* ====== Responsive ====== */
@media (max-width: 1000px){
  .boxes{ flex-direction:column; }
  .box-left, .box-right{ max-width:100%; }
  .form-row label{ width:110px; }
  .main{ padding:20px; }
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

  <div class="list">
    <a href="index.php">Dashboard</a>
    <a href="account.php">Accounts</a>
    <a href="savings.php">Savings Goal</a>
    <a href="transaction.php">Add/Manage Expenses</a>
    <a href="report.php">Expense Report</a>
    <a href="budget.php" class="active">Budget</a>
  </div>

  <div class="list" style="margin-top:18px;">
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
  </div>
</div>

<!-- MAIN -->
<div class="main">
  <div class="header">
    <h1>Manage Budget</h1>
  </div>

  <div class="boxes">
    <!-- LEFT: Add/Update Budget -->
    <div class="box box-left">
      <h4><?php echo $update ? '<i class="fa-solid fa-pen-to-square" style="color:#f0b429;margin-right:8px"></i> Update Budget' : '<i class="fa-solid fa-plus-circle" style="color:#4DA34D;margin-right:8px"></i> Add New Budget'; ?></h4>

      <form method="POST">
        <div class="form-row">
          <label for="bud_amount">Enter Budget Amount (₹)</label>
          <div class="control">
            <input id="bud_amount" name="bud_amount" class="form-control" type="number" placeholder="Enter amount" value="<?php echo htmlspecialchars($amount); ?>" required>
          </div>
        </div>

        <div style="display:flex; justify-content:flex-start; gap:12px; margin-top:8px;">
          <?php if ($update): ?>
            <button type="submit" name="update" class="btn-green">Update Budget</button>
            <a href="budget.php" class="action-btn" style="background:#ccc;color:#000;border-radius:8px;padding:8px 12px;text-decoration:none;">Cancel</a>
          <?php else: ?>
            <button type="submit" name="add" class="btn-green">Add Budget</button>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <!-- RIGHT: Budgets Table -->
    <div class="box box-right">
      <h4><i class="fa-solid fa-list" style="color:#4DA34D;margin-right:8px;"></i> Your Budgets</h4>

      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Budget ID</th>
              <th>Amount (₹)</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $budgets = Budget::getBudgets($con, $userid);
            if ($budgets && $budgets->num_rows > 0):
              while ($row = $budgets->fetch_assoc()):
            ?>
            <tr>
              <td><?php echo $row['budget_id']; ?></td>
              <td>₹<?php echo number_format($row['amount'], 2); ?></td>
              <td>
                <a class="action-btn btn-edit" href="budget.php?edit=<?php echo $row['budget_id']; ?>"><i class="fa fa-pen"></i> Edit</a>
                <a class="action-btn btn-delete" href="budget.php?delete=<?php echo $row['budget_id']; ?>" onclick="return confirm('⚠️ Are you sure you want to delete this budget?');"><i class="fa fa-trash"></i> Delete</a>
              </td>
            </tr>
            <?php
              endwhile;
            else:
            ?>
            <tr><td colspan="3" style="padding:20px;">No budgets found. Add one from the left.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="section-gap"></div>
</div>

</body>
</html>
