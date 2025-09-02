<?php
session_start();
require 'dbconnect.php';

$message = '';

// Handle login
if ($_POST && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $sql = "SELECT U_ID, Name, Type FROM user WHERE Email = ? AND Password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['U_ID'];
        $_SESSION['user_name'] = $user['Name'];
        $_SESSION['user_type'] = $user['Type'];
        
        // Redirect based on user type
        switch ($user['Type']) {
            case 'Admin':
                $_SESSION['admin_id'] = $user['U_ID'];
                $_SESSION['admin_name'] = $user['Name'];
                header("Location: admin/admin_dash.php");
                break;
            case 'Customer':
                header("Location: customer/customer_dashboard.php");
                break;
            case 'Cook':
                header("Location: cook/cook_dashboard.php");
                break;
            default:
                header("Location: index.html");
        }
        exit();
    } else {
        $message = "Invalid email or password.";
    }
}

// Handle registration
if ($_POST && isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $address = $_POST['address'] ?? '';
    $type = $_POST['type'] ?? 'Customer';
    $phone = $_POST['phone'] ?? '';
    
    // Check if email already exists
    $check_sql = "SELECT U_ID FROM user WHERE Email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $message = "Email already exists. Please use a different email.";
    } else {
        $conn->begin_transaction();
        try {
            // Insert user (U_ID will be auto-assigned by trigger)
            // Don't specify U_ID in the INSERT statement - let the trigger handle it
            $sql = "INSERT INTO user (Name, Email, Password, Address, Type, Exp_Years) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $exp_years = 0;
            $stmt->bind_param("sssssi", $name, $email, $password, $address, $type, $exp_years);
            $stmt->execute();
            
            // Get the auto-assigned user ID
            $get_id_sql = "SELECT U_ID FROM user WHERE Email = ? AND Name = ? ORDER BY U_ID DESC LIMIT 1";
            $get_id_stmt = $conn->prepare($get_id_sql);
            $get_id_stmt->bind_param("ss", $email, $name);
            $get_id_stmt->execute();
            $id_result = $get_id_stmt->get_result();
            $user_data = $id_result->fetch_assoc();
            $user_id = $user_data['U_ID'];
            
            // Insert phone number if provided
            if (!empty($phone)) {
                $sql_phone = "INSERT INTO user_phone_no (User_ID, Phone_No) VALUES (?, ?)";
                $stmt_phone = $conn->prepare($sql_phone);
                $stmt_phone->bind_param("ii", $user_id, $phone);
                $stmt_phone->execute();
            }
            
            $conn->commit();
            
            // Auto-login after successful registration
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_type'] = $type;
            
            // Redirect based on user type
            switch ($type) {
                case 'Customer':
                    header("Location: customer/customer_dashboard.php");
                    break;
                case 'Cook':
                    header("Location: cook/cook_dashboard.php");
                    break;
                default:
                    header("Location: index.html");
            }
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Registration failed. Please try again. Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barir Swad - Login</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 30px;
            border-radius: 25px;
            background: #f1f1f1;
            padding: 5px;
        }
        
        .tab {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .tab.active {
            background: #667eea;
            color: white;
        }
        
        .form-section {
            display: none;
        }
        
        .form-section.active {
            display: block;
        }
        
        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #5a67d8;
        }
        
        .message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #f5c6cb;
        }
        
        .links {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="tabs">
            <div class="tab active" onclick="showTab('login')">Login</div>
            <div class="tab" onclick="showTab('register')">Register</div>
        </div>
        
        <!-- LOGIN FORM -->
        <div class="form-section active" id="login-form">
            <h2> Login to Barir Swad</h2>
            <?php if ($message && !isset($_POST['register'])): ?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="login-email">Email</label>
                    <input type="email" id="login-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn">Login</button>
            </form>
        </div>
        
        <!-- REGISTER FORM -->
        <div class="form-section" id="register-form">
            <h2> Join Barir Swad</h2>
            <?php if ($message && isset($_POST['register'])): ?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="reg-name">Full Name</label>
                    <input type="text" id="reg-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="reg-email">Email</label>
                    <input type="email" id="reg-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="reg-password">Password</label>
                    <input type="password" id="reg-password" name="password" required maxlength="15">
                </div>
                <div class="form-group">
                    <label for="reg-type">User Type</label>
                    <select id="reg-type" name="type" required>
                        <option value="">Select Type</option>
                        <option value="Customer">Customer</option>
                        <option value="Cook">Cook</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="reg-address">Address</label>
                    <input type="text" id="reg-address" name="address">
                </div>
                <div class="form-group">
                    <label for="reg-phone">Phone (Optional)</label>
                    <input type="number" id="reg-phone" name="phone">
                </div>
                <button type="submit" name="register" class="btn">Register</button>
            </form>
        </div>
        
       
    </div>
    <script>
        function showTab(tabName) {
            // Hide all form sections
            const forms = document.querySelectorAll('.form-section');
            forms.forEach(form => form.classList.remove('active'));
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected form and activate tab
            if (tabName === 'login') {
                document.getElementById('login-form').classList.add('active');
                document.querySelectorAll('.tab')[0].classList.add('active');
            } else {
                document.getElementById('register-form').classList.add('active');
                document.querySelectorAll('.tab')[1].classList.add('active');
            }
        }
        
        // Auto-show register tab if there's a registration error
        <?php if ($message && isset($_POST['register'])): ?>
            showTab('register');
        <?php endif; ?>
    </script>
    
</body>
</html>