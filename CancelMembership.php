<?php
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['name'])) {
    header("Location: SignIn.php");
    exit();
}

$email = $_SESSION['email'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitnestwellness";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete membership
$stmt = $conn->prepare("DELETE FROM memberships WHERE email = ?");
$stmt->bind_param("s", $email);

if ($stmt->execute()) {
    $message = "Your membership has been cancelled successfully.";
} else {
    $message = "Error cancelling membership: " . $stmt->error;
}

$stmt->close();
$conn->close();

// Redirect back to membership page with message
$_SESSION['cancel_message'] = $message;
header("Location: customerDashboard.php");
exit();
?>