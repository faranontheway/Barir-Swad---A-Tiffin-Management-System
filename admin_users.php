<?php
session_start();
require 'dbconnect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';

// Handle user deletion
if ($_POST && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    
    $sql = "DELETE FROM user WHERE U_ID = ? AND Type IN ('Customer', 'Cook')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $message = "User deleted successfully!";
    } else {
        $message = "Error deleting user.";
    }
}

// Handle user type update
if ($_POST && isset($_POST['update_user_type'])) {
    $user_id = $_POST['user_id'];
    $new_type = $_POST['user_type'];
    
    $sql = "UPDATE user SET Type = ? WHERE U_ID = ? AND Type IN ('Customer', 'Cook')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_type, $user_id);
    
    if ($stmt->execute()) {
        $message = "User type updated successfully!";
    } else {
        $message = "Error updating user type.";
    }
}

// Get all users (customers and cooks) with their phone numbers
$sql = "
    SELECT 
        u.U_ID, 
        u.Name, 
        u.Email, 
        u.Type, 
        u.Address, 
        u.Exp_Years,
        GROUP_CONCAT(up.Phone_No SEPARATOR ', ') as Phone_Numbers
    FROM user u
    LEFT JOIN user_phone_no up ON u.U_ID = up.User_ID
    WHERE u.Type IN ('Customer', 'Cook')
    GROUP BY u.U_ID, u.Name, u.Email, u.Type, u.Address, u.Exp_Years
    ORDER BY u.Type, u.Name
";

$users_result = $conn->query($sql);

// Get user statistics
$customer_count = $conn->query("SELECT COUNT(*) as count FROM user WHERE Type = 'Customer'")->fetch_assoc()['count'];
$cook_count = $conn->query("SELECT COUNT(*) as count FROM user WHERE Type = 'Cook'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Barir Swad</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-header {
            background: #28a745;
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
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
            margin: 0 0 10px 0;
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #28a745;
        }
        .message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .users-table {
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
        .user-type {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .user-type.customer { background: #007bff; color: white; }
        .user-type.cook { background: #fd7e14; color: white; }
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
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-sm:hover { opacity: 0.8; }
        .type-form {
            display: inline-block;
        }
        .type-select {
            padding: 4px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
        }
        .exp-badge {
            background: #6c757d;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
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
            <h1>👥 Barir Swad - User Management</h1>
            <nav class="nav-links">
                <a href="admin_dash.php">Dashboard</a>
                <a href="admin_orders.php">Orders</a>
                <a href="admin_users.php" class="active">Users</a>
                <a href="admin_meals.php">Meals</a>
                <a href="admin_complaints.php">Complaints</a>
                <a href="admin_logout.php" class="logout-btn">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h2>👥 User Management</h2>
        
        <div class="stats-cards">
            <div class="stat-card">
                <h3>Total Customers</h3>
                <div class="number"><?= $customer_count ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Cooks</h3>
                <div class="number"><?= $cook_count ?></div>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="users-table">
            <?php if ($users_result->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Address</th>
                            <th>Phone</th>
                            <th>Experience</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= $user['U_ID'] ?></strong></td>
                                <td><?= htmlspecialchars($user['Name']) ?></td>
                                <td><?= htmlspecialchars($user['Email']) ?></td>
                                <td>
                                    <span class="user-type <?= strtolower($user['Type']) ?>">
                                        <?= $user['Type'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($user['Address']) ?></td>
                                <td>
                                    <?= $user['Phone_Numbers'] ? htmlspecialchars($user['Phone_Numbers']) : 'N/A' ?>
                                </td>
                                <td>
                                    <?php if ($user['Type'] == 'Cook'): ?>
                                        <span class="exp-badge"><?= $user['Exp_Years'] ?> years</span>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="POST" class="type-form">
                                            <input type="hidden" name="user_id" value="<?= $user['U_ID'] ?>">
                                            <select name="user_type" class="type-select" onchange="this.form.submit()">
                                                <option value="">Change Type</option>
                                                <option value="Customer" <?= $user['Type'] == 'Customer' ? 'selected' : '' ?>>Customer</option>
                                                <option value="Cook" <?= $user['Type'] == 'Cook' ? 'selected' : '' ?>>Cook</option>
                                            </select>
                                            <input type="hidden" name="update_user_type" value="1">
                                        </form>
                                        
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                            <input type="hidden" name="user_id" value="<?= $user['U_ID'] ?>">
                                            <button type="submit" name="delete_user" class="btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="padding: 40px; text-align: center;">
                    <h3>No users found</h3>
                    <p>Users will appear here once they register.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>