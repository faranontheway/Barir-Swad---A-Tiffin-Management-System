<?php
session_start();
require '../dbconnect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$catering_id = $_GET['id'] ?? 0;
$message = '';

// Handle meal assignment to catering
if ($_POST && isset($_POST['assign_meal'])) {
    $meal_id = $_POST['meal_id'];
    $quantity_per_person = $_POST['quantity_per_person'];
    $catering_id = $_POST['catering_id'];
    
    // Get meal price and catering people count
    $meal_query = $conn->query("SELECT `Pricing List` as price FROM meal WHERE Meal_ID = $meal_id");
    $catering_query = $conn->query("SELECT Number_of_People FROM catering_services WHERE Catering_ID = $catering_id");
    
    if ($meal_query->num_rows > 0 && $catering_query->num_rows > 0) {
        $meal_data = $meal_query->fetch_assoc();
        $catering_data = $catering_query->fetch_assoc();
        
        $unit_price = $meal_data['price'];
        $total_quantity = $catering_data['Number_of_People'] * $quantity_per_person;
        $total_price = $total_quantity * $unit_price;
        
        $sql = "INSERT INTO catering_has_meals (Catering_ID, Meal_ID, Quantity_Per_Person, Total_Quantity, Unit_Price, Total_Price) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE Quantity_Per_Person = ?, Total_Quantity = ?, Total_Price = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iidiiddid", $catering_id, $meal_id, $quantity_per_person, $total_quantity, $unit_price, $total_price, $quantity_per_person, $total_quantity, $total_price);
        
        if ($stmt->execute()) {
            // Update total cost in catering_services
            $update_cost = $conn->query("UPDATE catering_services SET Total_Cost = (SELECT SUM(Total_Price) FROM catering_has_meals WHERE Catering_ID = $catering_id) WHERE Catering_ID = $catering_id");
            $message = "Meal assigned successfully!";
        } else {
            $message = "Error assigning meal.";
        }
    }
}

// Handle meal removal
if ($_POST && isset($_POST['remove_meal'])) {
    $meal_id = $_POST['meal_id'];
    $catering_id = $_POST['catering_id'];
    
    $sql = "DELETE FROM catering_has_meals WHERE Catering_ID = ? AND Meal_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $catering_id, $meal_id);
    
    if ($stmt->execute()) {
        // Update total cost
        $update_cost = $conn->query("UPDATE catering_services SET Total_Cost = COALESCE((SELECT SUM(Total_Price) FROM catering_has_meals WHERE Catering_ID = $catering_id), 0) WHERE Catering_ID = $catering_id");
        $message = "Meal removed successfully!";
    } else {
        $message = "Error removing meal.";
    }
}

// Get catering details
$catering_sql = "
    SELECT cs.*, u.Name as Customer_Name, u.Email as Customer_Email, u.Address as Customer_Address
    FROM catering_services cs
    JOIN user u ON cs.Customer_ID = u.U_ID
    WHERE cs.Catering_ID = ?
";
$stmt = $conn->prepare($catering_sql);
$stmt->bind_param("i", $catering_id);
$stmt->execute();
$catering_result = $stmt->get_result();

if ($catering_result->num_rows == 0) {
    die("Catering booking not found.");
}

$catering = $catering_result->fetch_assoc();

// Get assigned meals
$meals_sql = "
    SELECT chm.*, m.Name as Meal_Name, m.Description, m.Cuisine
    FROM catering_has_meals chm
    JOIN meal m ON chm.Meal_ID = m.Meal_ID
    WHERE chm.Catering_ID = ?
";
$stmt = $conn->prepare($meals_sql);
$stmt->bind_param("i", $catering_id);
$stmt->execute();
$assigned_meals = $stmt->get_result();

