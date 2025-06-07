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

// booking deletion
if (isset($_GET['cancel_booking'])) {
  $booking_id = $_GET['cancel_booking'];
  $user_role = $_SESSION['role'];

  if ($user_role == 'Staff' || $user_role == 'Admin') {
      // mark the booking as cancelled
      $stmt = $conn->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ?");
      $stmt->bind_param("i", $booking_id);

      if ($stmt->execute()) {
          $success_message = "Booking marked as cancelled.";
      } else {
          $error_message = "Failed to cancel the booking.";
      }
  } else {
      $error_message = "You do not have permission to cancel bookings.";
  }
}


// get all bookings for staff to view
$all_bookings = [];
$all_bookings_query = $conn->query("
    SELECT b.*, s.name as service_name, s.category, t.name as trainer_name, u.name as client_name
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN trainer t ON b.trainer_id = t.id
    JOIN users u ON b.email = u.email
    ORDER BY b.schedule ASC
");

if ($all_bookings_query->num_rows > 0) {
    while ($row = $all_bookings_query->fetch_assoc()) {
        $all_bookings[] = $row;
    }
}

// get all memberships data
$memberships = [];
$memberships_query = $conn->query("
    SELECT id, name, plan, joined_at
    FROM memberships
    ORDER BY joined_at DESC
");

if ($memberships_query->num_rows > 0) {
    while ($row = $memberships_query->fetch_assoc()) {
        $memberships[] = $row;
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="Admin_dashboard_Css.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
</head>

<body>

  <div class="sidebar">
    <div class="logo">
      <img src="Images/logo_white.png" alt="FITNEST WELLNESS Logo" class="logo-img">
    </div>
    <ul>
    <li><a href="DashboardAdmin.php" class="active"><i class="ri-home-4-line"></i> Dashboard</a></li>
      <li><a href="ManageProfile_admin.php" ><i class="ri-user-settings-line"></i>Manage Profiles</a></li>
      <li><a href="InsertServices.php" ><i class="ri-add-circle-line"></i> Insert Services</a></li>
      <li><a href="addStaff.php"><i class="ri-user-add-line"></i>  Add Trainer</a></li>
      <li><a href="feedbackReply.php" ><i class="ri-feedback-line"></i> Feedback Reply</a></li>
      <li><a href="addBlog.php"><i class="ri-article-line"></i> Add Blog</a></li>
      <li><a href="EditProfile.php"><i class="ri-user-settings-line"></i> Edit Profile</a></li>
    </ul>
  </div>

  <div class="main-content">

    <header>
      <h1>Admin Dashboard</h1>
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
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="message error-message">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="tab-container">
      <div class="tab-buttons">
      <button class="tab-btn active" onclick="openTab('myClasses')">All Bookings</button>
        <button class="tab-btn" onclick="openTab('memberships')">Memberships</button> 
      </div>
      

      <div id="myClasses" class="tab-content active">
        <div class="bookings-container">
        <h2>All Bookings</h2>
          
          <?php if (count($all_bookings) > 0): ?>
              <table class="bookings-table">
                  <thead>
                      <tr>
                          <th>Client</th>
                          <th>Class</th>
                          <th>Category</th>
                          <th>Trainer</th>
                          <th>Schedule</th>
                          <th>Status</th>
                          <th>Actions</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php foreach ($all_bookings as $booking): ?>
                          <tr>
                              <td><?php echo htmlspecialchars($booking['client_name']); ?></td>
                              <td><?php echo htmlspecialchars($booking['service_name']); ?></td>
                              <td><?php echo htmlspecialchars($booking['category']); ?></td>
                              <td><?php echo htmlspecialchars($booking['trainer_name']); ?></td>
                              <td><?php echo htmlspecialchars($booking['schedule']); ?></td>
                              <td class="status-<?php echo strtolower($booking['status']); ?>">
                                  <?php echo htmlspecialchars($booking['status']); ?>
                              </td>
                              <td>
                                  <a href="?cancel_booking=<?php echo $booking['id']; ?>" class="action-btn delete-btn" 
                                     onclick="return confirm('Are you sure you want to cancel this booking?');">
                                      <i class="ri-delete-bin-line"></i> Cancel
                                  </a>
                              </td>
                          </tr>
                      <?php endforeach; ?>
                  </tbody>
              </table>   
          <?php else: ?>
              <p>There are no bookings in the system.</p>
          <?php endif; ?>
        </div>
      </div>

      <div id="allBookings" class="tab-content">
        <div class="bookings-container">
          <h2>All Bookings</h2>
          
          <?php if (count($all_bookings) > 0): ?>
              <table class="bookings-table">
                  <thead>
                      <tr>
                          <th>Client</th>
                          <th>Class</th>
                          <th>Category</th>
                          <th>Trainer</th>
                          <th>Schedule</th>
                          <th>Status</th>
                          <th>Actions</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php foreach ($all_bookings as $booking): ?>
                          <tr>
                              <td><?php echo htmlspecialchars($booking['client_name']); ?></td>
                              <td><?php echo htmlspecialchars($booking['service_name']); ?></td>
                              <td><?php echo htmlspecialchars($booking['category']); ?></td>
                              <td><?php echo htmlspecialchars($booking['trainer_name']); ?></td>
                              <td><?php echo htmlspecialchars($booking['schedule']); ?></td>
                              <td class="status-<?php echo strtolower($booking['status']); ?>">
                                  <?php echo htmlspecialchars($booking['status']); ?>
                              </td>
                              <td>
                                  <a href="?cancel_booking=<?php echo $booking['id']; ?>" class="action-btn delete-btn" 
                                     onclick="return confirm('Are you sure you want to cancel this booking?');">
                                      <i class="ri-delete-bin-line"></i> Cancel
                                  </a>
                              </td>
                          </tr>
                      <?php endforeach; ?>
                  </tbody>
              </table>   
          <?php else: ?>
              <p>There are no bookings in the system.</p>
          <?php endif; ?>
        </div>
      </div>




      <div id="memberships" class="tab-content">
        <div class="bookings-container">
          <h2>All Customer Memberships</h2>
          
          <?php if (count($memberships) > 0): ?>
              <table class="bookings-table">
                  <thead>
                      <tr>
                          <th>ID</th>
                          <th>Customer Name</th>
                          <th>Membership Plan</th>
                          <th>Joined Date</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php foreach ($memberships as $membership): ?>
                          <tr>
                              <td><?php echo htmlspecialchars($membership['id']); ?></td>
                              <td><?php echo htmlspecialchars($membership['name']); ?></td>
                              <td><?php echo htmlspecialchars($membership['plan']); ?></td>
                              <td><?php echo htmlspecialchars($membership['joined_at']); ?></td>
                          </tr>
                      <?php endforeach; ?>
                  </tbody>
              </table>   
          <?php else: ?>
              <p>There are no memberships in the system.</p>
          <?php endif; ?>
        </div>
      </div>

      
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
    

    function openTab(tabName) {

      const tabContents = document.getElementsByClassName("tab-content");
      for (let i = 0; i < tabContents.length; i++) {
        tabContents[i].classList.remove("active");
      }
      

      const tabButtons = document.getElementsByClassName("tab-btn");
      for (let i = 0; i < tabButtons.length; i++) {
        tabButtons[i].classList.remove("active");
      }
      
      document.getElementById(tabName).classList.add("active");
      event.currentTarget.classList.add("active");
    }
  </script>

</body>
</html>