<?php
session_start();
require '../dbconnect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get dashboard statistics
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM user");
$stats['total_users'] = $result->fetch_assoc()['count'];

// Total customers
$result = $conn->query("SELECT COUNT(*) as count FROM user WHERE Type = 'Customer'");
$stats['total_customers'] = $result->fetch_assoc()['count'];

// Total cooks
$result = $conn->query("SELECT COUNT(*) as count FROM user WHERE Type = 'Cook'");
$stats['total_cooks'] = $result->fetch_assoc()['count'];

// Total meals
$result = $conn->query("SELECT COUNT(*) as count FROM meal");
$stats['total_meals'] = $result->fetch_assoc()['count'];

// Total orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders");
$stats['total_orders'] = $result->fetch_assoc()['count'];

// Pending orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE Status = 'Pending'");
$stats['pending_orders'] = $result->fetch_assoc()['count'];

// Total complaints
$result = $conn->query("SELECT COUNT(*) as count FROM complaint_support");
$stats['total_complaints'] = $result->fetch_assoc()['count'];

// Open complaints
$result = $conn->query("SELECT COUNT(*) as count FROM complaint_support WHERE Status = 'Open'");
$stats['open_complaints'] = $result->fetch_assoc()['count'];

// Recent orders
$recent_orders = $conn->query("
    SELECT o.OrderID, u.Name as Customer, o.Cost, o.Status, o.Date 
    FROM orders o 
    JOIN user u ON o.Customer_ID = u.U_ID 
    ORDER BY o.Date DESC 
    LIMIT 5
");

// Recent users
$recent_users = $conn->query("
    SELECT Name, Email, Type, Address 
    FROM user 
    WHERE Type IN ('Customer', 'Cook') 
    ORDER BY U_ID DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Barir Swad</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-header {
            background: #851c7c;
            color: white;
            padding: 15px 0;
            margin-bottom: 30px;
        }
        .admin-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .nav-links {
            display: flex;
            gap: 20px;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .nav-links a:hover {
            background: rgba(255,255,255,0.2);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            margin: 0;
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
        }
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #007bff;
            margin: 10px 0;
        }
        .dashboard-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }
        .section-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .section-card h3 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status.pending { background: #ffc107; color: white; }
        .status.delivered { background: #28a745; color: white; }
        .status.cancelled { background: #dc3545; color: white; }
        .status.accepted { background: #17a2b8; color: white; }
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        .logout-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="admin-nav">
            <h1> Barir Swad - Admin Panel</h1>
            <nav class="nav-links">
                
                <a href="admin_orders.php">Orders</a>
                <a href="admin_users.php">Users</a>
                <a href="admin_meals.php">Meals</a>
                <a href="../complaint/admin_complaint_dashboard.php">Complaints</a>
                <a href="admin_logout.php" class="logout-btn">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h2>Welcome back <?= htmlspecialchars($_SESSION['admin_name']) ?>! </h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="number"><?= $stats['total_users'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Customers</h3>
                <div class="number"><?= $stats['total_customers'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Cooks</h3>
                <div class="number"><?= $stats['total_cooks'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Meals</h3>
                <div class="number"><?= $stats['total_meals'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="number"><?= $stats['total_orders'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Orders</h3>
                <div class="number"><?= $stats['pending_orders'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Complaints</h3>
                <div class="number"><?= $stats['total_complaints'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Open Complaints</h3>
                <div class="number"><?= $stats['open_complaints'] ?></div>
            </div>
        </div>

        <div class="dashboard-sections">
            <div class="section-card">
                <h3> Recent Orders</h3>
                <?php if ($recent_orders->num_rows > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Cost</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($order = $recent_orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= $order['OrderID'] ?></td>
                                    <td><?= htmlspecialchars($order['Customer']) ?></td>
                                    <td>৳<?= number_format($order['Cost'], 2) ?></td>
                                    <td><span class="status <?= strtolower($order['Status']) ?>"><?= $order['Status'] ?></span></td>
                                    <td><?= $order['Date'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No orders found.</p>
                <?php endif; ?>
            </div>

            <div class="section-card">
                <h3>Recent Users</h3>
                <?php if ($recent_users->num_rows > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($user = $recent_users->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['Name']) ?></td>
                                    <td><?= htmlspecialchars($user['Email']) ?></td>
                                    <td><?= $user['Type'] ?></td>
                                    <td><?= htmlspecialchars($user['Address']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No users found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>