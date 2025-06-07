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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Membership Join</title>
  <link rel="stylesheet" href="Customer_dashbard_css.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
</head>

<body>

  <div class="sidebar">
    <div class="logo">
      <img src="Images/logo_white.png" alt="FITNEST WELLNESS Logo" class="logo-img">
    </div>
    <ul>
    <li><a href="customerDashboard.php" ><i class="ri-home-4-line"></i> Dashboard</a></li>
      <li><a href="services.php" ><i class="ri-calendar-check-line"></i> Classes</a></li>
      <li><a href="membership.php" class="active"><i class="ri-id-card-line"></i> Membership</a></li>
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
        <span>Welcome, <?php echo htmlspecialchars($name); ?></span>

        <button class="logout-btn" onclick="window.location.href='?logout=true'">
        <i class="ri-logout-circle-r-line"></i>
        Logout
    </button>

      </div>
    </header>

    <div class="dashboard-container">

        <h2>Upgrade Your Membership</h2>
        
        <div class="membership-plans">

            <div class="membership-plan">
                <h3>Basic Membership</h3>
                <div class="membership-price">Rs. 6000<span>/month</span></div>
                <ul class="membership-features">
                    <li>Access to Gym Equipment</li>
                    <li>One Group Class Weekly</li>
                    <li>Locker Facility</li>
                </ul>
                <button class="membership-cta" onclick="window.location='join-membership.php?plan=basic'">
                    Join Now
                </button>
            </div>

            <div class="membership-plan">
                <h3>Premium Membership</h3>
                <div class="membership-price">Rs. 10,000<span>/month</span></div>
                <ul class="membership-features">
                    <li>Unlimited Gym Access</li>
                    <li>Personal Trainer Sessions</li>
                    <li>Sauna & Steam Room</li>
                </ul>
                <button class="membership-cta" onclick="window.location='join-membership.php?plan=premium'">
                    Join Now
                </button>
            </div>

            <div class="membership-plan">
                <h3>Elite Package</h3>
                <div class="membership-price">Rs. 15,000<span>/month</span></div>
                <ul class="membership-features">
                    <li>All Premium Benefits</li>
                    <li>Customized Diet Plan</li>
                    <li>24/7 Support & Consultations</li>
                </ul>
                <button class="membership-cta" onclick="window.location='join-membership.php?plan=elite'">
                    Join Now
                </button>
            </div>
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
  </script>

</body>
</html>
