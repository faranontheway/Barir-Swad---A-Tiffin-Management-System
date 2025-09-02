<?php
session_start();
require '../dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get complaint stats
$stats_sql = "SELECT 
                COUNT(*) AS total_complaints, 
                SUM(CASE WHEN Status IN ('Open','In Progress') THEN 1 ELSE 0 END) AS unresolved_complaints
              FROM complaint_support
              WHERE User_ID = ?";
$stmt = $conn->prepare($stats_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Complaint Dashboard</title>
<link rel="stylesheet" href="../assets/css/cook_styles.css"> <!-- reuse CSS -->
</head>
<body>
<header class="header">
    <div class="nav">
        <div class="logo">🥘Barir Swad</div>
        <nav class="nav-links">
            <a href="../dashboard.php" class="btn">Dashboard</a>
            <a href="complaint_dashboard.php" class="btn">Complaints</a>
            <a href="../logout.php" class="btn logout">Logout</a>
        </nav>
    </div>
</header>

<div class="container">

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Complaints</h3>
            <div class="number"><?= $stats['total_complaints'] ?></div>
        </div>
        <div class="stat-card">
            <h3>Unresolved Complaints</h3>
            <div class="number"><?= $stats['unresolved_complaints'] ?></div>
        </div>
    </div>

</div>
</body>
</html>
