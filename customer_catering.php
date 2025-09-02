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

$message = '';

// Handle booking cancellation
if ($_POST && isset($_POST['cancel_booking'])) {
    $catering_id = $_POST['catering_id'];
    
    // Only allow cancellation if status is Pending or Confirmed
    $check_sql = "SELECT Status FROM catering_services WHERE Catering_ID = ? AND Customer_ID = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $catering_id, $customer_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $booking = $check_result->fetch_assoc();
        if (in_array($booking['Status'], ['Pending', 'Confirmed'])) {
            $cancel_sql = "UPDATE catering_services SET Status = 'Cancelled' WHERE Catering_ID = ? AND Customer_ID = ?";
            $cancel_stmt = $conn->prepare($cancel_sql);
            $cancel_stmt->bind_param("ii", $catering_id, $customer_id);
            
            if ($cancel_stmt->execute()) {
                $message = "Booking cancelled successfully.";
            } else {
                $message = "Error cancelling booking.";
            }
        } else {
            $message = "Cannot cancel booking with status: " . $booking['Status'];
        }
    }
}

// Get customer's catering bookings
$catering_sql = "
    SELECT cs.*, 
           COUNT(chm.Meal_ID) as assigned_meals_count,
           SUM(chm.Total_Price) as calculated_cost
    FROM catering_services cs
    LEFT JOIN catering_has_meals chm ON cs.Catering_ID = chm.Catering_ID
    WHERE cs.Customer_ID = ?
    GROUP BY cs.Catering_ID
    ORDER BY cs.Event_Date DESC, cs.Created_Date DESC
";

