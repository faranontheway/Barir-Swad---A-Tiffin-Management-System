<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['User_ID'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['User_ID'];

// Validate complaint ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "Invalid complaint ID.";
    $_SESSION['msg_type'] = "error";
    header("Location: complaint_dashboard.php");
    exit();
}

$complaint_id = (int) $_GET['id'];

// Fetch complaint & check ownership
$sql = "SELECT * FROM complaint_support WHERE Complaint_ID = ? AND User_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $complaint_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['message'] = "Complaint not found or access denied.";
    $_SESSION['msg_type'] = "error";
    header("Location: complaint_dashboard.php");
    exit();
}

$complaint = $result->fetch_assoc();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['Complaint_Description']);

    if (empty($description)) {
        $_SESSION['message'] = "Description cannot be empty.";
        $_SESSION['msg_type'] = "error";
    } else {
        $update_sql = "UPDATE complaint_support SET Complaint_Description = ? WHERE Complaint_ID = ? AND User_ID = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sii", $description, $complaint_id, $user_id);

        if ($update_stmt->execute()) {
            $_SESSION['message'] = "Complaint updated successfully.";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating complaint.";
            $_SESSION['msg_type'] = "error";
        }
    }
    header("Location: complaint_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Complaint</title>
    <link rel="stylesheet" href="cook_styles.css">
    <style>
        .readonly-field {
            background: #f5f5f5;
            border: 1px solid #ddd;
            padding: 8px;
            border-radius: 6px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Complaint #<?= $complaint['Complaint_ID']; ?></h2>

        <form method="post">
            <label for="Complaint_Description">Complaint Description:</label>
            <textarea name="Complaint_Description" id="Complaint_Description" rows="4" required><?= htmlspecialchars($complaint['Complaint_Description']); ?></textarea>

            <label>Admin Message:</label>
            <div class="readonly-field">
                <?= $complaint['Complaint_Message'] ? nl2br(htmlspecialchars($complaint['Complaint_Message'])) : "<em>No updates yet</em>"; ?>
            </div>

            <button type="submit" class="btn-action">Save Changes</button>
            <a href="complaint_dashboard.php" class="btn-action">Cancel</a>
        </form>
    </div>
</body>
</html>
