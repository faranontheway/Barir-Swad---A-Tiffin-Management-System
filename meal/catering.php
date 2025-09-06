<?php
session_start();
require '../dbconnect.php';

$message = '';
$success = false;

// Handle catering booking submission
if ($_POST && isset($_POST['book_catering'])) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $message = "Please login to book catering services.";
    } else {
        $customer_id = $_SESSION['user_id'];
        $event_name = $_POST['event_name'];
        $event_date = $_POST['event_date'];
        $event_time = $_POST['event_time'];
        $event_location = $_POST['event_location'];
        $number_of_people = $_POST['number_of_people'];
        $contact_person = $_POST['contact_person'];
        $contact_phone = $_POST['contact_phone'];
        $special_requirements = $_POST['special_requirements'] ?? '';
        
        // Validate date is not in the past
        $today = date('Y-m-d');
        if ($event_date < $today) {
            $message = "Event date cannot be in the past.";
        } else {
            // Insert catering booking
            $sql = "INSERT INTO catering_services (Customer_ID, Event_Name, Event_Date, Event_Time, Event_Location, Number_of_People, Contact_Person, Contact_Phone, Special_Requirements) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssisss", $customer_id, $event_name, $event_date, $event_time, $event_location, $number_of_people, $contact_person, $contact_phone, $special_requirements);
            
            if ($stmt->execute()) {
                $booking_id = $stmt->insert_id;
                $message = "Catering booking submitted successfully! Your booking ID is #$booking_id. We will contact you within 24 hours.";
                $success = true;
            } else {
                $message = "Error submitting booking. Please try again.";
            }
        }
    }
}

