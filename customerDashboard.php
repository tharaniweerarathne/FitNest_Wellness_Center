<?php
session_start();
if (!isset($_SESSION['email'])) {
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

// handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

//  booking deletion
if (isset($_GET['delete_booking'])) {
    $booking_id = $_GET['delete_booking'];
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ? AND email = ?");
    $stmt->bind_param("is", $booking_id, $email);
    if ($stmt->execute()) {
        $success_message = "Booking deleted successfully!";
    } else {
        $error_message = "Error deleting booking.";
    }
}

// get current membership
$membership = null;
$check = $conn->prepare("SELECT * FROM memberships WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $membership = $result->fetch_assoc();
}

// get user's bookings
$bookings = [];
$bookings_query = $conn->prepare("
    SELECT b.*, s.name as service_name, s.category, t.name as trainer_name 
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN trainer t ON b.trainer_id = t.id
    WHERE b.email = ?
    ORDER BY b.schedule ASC
");
$bookings_query->bind_param("s", $email);
$bookings_query->execute();
$bookings_result = $bookings_query->get_result();

if ($bookings_result->num_rows > 0) {
    while ($row = $bookings_result->fetch_assoc()) {
        $bookings[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Customer Dashboard</title>
  <link rel="stylesheet" href="Customer_dashbard_css.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
</head>

<body>

 
  <div class="sidebar">
    <div class="logo">
      <img src="Images/logo_white.png" alt="FITNEST WELLNESS Logo" class="logo-img">
    </div>
    <ul>
      <li><a href="customerDashboard.php" class="active"><i class="ri-home-4-line"></i> Dashboard</a></li>
      <li><a href="services.php"><i class="ri-calendar-check-line"></i>Book Classes</a></li>
      <li><a href="membership.php"><i class="ri-id-card-line"></i> Membership</a></li>
      <li><a href="customerFeedback.php"><i class="ri-feedback-line"></i> Feedback</a></li>
      <li><a href="EditProfile.php"><i class="ri-user-settings-line"></i> Edit Profile</a></li>
    </ul>
  </div>


  <div class="main-content">

    <header>
      <h1>Customer Dashboard</h1>
      <div class="user-profile">

        <div class="profile-section">
          <i class="ri-user-3-fill profile-icon" onclick="toggleProfileOptions(event)"></i>
          <div class="profile-options" id="profileOptions">
            <a href="EditProfile.php">Edit Profile</a>
          </div>
        </div>
        <span class="user-name">Welcome, <?php echo htmlspecialchars($name); ?></span>

        <button class="logout-btn" onclick="window.location.href='?logout=true'">
        <i class="ri-logout-circle-r-line"></i>
        Logout
    </button>

      </div>
    </header>

    <?php if (isset($success_message)): ?>
        <div class="message success-message">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="message error-message">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>
            
    <?php if ($membership): ?>
        <div class="membership-card active">
        <h2>Your Membership</h2>
            <h3>Current Plan: <?php echo htmlspecialchars($membership['plan']); ?></h3>
            <p>Joined on: <?php echo htmlspecialchars($membership['joined_at']); ?></p>
            
            <div class="membership-actions">
                <a href="EditMembership.php" class="btn edit-btn">
                    <i class="ri-edit-line"></i> Change Plan
                </a>
                <a href="CancelMembership.php" class="btn cancel-btn" 
                   onclick="return confirm('Are you sure you want to cancel your membership?');">
                    <i class="ri-close-circle-line"></i> Cancel Membership
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="membership-card inactive">
            <h3>No Active Membership</h3>
            <p>You don't have an active membership plan.</p>
            <a href="membership.php" class="btn join-btn">Join Now</a>
        </div>
    <?php endif; ?>

    <div class="bookings-container">
        <h2>Your Booked Classes</h2>
        
        <?php if (count($bookings) > 0): ?>
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Trainer</th>
                        <th>Category</th>
                        <th>Schedule</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['service_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['trainer_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['category']); ?></td>
                            <td><?php echo htmlspecialchars($booking['schedule']); ?></td>
                            <td class="status-<?php echo strtolower($booking['status']); ?>">
                                <?php echo htmlspecialchars($booking['status']); ?>
                            </td>
                            <td>
                                <a href="EditBooking.php?id=<?php echo $booking['id']; ?>" class="action-btn edit-btn">
                                    <i class="ri-edit-line"></i> Edit
                                </a>
                                <a href="?delete_booking=<?php echo $booking['id']; ?>" class="action-btn delete-btn" 
                                   onclick="return confirm('Are you sure you want to delete this booking?');">
                                    <i class="ri-delete-bin-line"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You haven't booked any classes yet. <a href="services.php">Browse classes</a> to get started!</p>
        <?php endif; ?>
    </div>

  </div>

  <script>
    function toggleProfileOptions(event) {
      const profileOptions = document.getElementById("profileOptions");
      profileOptions.style.display = (profileOptions.style.display === "block") ? "none" : "block";
      event.stopPropagation();
    }

    document.addEventListener("click", function (event) {
      const profileSection = document.querySelector(".profile-section");
      const profileOptions = document.getElementById("profileOptions");

      if (!profileSection.contains(event.target)) {
        profileOptions.style.display = "none";
      }
    });
  </script>

</body>
</html>