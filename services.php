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


// fetch services 
$sql = "SELECT id, name, description, image_path, category, price, schedule FROM services";
$result = $conn->query($sql);

$services = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // convert schedule times to AM/PM format
        $schedule = $row["schedule"];
        $formattedSchedule = "";
        
        if ($schedule && is_string($schedule)) {
            $scheduleData = json_decode($schedule, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                foreach ($scheduleData as $day => $times) {
                    if (!empty($times['start']) && !empty($times['end'])) {
                        $startTime = date("g:i A", strtotime($times['start']));
                        $endTime = date("g:i A", strtotime($times['end']));
                        $formattedSchedule .= "$day: $startTime - $endTime<br>";
                    }
                }
                $schedule = $formattedSchedule ?: "Not scheduled";
            } else {
                $schedule = preg_replace_callback('/(\d{1,2}:\d{2})/', function($matches) {
                    return date("g:i A", strtotime($matches[0]));
                }, $schedule);
            }
        }
        
        $row['formatted_schedule'] = $schedule;
        $services[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Services</title>
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
      <li><a href="services.php" class="active"><i class="ri-calendar-check-line"></i> Classes</a></li>
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


    <section id="Classes" class="class-section">
    <h1>TRAINING PROGRAMS</h1>
    <div class="class-grid">
        <?php if (!empty($services)): ?>
            <?php foreach ($services as $service): ?>
                <div class="class-card">
                    <img src="<?= htmlspecialchars($service['image_path']) ?>" alt="<?= htmlspecialchars($service['name']) ?>" class="class-image">
                    <div class="class-details">
                        <h3 class="class-name"><?= htmlspecialchars($service['name']) ?></h3>
                        <span class="class-category"><?= htmlspecialchars($service['category']) ?></span>
                        <div class="class-schedule"><?= $service['formatted_schedule'] ?></div>
                        <p class="class-paragraph"><?= htmlspecialchars($service['description']) ?></p>
                        <div class="class-price">Rs <?= number_format($service['price'], 2) ?></div>
                        <div class="class-actions">
                            <a href="bookings.php?service_id=<?= $service['id'] ?>" class="book-btn">Book Now</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1 / -1; text-align: center;">No services found</p>
        <?php endif; ?>
    </div>
</section>



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