// Get available meals for assignment
$available_meals = $conn->query("SELECT * FROM meal ORDER BY Cuisine, Name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catering Details - Barir Swad</title>
    <!-- <link rel="stylesheet" href="style.css"> -->
    <style>
        .admin-header {
            background: #ff9a56;
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
        .catering-details {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .main-details {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .side-info {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .detail-row {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            width: 150px;
            color: #333;
        }
        .detail-value {
            flex: 1;
            color: #666;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-badge.pending { background: #ffc107; color: #212529; }
        .status-badge.confirmed { background: #28a745; color: white; }
        .status-badge.in-progress { background: #17a2b8; color: white; }
        .status-badge.completed { background: #6f42c1; color: white; }
        .status-badge.cancelled { background: #dc3545; color: white; }
        .payment-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .payment-badge.pending { background: #ffc107; color: #212529; }
        .payment-badge.partial { background: #17a2b8; color: white; }
        .payment-badge.full { background: #28a745; color: white; }
        .meals-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .meal-assignment-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 10px;
            align-items: end;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group select, .form-group input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .assigned-meals-table {
            margin-top: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn:hover { opacity: 0.8; }
        .cost-summary {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .cost-summary h4 {
            color: #28a745;
            margin-bottom: 10px;
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
        .message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="admin-nav">
            <h1>📋 Catering Details #<?= $catering['Catering_ID'] ?></h1>
            <nav class="nav-links">
                <a href="../admin/admin_dash.php">Dashboard</a>
                <a href="../admin/admin_catering.php">Back to Catering</a>
                <a href="../logout.php" class="logout-btn">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <div class="catering-details">
            <div class="main-details">
                <h3>Event Information</h3>
                <div class="detail-row">
                    <div class="detail-label">Event Name:</div>
                    <div class="detail-value"><?= htmlspecialchars($catering['Event_Name']) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date & Time:</div>
                    <div class="detail-value">
                        <?= date('F j, Y', strtotime($catering['Event_Date'])) ?> 
                        at <?= date('g:i A', strtotime($catering['Event_Time'])) ?>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Location:</div>
                    <div class="detail-value"><?= htmlspecialchars($catering['Event_Location']) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Number of People:</div>
                    <div class="detail-value"><strong><?= $catering['Number_of_People'] ?></strong> people</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Contact Person:</div>
                    <div class="detail-value"><?= htmlspecialchars($catering['Contact_Person']) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Contact Phone:</div>
                    <div class="detail-value"><?= htmlspecialchars($catering['Contact_Phone']) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Special Requirements:</div>
                    <div class="detail-value">
                        <?= $catering['Special_Requirements'] ? htmlspecialchars($catering['Special_Requirements']) : 'None' ?>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Booking Date:</div>
                    <div class="detail-value"><?= date('F j, Y g:i A', strtotime($catering['Created_Date'])) ?></div>
                </div>
            </div>
            
            <div class="side-info">
                <h3>Customer Information</h3>
                <div class="detail-row">
                    <div class="detail-label">Name:</div>
                    <div class="detail-value"><?= htmlspecialchars($catering['Customer_Name']) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Email:</div>
                    <div class="detail-value"><?= htmlspecialchars($catering['Customer_Email']) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Address:</div>
                    <div class="detail-value"><?= htmlspecialchars($catering['Customer_Address']) ?></div>
                </div>
                
                <h3 style="margin-top: 25px;">Status & Payment</h3>
                <div class="detail-row">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value">
                        <span class="status-badge <?= strtolower(str_replace(' ', '-', $catering['Status'])) ?>">
                            <?= $catering['Status'] ?>
                        </span>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Payment Status:</div>
                    <div class="detail-value">
                        <span class="payment-badge <?= strtolower($catering['Payment_Status']) ?>">
                            <?= $catering['Payment_Status'] ?>
                        </span>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Advance Payment:</div>
                    <div class="detail-value"><strong>৳<?= number_format($catering['Advance_Payment'], 2) ?></strong></div>
                </div>
                
                <div class="cost-summary">
                    <h4>Total Cost: ৳<?= number_format($catering['Total_Cost'], 2) ?></h4>
                    <p>Per Person: ৳<?= number_format($catering['Total_Cost'] / $catering['Number_of_People'], 2) ?></p>
                    <?php if ($catering['Advance_Payment'] > 0): ?>
                        <p>Remaining: ৳<?= number_format($catering['Total_Cost'] - $catering['Advance_Payment'], 2) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Meal Assignment Section -->
        <div class="meals-section">
            <h3>Meal Assignment</h3>
            
            <div class="meal-assignment-form">
                <h4>Assign Meal to Catering</h4>
                <form method="POST">
                    <input type="hidden" name="catering_id" value="<?= $catering_id ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Select Meal</label>
                            <select name="meal_id" required>
                                <option value="">Choose a meal...</option>
                                <?php 
                                $available_meals = $conn->query("SELECT * FROM meal ORDER BY Cuisine, Name");
                                while($meal = $available_meals->fetch_assoc()): 
                                ?>
                                    <option value="<?= $meal['Meal_ID'] ?>">
                                        <?= htmlspecialchars($meal['Name']) ?> - ৳<?= $meal['Pricing List'] ?> (<?= $meal['Cuisine'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Quantity per Person</label>
                            <input type="number" name="quantity_per_person" step="0.1" min="0.1" max="5" value="1" required>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" name="assign_meal" class="btn btn-success">Assign</button>
                        </div>
                    </div>
                </form>
            </div>

            <?php if ($assigned_meals->num_rows > 0): ?>
                <div class="assigned-meals-table">
                    <h4>Assigned Meals</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Meal Name</th>
                                <th>Cuisine</th>
                                <th>Qty/Person</th>
                                <th>Total Qty</th>
                                <th>Unit Price</th>
                                <th>Total Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $assigned_meals = $conn->query("
                                SELECT chm.*, m.Name as Meal_Name, m.Description, m.Cuisine
                                FROM catering_has_meals chm
                                JOIN meal m ON chm.Meal_ID = m.Meal_ID
                                WHERE chm.Catering_ID = $catering_id
                            ");
                            while($meal = $assigned_meals->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($meal['Meal_Name']) ?></strong>
                                        <br><small><?= htmlspecialchars(substr($meal['Description'], 0, 40)) ?>...</small>
                                    </td>
                                    <td><?= htmlspecialchars($meal['Cuisine']) ?></td>
                                    <td><?= $meal['Quantity_Per_Person'] ?></td>
                                    <td><?= $meal['Total_Quantity'] ?></td>
                                    <td>৳<?= number_format($meal['Unit_Price'], 2) ?></td>
                                    <td><strong>৳<?= number_format($meal['Total_Price'], 2) ?></strong></td>
                                    <td>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Remove this meal from catering?')">
                                            <input type="hidden" name="catering_id" value="<?= $catering_id ?>">
                                            <input type="hidden" name="meal_id" value="<?= $meal['Meal_ID'] ?>">
                                            <button type="submit" name="remove_meal" class="btn btn-danger" style="padding: 4px 8px; font-size: 12px;">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 20px; color: #666;">
                    <p>No meals assigned yet. Use the form above to assign meals to this catering service.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center; margin: 20px 0;">
            <a href="../admin/admin_catering.php" class="btn btn-primary">← Back to Catering Management</a>
        </div>
    </div>
</body>
</html>