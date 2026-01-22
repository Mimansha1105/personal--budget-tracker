<?php
include("session.php");
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reportType = $_POST['report_type'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    $query = "";
    $tableHeader = '';
    $periodDates = array();

    switch ($reportType) {

        case 'datewise':
            $query = "SELECT DATE(expensedate) AS period, SUM(expense) AS totalExpense 
            FROM transaction_table 
            WHERE user_id='$userid' AND expensedate BETWEEN '$startDate' AND '$endDate'
            GROUP BY period";
            $tableHeader = "Date";
            $periodDates = generateDateRange($startDate, $endDate);
            break;

        case 'monthwise':
            $query = "SELECT YEAR(expensedate) AS year, MONTH(expensedate) AS month, SUM(expense) AS totalExpense 
            FROM transaction_table 
            WHERE user_id='$userid' AND expensedate BETWEEN '$startDate' AND '$endDate'
            GROUP BY year, month";
            $tableHeader = "Year-Month";
            $periodDates = generateMonthYearRange($startDate, $endDate);
            break;

        case 'yearwise':
            $query = "SELECT YEAR(expensedate) AS year, SUM(expense) AS totalExpense 
            FROM transaction_table 
            WHERE user_id='$userid' AND expensedate BETWEEN '$startDate' AND '$endDate'
            GROUP BY year";
            $tableHeader = "Year";
            $periodDates = generateYearRange($startDate, $endDate);
            break;
    }

    if ($query != "") {
        $exp_fetched = mysqli_query($con, $query);
    }
}

function generateDateRange($start, $end) {
    $dates = [];
    $current = strtotime($start);
    while ($current <= strtotime($end)) {
        $dates[] = date('Y-m-d', $current);
        $current = strtotime("+1 day", $current);
    }
    return $dates;
}

function generateMonthYearRange($start, $end) {
    $arr = [];
    $current = strtotime($start);
    while ($current <= strtotime($end)) {
        $arr[] = date('Y-m', $current);
        $current = strtotime("+1 month", $current);
    }
    return $arr;
}

function generateYearRange($start, $end) {
    $arr = [];
    $current = strtotime($start);
    while ($current <= strtotime($end)) {
        $arr[] = date('Y', $current);
        $current = strtotime("+1 year", $current);
    }
    return $arr;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Expense Report</title>
<link rel="stylesheet" href="css/bootstrap.css">

<style>
/* ============================
Global Layout + Page Background
============================= */
body {
    margin: 0;
    background-color: #E8DCC8;
    font-family: "Segoe UI", sans-serif;
}

.main-container {
    margin-left: 260px;
    padding: 40px;
    min-height: 100vh;
    background-color: #E8DCC8;
}

/* ============================
SIDEBAR (RESTORED ORIGINAL)
============================= */
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
    width: 120px;
    border-radius: 50%;
    border: 3px solid #9a7b55;
}

.sidebar .user h5 { 
    font-weight: bold; 
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
    font-size: 16px;
    color: #2b2b2b;
    text-decoration: none;
    border-bottom: 1px solid #8a6b48;
}

.sidebar a:hover,
.sidebar a.active {
    background: #8a6b48;
    color: #fff;
}


/* ============================
Page Title
============================= */
.page-title {
    font-size: 40px;
    font-weight: 800;
    text-align: center;
    color: #3b2a1a;
    margin-bottom: 40px;
    letter-spacing: 1px;
}

/* ============================
Report Box Card
============================= */
.report-box {
    max-width: 900px;
    margin: auto;
    background: white;
    padding: 30px 40px;
    border-radius: 15px;
    border: 2px solid #000;
    box-shadow: 3px 3px 12px rgba(0,0,0,0.25);
}

/* Labels */
.form-label {
    font-weight: 700;
    color: #4a3824;
    font-size: 16px;
}

/* Input Fields */
.form-control {
    height: 48px;
    border: 2px solid #8B6B48;
    border-radius: 10px;
    background: #fff;
    font-size: 16px;
    padding-left: 12px;
}

.form-control:focus {
    border-color: #000;
    box-shadow: 0 0 6px rgba(0,0,0,0.3);
}

/* Generate Button */
.btn-generate {
    width: 100%;
    height: 55px;
    margin-top: 25px;
    font-size: 20px;
    font-weight: bold;
    background-color: #4DA34D;
    color: white;
    border-radius: 12px;
    border: none;
    transition: 0.3s ease;
}

