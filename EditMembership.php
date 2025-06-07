<?php
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['name'])) {
    header("Location: SignIn.php");
    exit();
}

$name = $_SESSION['name'];
$email = $_SESSION['email'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitnestwellness";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// get current membership
$current_plan = null;
$check = $conn->prepare("SELECT plan FROM memberships WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $current_plan = $row['plan'];
}

// handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_plan'])) {
    $new_plan = $_POST['new_plan'];
    
    $stmt = $conn->prepare("UPDATE memberships SET plan = ? WHERE email = ?");
    $stmt->bind_param("ss", $new_plan, $email);
    
    if ($stmt->execute()) {
        $message = "Your membership plan has been updated to <strong>$new_plan</strong> successfully!";
        $current_plan = $new_plan;
    } else {
        $message = "Error updating membership: " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Membership</title>
    <link rel="stylesheet" href="editMemership.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" rel="stylesheet">
</head>


<body>

    <div class="main-content">

        <div class="edit-membership-container">
            <h2><i class="ri-refresh-line"></i> Change Membership Plan</h2>
            
            <?php if (isset($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="membership-form">
                <div class="form-group">
                    <label for="new_plan"><i class="ri-account-box-line"></i> Select New Plan:</label>
                    <select name="new_plan" id="new_plan" required>
                        <option value="">-- Select Plan --</option>
                        <option value="basic" <?= ($current_plan === 'basic') ? 'selected' : '' ?>>Basic Package(Rs. 6000/month)</option>
                        <option value="premium" <?= ($current_plan === 'premium') ? 'selected' : '' ?>>Premium Package (Rs. 10,000/month)</option>
                        <option value="VIP" <?= ($current_plan === 'elite') ? 'selected' : '' ?>>Elite Package(Rs. 15,000/month)</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn update-btn">
                        <i class="ri-check-line"></i> Update Plan
                    </button>
                    <a href="customerDashboard.php" class="btn cancel-btn">
                        <i class="ri-close-line"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>