// Get sample catering packages (from meals table)
$packages_sql = "SELECT * FROM meal WHERE Cuisine IN ('Bengali', 'Indian', 'Chinese') ORDER BY Pricing LIMIT 6";
$packages_result = $conn->query($packages_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catering Services - Barir Swad</title>
    <!-- <link rel="stylesheet" href="style.css"> -->
    <style>
        .catering-hero {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('catering-bg.jpg');
            background-size: cover;
            background-position: center;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            margin-bottom: 50px;
        }
        
        .catering-hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .catering-hero p {
            font-size: 20px;
            max-width: 600px;
        }
        
        .services-section {
            max-width: 1200px;
            margin: 0 auto 50px auto;
            padding: 0 20px;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .service-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
        }
        
        .service-card h3 {
            color: #ff6b35;
            margin-bottom: 15px;
            font-size: 24px;
        }
        
        .service-card ul {
            text-align: left;
            color: #666;
            margin-bottom: 20px;
        }
        
        .service-card ul li {
            margin-bottom: 8px;
        }
        
        .booking-section {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .booking-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group-full {
            grid-column: 1 / -1;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ff6b35;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .btn-catering {
            background: #ff6b35;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
        }
        
        .btn-catering:hover {
            background: #e55a2b;
        }
        
        .packages-section {
            margin-bottom: 50px;
        }
        
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .package-card {
            background: white;
            border: 2px solid #f0f0f0;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: border-color 0.3s, transform 0.3s;
        }
        
        .package-card:hover {
            border-color: #ff6b35;
            transform: translateY(-3px);
        }
        
        .package-card h4 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .package-price {
            color: #ff6b35;
            font-size: 24px;
            font-weight: bold;
            margin: 15px 0;
        }
        
        .cuisine-tag {
            background: #ff6b35;
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
        }
        
        .navbar .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .navbar .logo img {
            width: 40px;
            height: 40px;
        }
        
        .navbar .logo h2 {
            color: #ff6b35;
            font-size: 24px;
        }
        
        .navbar nav {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .navbar nav a {
            text-decoration: none;
            color: #333;
            padding: 8px 16px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .navbar nav a:hover {
            background: #f8f9fa;
        }
        
        .navbar nav .btn {
            background: #ff6b35;
            color: white;
        }
        
        .navbar nav .btn:hover {
            background: #e55a2b;
        }
        
        .pricing-info {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .pricing-info h3 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .pricing-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #666;
        }
        
        .feature-icon {
            color: #28a745;
            font-size: 18px;
        }
        
        @media (max-width: 768px) {
            .booking-form {
                grid-template-columns: 1fr;
            }
            
            .catering-hero h1 {
                font-size: 32px;
            }
            
            .catering-hero p {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <header class="navbar">
        <div class="container">
            <div class="logo">
                <img src="../assets/images/welcomeart.jpg" alt="Barir Swad Logo">
                <h2>Barir Swad</h2>
            </div>
            <nav>
                <a href="../index.php">Home</a>
                <a href="../meal/meal.php">Menu</a>
                <a href="catering.php">Catering</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_type'] == 'Customer'): ?>
                        <a href="../customer/customer_dashboard.php">Dashboard</a>
                    <?php elseif ($_SESSION['user_type'] == 'Cook'): ?>
                        <a href="../cook/cook_dashboard.php">Dashboard</a>
                    <?php endif; ?>
                    <a href="../logout.php">Logout</a>
                <?php else: ?>
                    <a href="../login.php" class="btn">Login / Sign Up</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="catering-hero">
        <div>
            <h1>Catering Services</h1>
            <p>Let us make your special events memorable with authentic homemade flavors</p>
        </div>
    </section>

    <div class="services-section">
        <!-- Pricing Information -->
        <div class="pricing-info">
            <h3>Our Catering Services Include</h3>
            <div class="pricing-features">
                <div class="feature-item">
                    <span class="feature-icon">✓</span>
                    <span>Professional food preparation</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">✓</span>
                    <span>Fresh, homemade quality</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">✓</span>
                    <span>Customizable menu options</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">✓</span>
                    <span>Professional serving setup</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">✓</span>
                    <span>Advance booking available</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">✓</span>
                    <span>Flexible payment options</span>
                </div>
            </div>
        </div>

        <!-- Service Types -->
        <h2 style="text-align: center; margin-bottom: 30px; color: #333;">Our Catering Services</h2>
        <div class="services-grid">
            <div class="service-card">
                <h3>Wedding Catering</h3>
                <ul>
                    <li>Traditional Bengali wedding menu</li>
                    <li>Buffet style serving</li>
                    <li>Decorative food presentation</li>
                    <li>Minimum 50 guests</li>
                    <li>Starting from ৳250 per person</li>
                </ul>
            </div>
            
            <div class="service-card">
                <h3>Corporate Events</h3>
                <ul>
                    <li>Professional lunch meetings</li>
                    <li>Office parties and celebrations</li>
                    <li>Continental and Bengali options</li>
                    <li>Minimum 20 guests</li>
                    <li>Starting from ৳200 per person</li>
                </ul>
            </div>
            
            <div class="service-card">
                <h3>Private Parties</h3>
                <ul>
                    <li>Birthday parties and family gatherings</li>
                    <li>Anniversary celebrations</li>
                    <li>Customizable menu</li>
                    <li>Minimum 15 guests</li>
                    <li>Starting from ৳180 per person</li>
                </ul>
            </div>
        </div>

        <!-- Sample Menu Packages -->
        <h2 style="text-align: center; margin-bottom: 30px; color: #333;">Sample Menu Items</h2>
        <div class="packages-section">
            <div class="packages-grid">
                <?php if ($packages_result->num_rows > 0): ?>
                    <?php while($package = $packages_result->fetch_assoc()): ?>
                        <div class="package-card">
                            <div class="cuisine-tag"><?= htmlspecialchars($package['Cuisine']) ?></div>
                            <h4><?= htmlspecialchars($package['Name']) ?></h4>
                            <p><?= htmlspecialchars($package['Description']) ?></p>
                            <div class="package-price">৳<?= number_format($package['Pricing'], 0) ?></div>
                            <small>per person</small>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="booking-section">
            <h2 style="text-align: center; margin-bottom: 30px; color: #333;">Book Your Catering Service</h2>
            
            <?php if ($message): ?>
                <div class="message <?= $success ? 'success' : 'error' ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="message error">
                    Please <a href="../login.php" style="color: #ff6b35;">login</a> to book catering services.
                </div>
            <?php else: ?>
                <form method="POST" class="booking-form">
                    <div class="form-group">
                        <label for="event_name">Event Name *</label>
                        <input type="text" id="event_name" name="event_name" required 
                               placeholder="e.g., Wedding Reception, Birthday Party">
                    </div>
                    
                    <div class="form-group">
                        <label for="number_of_people">Number of People *</label>
                        <input type="number" id="number_of_people" name="number_of_people" 
                               min="15" max="500" required placeholder="Minimum 15 guests">
                    </div>
                    
                    <div class="form-group">
                        <label for="event_date">Event Date *</label>
                        <input type="date" id="event_date" name="event_date" required 
                               min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="event_time">Event Time *</label>
                        <input type="time" id="event_time" name="event_time" required>
                    </div>
                    
                    <div class="form-group form-group-full">
                        <label for="event_location">Event Location *</label>
                        <textarea id="event_location" name="event_location" required 
                                  placeholder="Full address of the event venue"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_person">Contact Person *</label>
                        <input type="text" id="contact_person" name="contact_person" required 
                               placeholder="Primary contact for event">
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_phone">Contact Phone *</label>
                        <input type="tel" id="contact_phone" name="contact_phone" required 
                               placeholder="e.g., 01XXXXXXXXX">
                    </div>
                    
                    <div class="form-group form-group-full">
                        <label for="special_requirements">Special Requirements</label>
                        <textarea id="special_requirements" name="special_requirements" 
                                  placeholder="Any dietary restrictions, special requests, or additional information"></textarea>
                    </div>
                    
                    <div class="form-group form-group-full">
                        <button type="submit" name="book_catering" class="btn-catering">
                            Submit Catering Request
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Calculate estimated cost based on number of people
        document.getElementById('number_of_people').addEventListener('input', function() {
            const people = this.value;
            if (people >= 15) {
                const estimatedCost = people * 200; // Base rate of ৳200 per person
                const costDisplay = document.getElementById('cost-estimate');
                if (!costDisplay) {
                    const costDiv = document.createElement('div');
                    costDiv.id = 'cost-estimate';
                    costDiv.style.cssText = 'margin-top: 10px; padding: 10px; background: #e8f5e8; border-radius: 5px; text-align: center; color: #28a745; font-weight: bold;';
                    this.parentNode.appendChild(costDiv);
                }
                document.getElementById('cost-estimate').innerHTML = 
                    'Estimated Cost: ৳' + estimatedCost.toLocaleString() + ' (Base rate: ৳200/person)';
            }
        });

        // Set minimum date to tomorrow
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            const dateInput = document.getElementById('event_date');
            dateInput.min = tomorrow.toISOString().split('T')[0];
        });
    </script>
</body>
</html>