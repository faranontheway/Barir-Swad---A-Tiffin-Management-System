<?php
session_start();
require 'dbconnect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';

// Handle status update
if ($_POST && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $sql = "UPDATE orders SET Status = ? WHERE OrderID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $message = "Order status updated successfully!";
    } else {
        $message = "Error updating order status.";
    }
}

// Handle order deletion
if ($_POST && isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];
    
    $sql = "DELETE FROM orders WHERE OrderID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    
    if ($stmt->execute()) {
        $message = "Order deleted successfully!";
    } else {
        $message = "Error deleting order.";
    }
}

// Get all orders with customer details and meal information
$sql = "
    SELECT 
        o.OrderID, 
        u.Name as Customer, 
        u.Email, 
        o.Cost, 
        o.Status, 
        o.Date,
        GROUP_CONCAT(m.Name SEPARATOR ', ') as Meals
    FROM orders o
    JOIN user u ON o.Customer_ID = u.U_ID
    LEFT JOIN orders_have_meal ohm ON o.OrderID = ohm.OrderID
    LEFT JOIN meal m ON ohm.M_ID = m.Meal_ID
    GROUP BY o.OrderID, u.Name, u.Email, o.Cost, o.Status, o.Date
    ORDER BY o.Date DESC, o.OrderID DESC
";

$orders_result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Barir Swad</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-header {
            background: #007bff;
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
        .nav-links a:hover, .nav-links a.active {
            background: rgba(255,255,255,0.2);
        }
        .message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .orders-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th {
            background: #f8f9fa;
            padding: 15px 10px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-weight: bold;
        }
        .table td {
            padding: 12px 10px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: top;
        }
        .table tr:hover {
            background: #f8f9fa;
        }
        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status.pending { background: #ffc107; color: #212529; }
        .status.on-the-way { background: #17a2b8; color: white; }
        .status.accepted { background: #28a745; color: white; }
        .status.delivered { background: #6f42c1; color: white; }
        .status.cancelled { background: #dc3545; color: white; }
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary { background: #007bff; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-sm:hover { opacity: 0.8; }
        .status-form {
            display: inline-block;
        }
        .status-select {
            padding: 4px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
        }
        .catering-badge {
            background: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        .meals-list {
            max-width: 200px;
            word-wrap: break-word;
        }
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
            <h1>📋 Barir Swad - Order Management</h1>
            <nav class="nav-links">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="admin_orders.php" class="active">Orders</a>
                <a href="admin_users.php">Users</a>
                <a href="admin_meals.php">Meals</a>
                <a href="admin_complaints.php">Complaints</a>
                <a href="admin_logout.php" class="logout-btn">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h2>📋 Order Management</h2>
        
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="orders-table">
            <?php if ($orders_result->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Meals</th>
                            <th>Cost</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($order = $orders_result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $order['OrderID'] ?></td>
                                <td><?= htmlspecialchars($order['Customer']) ?></td>
                                <td><?= htmlspecialchars($order['Email']) ?></td>
                                <td class="meals-list">
                                    <?= $order['Meals'] ? htmlspecialchars($order['Meals']) : 'No meals' ?>
                                </td>
                                <td><strong>৳<?= number_format($order['Cost'], 2) ?></strong></td>
                                <td>
                                    <span class="status <?= strtolower(str_replace(' ', '-', $order['Status'])) ?>">
                                        <?= $order['Status'] ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($order['Date'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="POST" class="status-form">
                                            <input type="hidden" name="order_id" value="<?= $order['OrderID'] ?>">
                                            <select name="status" class="status-select" onchange="this.form.submit()">
                                                <option value="">Change Status</option>
                                                <option value="Pending" <?= $order['Status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="Accepted" <?= $order['Status'] == 'Accepted' ? 'selected' : '' ?>>Accepted</option>
                                                <option value="On the way" <?= $order['Status'] == 'On the way' ? 'selected' : '' ?>>On the way</option>
                                                <option value="Delivered" <?= $order['Status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                                <option value="Cancelled" <?= $order['Status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                        
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this order?')">
                                            <input type="hidden" name="order_id" value="<?= $order['OrderID'] ?>">
                                            <button type="submit" name="delete_order" class="btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="padding: 40px; text-align: center;">
                    <h3>No orders found</h3>
                    <p>Orders will appear here once customers start placing them.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
