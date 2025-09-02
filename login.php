<?php
session_start();
require 'dbconnect.php';

$message = '';
if (isset($_SESSION['error'])) {
    $message = $_SESSION['error'];
    unset($_SESSION['error']); // Clear it after reading
}

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
        $_SESSION['login_error'] = "Invalid email or password.";
        $_SESSION['form'] = 'login';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
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
        $_SESSION['register_error'] = "Email already exists. Please use a different email.";
        $_SESSION['form'] = 'register';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();

    } else {
        $conn->begin_transaction();
        try {
            // Insert user
            $sql = "INSERT INTO user (Name, Email, Password, Address, Type, Exp_Years) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $exp_years = 0;
            $stmt->bind_param("sssssi", $name, $email, $password, $address, $type, $exp_years);
            $stmt->execute();
            
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
            $_SESSION['register_error'] = "Registration failed. Please try again.";
            $_SESSION['form'] = 'register';
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();

        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Homemade Food">
    <meta name="keywords" content="Food, Order, Homemade, Cuisine, Meal, Catering">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sign Up</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/login_style.css">
</head>

<body>
    <div class="container">
        <!-- LOGIN FORM -->
        <div class="form-box login">
            <form method="POST">
                <h1>Login</h1>
                <?php if (isset($_SESSION['login_error'])): ?>
                    <div class="error-box"><?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?></div>
                <?php endif; ?>     
                <div class="input-box">
                    <input type="email" placeholder="Email" name="email" required>
                    <i class='bx bxs-user'></i>
                </div>
                <div class="input-box">
                    <input type="password" placeholder="Password" name="password" required>
                    <i class='bx bxs-lock-alt'></i>
                </div>
                <button type="submit" class="btn" name="login">Login</button> 
            </form>
        </div>

        <!-- REGISTER FORM -->
        <div class="form-box register">
            <form method="POST">
                <h1>Registration</h1>
                <?php if (isset($_SESSION['register_error'])): ?>
                    <div class="error-box"><?php echo $_SESSION['register_error']; unset($_SESSION['register_error']); ?></div>
                <?php endif; ?>
 
                <div class="input-box">
                    <input type="text" placeholder="Full Name" name="name" required>
                    <i class='bx bxs-user'></i>
                </div>
                <div class="input-box">
                    <input type="email" placeholder="Email" name="email" required>
                    <i class='bx bxs-envelope'></i>
                </div>
                <div class="input-box">
                    <input type="password" placeholder="Password" name="password" required>
                    <i class='bx bxs-lock-alt'></i>
                </div>
                <div class="input-box">
                    <select name="type" required>
                        <option value="">Select User Type</option>
                        <option value="Cook">Cook</option>
                        <option value="Customer">Customer</option>
                    </select>
                    <i class='bx bxs-user'></i>
                </div>
                <div class="input-box">
                    <input type="text" placeholder="Address" name="address" required>
                    <i class='bx bxs-map'></i>
                </div>
                <div class="input-box">
                    <input type="tel" placeholder="Phone" name="phone" required>
                    <i class='bx bxs-phone'></i>
                </div>
                <button type="submit" class="btn" name="register">Register</button>
            </form>
        </div>

        <!-- TOGGLE PANEL -->
        <div class="toggle-box">
            <div class="toggle-panel toggle-left">
                <h1>Hello, Welcome!</h1>
                <p>Don't have an account?</p>
                <button class="btn register-btn">Register</button>
            </div>
            <div class="toggle-panel toggle-right">
                <h1>Welcome Back!</h1>
                <p>Already have an account?</p>
                <button class="btn login-btn">Login</button>
            </div>
        </div>
    </div>

    <script src="script.js"></script>

    <?php if (isset($_SESSION['form']) && $_SESSION['form'] === 'register'): ?>
<script>
    document.querySelector(".container").classList.add("active");
</script>
<?php unset($_SESSION['form']); endif; ?>

<?php if (isset($_SESSION['form']) && $_SESSION['form'] === 'login'): ?>
<script>
    document.querySelector(".container").classList.remove("active");
</script>
<?php unset($_SESSION['form']); endif; ?>


</body>
</html>