.btn-generate:hover {
    background-color: #3C8A3C;
    transform: translateY(-3px);
    box-shadow: 3px 4px 12px rgba(0,0,0,0.3);
}

/* ============================
Result Table Styling
============================= */
.result-table {
    margin-top: 40px;
    border: 2px solid black;
    border-radius: 12px;
    overflow: hidden;
}

.result-table th {
    background: #C8A77A;
    color: white;
    padding: 12px;
    font-size: 18px;
    border: 1px solid black;
}

.result-table td {
    background: #fff;
    border: 1px solid black;
    padding: 10px;
    font-size: 16px;
}

.result-table tr:hover td {
    background: #f3e5d2;
}

/* ============================
Mobile Responsive Fixes
============================= */
@media(max-width: 768px){
    .main-container { 
        margin-left: 0; 
        padding: 20px;
    }
    .sidebar { 
        width: 100%; 
        height: auto; 
        position: relative; 
    }
    .report-box {
        padding: 20px;
    }
}
</style>

</head>

<body>

<!-- ========================= SIDEBAR ========================= -->
<div class="sidebar">
    <div class="user">
        <img src="uploads/default_profile.png">
        <h5><?php echo $username; ?></h5>
        <p><?php echo $useremail; ?></p>
    </div>

    <a href="index.php">Dashboard</a>
    <a href="account.php">Accounts</a>
    <a href="savings.php">Savings Goal</a>
    <a href="transaction.php">Add/Manage Expenses</a>
    <a href="report.php" class="active">Expense Report</a>
    <a href="budget.php">Budget</a>

    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
</div>

<!-- ========================= MAIN CONTENT ========================= -->
<div class="main-container">

    <h2 class="page-title">Expense Report</h2>

    <div class="report-box">
        <form method="POST">

            <!-- Report Type -->
            <div class="row mb-3">
                <label class="col-sm-5 form-label">Select Report Type:</label>
                <div class="col-sm-7">
                    <select class="form-control" name="report_type">
                        <option value="datewise">Datewise Report</option>
                        <option value="monthwise">Monthwise Report</option>
                        <option value="yearwise">Yearwise Report</option>
                    </select>
                </div>
            </div>

            <!-- Start Date -->
            <div class="row mb-3">
                <label class="col-sm-5 form-label">Start Date:</label>
                <div class="col-sm-7">
                    <input type="date" class="form-control" name="start_date" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <!-- End Date -->
            <div class="row mb-3">
                <label class="col-sm-5 form-label">End Date:</label>
                <div class="col-sm-7">
                    <input type="date" class="form-control" name="end_date" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <!-- Button -->
            <button class="btn-generate">Generate Report</button>
        </form>

        <!-- ========================= RESULTS TABLE ========================= -->
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($exp_fetched)) { ?>
            <table class="table result-table">
                <thead>
                    <tr>
                        <th>Sl No</th>
                        <th><?php echo $tableHeader; ?></th>
                        <th>Total Amount</th>
                    </tr>
                </thead>

                <tbody>
                <?php
                $count = 1;

                foreach ($periodDates as $pd) {

                    mysqli_data_seek($exp_fetched, 0);
                    $total = 0;

                    while ($r = mysqli_fetch_assoc($exp_fetched)) {

                        if ($reportType == 'datewise' && $pd == $r['period'])
                            $total = $r['totalExpense'];

                        if ($reportType == 'monthwise' &&
                            substr($pd,0,4)==$r['year'] && substr($pd,5,2)==$r['month'])
                            $total = $r['totalExpense'];

                        if ($reportType == 'yearwise' && $pd == $r['year'])
                            $total = $r['totalExpense'];
                    }

                    if ($total > 0) {
                        echo "<tr>
                                <td>$count</td>";

                        if ($reportType == 'datewise')
                            echo "<td>$pd</td>";

                        if ($reportType == 'monthwise')
                            echo "<td>" . date("F Y", strtotime($pd)) . "</td>";

                        if ($reportType == 'yearwise')
                            echo "<td>$pd</td>";

                        echo "<td>$total</td></tr>";

                        $count++;
                    }
                }
                ?>
                </tbody>
            </table>
        <?php } ?>

    </div>

</div>

</body>
</html>
