<?php
session_start();
require 'dbconnect.php';

// Check if customer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Customer') {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];
$customer_name = $_SESSION['user_name'];

// Get customer's recent orders
$recent_orders_sql = "
    SELECT 
        o.OrderID, 
        o.Cost, 
        o.Status, 
        o.Date,
        o.`Catering Service`,
        GROUP_CONCAT(m.Name SEPARATOR ', ') as Meals
    FROM orders o
    LEFT JOIN orders_have_meal ohm ON o.OrderID = ohm.OrderID
    LEFT JOIN meal m ON ohm.M_ID = m.Meal_ID
    WHERE o.Customer_ID = ?
    GROUP BY o.OrderID, o.Cost, o.Status, o.Date, o.`Catering Service`
    ORDER BY o.Date DESC
    LIMIT 20
";

$stmt = $conn->prepare($recent_orders_sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$recent_orders = $stmt->get_result();

// Get customer statistics
$stats = [];

// Total orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE Customer_ID = $customer_id");
$stats['total_orders'] = $result->fetch_assoc()['count'];

// Pending orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE Customer_ID = $customer_id AND Status = 'Pending'");
$stats['pending_orders'] = $result->fetch_assoc()['count'];

// Delivered orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE Customer_ID = $customer_id AND Status = 'Delivered'");
$stats['delivered_orders'] = $result->fetch_assoc()['count'];

// Total spent
$result = $conn->query("SELECT SUM(Cost) as total FROM orders WHERE Customer_ID = $customer_id AND Status = 'Delivered'");
$stats['total_spent'] = $result->fetch_assoc()['total'] ?? 0;

// Get popular meals for recommendations
$popular_meals = $conn->query("
    SELECT m.*, COUNT(ohm.M_ID) as order_count
    FROM meal m
    LEFT JOIN orders_have_meal ohm ON m.Meal_ID = ohm.M_ID
    GROUP BY m.Meal_ID, m.Name, m.Description, m.`Pricing`, m.Cuisine
    ORDER BY order_count DESC, m.Name
    LIMIT 6
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Barir Swad</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .header {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
        }
        
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #333;
            padding: 8px 16px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .nav-links a:hover {
            background: #f8f9fa;
        }
        
        .nav-links .btn {
            background: #667eea;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
        }
        
        .nav-links .btn:hover {
            background: #5a67d8;
        }
        
        .nav-links .logout {
            background: #dc3545;
        }
        
        .nav-links .logout:hover {
            background: #c82333;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .welcome-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .welcome-section h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .welcome-section p {
            color: #666;
            font-size: 18px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-card.money .number {
            color: #28a745;
        }
        
        .dashboard-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .section-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .section-card h3 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        
        .order-item {
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: background 0.3s;
        }
        
        .order-item:hover {
            background: #f8f9fa;
        }
        
        .order-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .order-id {
            font-weight: bold;
            color: #667eea;
        }
        
        .order-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .order-status.pending { background: #ffc107; color: #212529; }
        .order-status.accepted { background: #17a2b8; color: white; }
        .order-status.on-the-way { background: #28a745; color: white; }
        .order-status.delivered { background: #6f42c1; color: white; }
        .order-status.cancelled { background: #dc3545; color: white; }
        
        .meal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .meal-card {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: transform 0.3s;
        }
        
        .meal-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .meal-card h4 {
            color: #333;
            margin-bottom: 8px;
        }
        
        .meal-price {
            color: #28a745;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .meal-cuisine {
            background: #667eea;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin-bottom: 10px;
            display: inline-block;
        }
        
        .btn-order {
            background: #28a745;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-order:hover {
            background: #218838;
        }
        
        .quick-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .quick-actions .btn {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: transform 0.3s;
        }
        
        .quick-actions .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .cart-indicator {
            position: relative;
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        @media (max-width: 768px) {
            .dashboard-sections {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            .quick-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav">
            <div class="logo">Barir Swad</div>
            <nav class="nav-links">
                <a href="customer_dashboard.php">Dashboard</a>
                <a href="meal.php">Browse Meals</a>
                <a href="view_cart.php" class="cart-indicator">
                    Cart
                    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="cart-count"><?= count($_SESSION['cart']) ?></span>
                    <?php endif; ?>
                </a>
                <span>Welcome, <?= htmlspecialchars($customer_name) ?>!</span>
                <a href="admin_logout.php" class="btn logout">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="welcome-section">
            <h1>Welcome back, <?= htmlspecialchars($customer_name) ?>!</h1>
            <p>Ready to order some delicious homemade food?</p>
            <div class="quick-actions">
                <a href="meal.php" class="btn btn-primary">Browse Meals</a>
                <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                    <a href="view_cart.php" class="btn btn-success">View Cart (<?= count($_SESSION['cart']) ?> items)</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="number"><?= $stats['total_orders'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Orders</h3>
                <div class="number"><?= $stats['pending_orders'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Delivered Orders</h3>
                <div class="number"><?= $stats['delivered_orders'] ?></div>
            </div>
            <div class="stat-card money">
                <h3>Total Spent</h3>
                <div class="number">৳<?= number_format($stats['total_spent'], 2) ?></div>
            </div>
        </div>

        <div class="dashboard-sections">
            <div class="section-card">
                <h3>Recent Orders</h3>
                <?php if ($recent_orders->num_rows > 0): ?>
                    <?php while($order = $recent_orders->fetch_assoc()): ?>
                        <div class="order-item">
                            <div class="order-header">
                                <span class="order-id">Order #<?= $order['OrderID'] ?></span>
                                <span class="order-status <?= strtolower(str_replace(' ', '-', $order['Status'])) ?>">
                                    <?= $order['Status'] ?>
                                </span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong>৳<?= number_format($order['Cost'], 2) ?></strong><br>
                                    <small><?= date('M j, Y', strtotime($order['Date'])) ?></small>
                                </div>
                                <div style="text-align: right; max-width: 200px;">
                                    <small><?= $order['Meals'] ? htmlspecialchars($order['Meals']) : 'No meals listed' ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <div style="text-align: center; margin-top: 15px;">
                        <a href="customer_orders.php" style="color: #667eea; text-decoration: none;">View All Orders →</a>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #666;">No orders yet. <a href="meal.php" style="color: #667eea;">Start by browsing our meals!</a></p>
                <?php endif; ?>
            </div>

            <div class="section-card">
                <h3>Popular Meals</h3>
                <?php if ($popular_meals->num_rows > 0): ?>
                    <div class="meal-grid">
                        <?php while($meal = $popular_meals->fetch_assoc()): ?>
                            <div class="meal-card">
                                <h4><?= htmlspecialchars($meal['Name']) ?></h4>
                                <div class="meal-cuisine"><?= htmlspecialchars($meal['Cuisine']) ?></div>
                                <div class="meal-price">৳<?= number_format($meal['Pricing'], 2) ?></div>
                                <a href="add_to_cart.php?meal_id=<?= $meal['Meal_ID'] ?>" class="btn-order">Add to Cart</a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="meal.php" style="color: #667eea; text-decoration: none;">View All Meals →</a>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #666;">No meals available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>