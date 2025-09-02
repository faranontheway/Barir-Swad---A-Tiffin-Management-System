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

// Check ownership before deleting
$sql = "DELETE FROM complaint_support WHERE Complaint_ID = ? AND User_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $complaint_id, $user_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $_SESSION['message'] = "Complaint deleted successfully.";
    $_SESSION['msg_type'] = "success";
} else {
    $_SESSION['message'] = "Complaint not found or access denied.";
    $_SESSION['msg_type'] = "error";
}

header("Location: complaint_dashboard.php");
exit();