$stmt = $conn->prepare($catering_sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$catering_bookings = $stmt->get_result();

// Get customer catering statistics
$stats = [];

// Total bookings
$result = $conn->query("SELECT COUNT(*) as count FROM catering_services WHERE Customer_ID = $customer_id");
$stats['total_bookings'] = $result->fetch_assoc()['count'];

// Upcoming events
$result = $conn->query("SELECT COUNT(*) as count FROM catering_services WHERE Customer_ID = $customer_id AND Event_Date >= CURDATE() AND Status NOT IN ('Cancelled', 'Completed')");
$stats['upcoming_events'] = $result->fetch_assoc()['count'];

// Completed events
$result = $conn->query("SELECT COUNT(*) as count FROM catering_services WHERE Customer_ID = $customer_id AND Status = 'Completed'");
$stats['completed_events'] = $result->fetch_assoc()['count'];

// Total spent on catering
$result = $conn->query("SELECT SUM(Total_Cost) as total FROM catering_services WHERE Customer_ID = $customer_id AND Status = 'Completed'");
$stats['total_spent'] = $result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Catering Bookings - Barir Swad</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
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
            color: #ff6b35;
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
            background: #ff6b35;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
        }
        
        .nav-links .btn:hover {
            background: #e55a2b;
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
        
        .page-header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .page-header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .page-header p {
            color: #666;
            font-size: 18px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
            color: #ff6b35;
            margin-bottom: 5px;
        }
        
        .stat-card.money .number {
            color: #28a745;
        }
        
        .bookings-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .bookings-header {
            background: #f8f9fa;
            padding: 20px 30px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .bookings-header h2 {
            color: #333;
            margin: 0;
        }
        
        .booking-card {
            padding: 25px 30px;
            border-bottom: 1px solid #dee2e6;
            transition: background 0.3s;
        }
        
        .booking-card:hover {
            background: #f8f9fa;
        }
        
        .booking-card:last-child {
            border-bottom: none;
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .booking-id {
            font-size: 18px;
            font-weight: bold;
            color: #ff6b35;
        }
        
        .booking-status {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .booking-status.pending { background: #ffc107; color: #212529; }
        .booking-status.confirmed { background: #28a745; color: white; }
        .booking-status.in-progress { background: #17a2b8; color: white; }
        .booking-status.completed { background: #6f42c1; color: white; }
        .booking-status.cancelled { background: #dc3545; color: white; }
        
        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-weight: bold;
            color: #333;
        }
        
        .booking-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-primary { background: #007bff; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-success { background: #28a745; color: white; }
        
        .btn:hover { opacity: 0.8; }
        
        .payment-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .payment-info h4 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .payment-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .no-bookings {
            text-align: center;
            padding: 60px 30px;
            color: #666;
        }
        
        .no-bookings h3 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        
        .quick-actions {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .quick-actions .btn {
            padding: 12px 24px;
            font-size: 16px;
            margin: 0 10px;
        }
        
        @media (max-width: 768px) {
            .booking-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .booking-details {
                grid-template-columns: 1fr;
            }
            
            .quick-actions .btn {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav">
            <div class="logo">Barir Swad - Catering</div>
            <nav class="nav-links">
                <a href="customer_dashboard.php">Dashboard</a>
                <a href="meal.php">Browse Meals</a>
                <a href="catering.php">Book Catering</a>
                <a href="customer_catering.php">My Catering</a>
                <span>Welcome, <?= htmlspecialchars($customer_name) ?>!</span>
                <a href="admin_logout.php" class="btn logout">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h1>My Catering Bookings</h1>
            <p>Manage your catering service requests and track event planning</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Bookings</h3>
                <div class="number"><?= $stats['total_bookings'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Upcoming Events</h3>
                <div class="number"><?= $stats['upcoming_events'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Completed Events</h3>
                <div class="number"><?= $stats['completed_events'] ?></div>
            </div>
            <div class="stat-card money">
                <h3>Total Spent</h3>
                <div class="number">৳<?= number_format($stats['total_spent'], 2) ?></div>
            </div>
        </div>
        
        <div class="quick-actions">
            <a href="catering.php" class="btn btn-success">Book New Catering Service</a>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?= strpos($message, 'Error') !== false ? 'error' : '' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="bookings-section">
            <div class="bookings-header">
                <h2>Your Catering Bookings</h2>
            </div>
            
            <?php if ($catering_bookings->num_rows > 0): ?>
                <?php while($booking = $catering_bookings->fetch_assoc()): ?>
                    <div class="booking-card">
                        <div class="booking-header">
                            <div>
                                <div class="booking-id">Booking #<?= $booking['Catering_ID'] ?></div>
                                <h3><?= htmlspecialchars($booking['Event_Name']) ?></h3>
                            </div>
                            <div class="booking-status <?= strtolower(str_replace(' ', '-', $booking['Status'])) ?>">
                                <?= $booking['Status'] ?>
                            </div>
                        </div>
                        
                        <div class="booking-details">
                            <div class="detail-item">
                                <div class="detail-label">Event Date</div>
                                <div class="detail-value"><?= date('F j, Y', strtotime($booking['Event_Date'])) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Event Time</div>
                                <div class="detail-value"><?= date('g:i A', strtotime($booking['Event_Time'])) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Number of People</div>
                                <div class="detail-value"><?= $booking['Number_of_People'] ?> guests</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Total Cost</div>
                                <div class="detail-value">৳<?= number_format($booking['Total_Cost'], 2) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Contact Person</div>
                                <div class="detail-value"><?= htmlspecialchars($booking['Contact_Person']) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Meals Assigned</div>
                                <div class="detail-value"><?= $booking['assigned_meals_count'] ?> items</div>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Event Location</div>
                            <div class="detail-value"><?= htmlspecialchars($booking['Event_Location']) ?></div>
                        </div>
                        
                        <?php if ($booking['Special_Requirements']): ?>
                            <div class="detail-item" style="margin-top: 15px;">
                                <div class="detail-label">Special Requirements</div>
                                <div class="detail-value"><?= htmlspecialchars($booking['Special_Requirements']) ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($booking['Total_Cost'] > 0): ?>
                            <div class="payment-info">
                                <h4>Payment Information</h4>
                                <div class="payment-row">
                                    <span>Total Cost:</span>
                                    <span><strong>৳<?= number_format($booking['Total_Cost'], 2) ?></strong></span>
                                </div>
                                <div class="payment-row">
                                    <span>Advance Paid:</span>
                                    <span>৳<?= number_format($booking['Advance_Payment'], 2) ?></span>
                                </div>
                                <div class="payment-row">
                                    <span>Remaining:</span>
                                    <span><strong>৳<?= number_format($booking['Total_Cost'] - $booking['Advance_Payment'], 2) ?></strong></span>
                                </div>
                                <div class="payment-row">
                                    <span>Payment Status:</span>
                                    <span class="booking-status <?= strtolower($booking['Payment_Status']) ?>">
                                        <?= $booking['Payment_Status'] ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="booking-actions">
                            <?php 
                            $can_cancel = in_array($booking['Status'], ['Pending', 'Confirmed']);
                            $is_upcoming = strtotime($booking['Event_Date']) >= strtotime('today');
                            ?>
                            
                            <?php if ($can_cancel && $is_upcoming): ?>
                                <form method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                                    <input type="hidden" name="catering_id" value="<?= $booking['Catering_ID'] ?>">
                                    <button type="submit" name="cancel_booking" class="btn btn-danger">
                                        Cancel Booking
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($booking['Status'] == 'Confirmed' && $booking['assigned_meals_count'] > 0): ?>
                                <button class="btn btn-primary" onclick="viewMealDetails(<?= $booking['Catering_ID'] ?>)">
                                    View Menu
                                </button>
                            <?php endif; ?>
                            
                            <small style="color: #666; margin-left: 10px;">
                                Booked on <?= date('M j, Y', strtotime($booking['Created_Date'])) ?>
                            </small>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-bookings">
                    <h3>No catering bookings yet</h3>
                    <p>Start planning your special event with our catering services!</p>
                    <a href="catering.php" class="btn btn-success" style="margin-top: 20px;">
                        Book Your First Catering Service
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function viewMealDetails(cateringId) {
            // This could open a modal or redirect to a detailed view
            alert('Meal details view will be implemented. Catering ID: ' + cateringId);
            // In a real implementation, you might:
            // window.open('catering_menu_details.php?id=' + cateringId, '_blank');
        }
        
        // Auto-refresh page every 30 seconds to show status updates
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>