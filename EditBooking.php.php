<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: SignIn.php");
    exit();
}

$name = $_SESSION['name'];
$email = $_SESSION['email'];
$booking_id = $_GET['id'] ?? null;

if (!$booking_id) {
    header("Location: customerDashboard.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitnestwellness";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// get booking details
$booking = null;
$stmt = $conn->prepare("
    SELECT b.*, s.name as service_name, s.category, s.schedule as service_schedule, 
           t.name as trainer_name, t.id as trainer_id
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN trainer t ON b.trainer_id = t.id
    WHERE b.id = ? AND b.email = ?
");
$stmt->bind_param("is", $booking_id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: customerDashboard.php");
    exit();
}

$booking = $result->fetch_assoc();

// get available trainers for this service category
$trainers = [];
$trainer_stmt = $conn->prepare("SELECT * FROM trainer WHERE category = ?");
$trainer_stmt->bind_param("s", $booking['category']);
$trainer_stmt->execute();
$trainer_result = $trainer_stmt->get_result();

while ($row = $trainer_result->fetch_assoc()) {
    $trainers[] = $row;
}

// get available time slots for this service
$service_schedule = explode(',', $booking['service_schedule']);

// process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trainer_id = $_POST['trainer_id'];
    $schedule = $_POST['schedule'];
    
    $update_stmt = $conn->prepare("
        UPDATE bookings 
        SET trainer_id = ?, schedule = ?
        WHERE id = ? AND email = ?
    ");
    $update_stmt->bind_param("isis", $trainer_id, $schedule, $booking_id, $email);
    
    if ($update_stmt->execute()) {
        $success_message = "Booking updated successfully!";
        // refresh booking data
        $booking['trainer_id'] = $trainer_id;
        $booking['schedule'] = $schedule;
        $booking['trainer_name'] = array_values(array_filter($trainers, function($t) use ($trainer_id) {
            return $t['id'] == $trainer_id;
        }))[0]['name'];
    } else {
        $error_message = "Error updating booking. Please try again.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Booking</title>
  <link rel="stylesheet" href="Customer_dashbard_css.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
  <style>
    .edit-booking-container {
      max-width: 800px;
      margin: 2rem auto;
      padding: 2rem;
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .edit-booking-container h1 {
      color: #2c3e50;
      margin-bottom: 1.5rem;
      font-size: 1.8rem;
      text-align: center;
      position: relative;
      padding-bottom: 0.5rem;
    }

    .edit-booking-container h1::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 100px;
      height: 3px;
      background: #D91656;
    }

    .booking-info {
      margin-bottom: 2rem;
      padding: 1.5rem;
      background: #f8f9fa;
      border-radius: 8px;
    }

    .booking-info p {
      margin-bottom: 0.5rem;
      color: #333;
    }

    .booking-info strong {
      color: #2c3e50;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #2c3e50;
    }

    select {
      width: 100%;
      padding: 0.8rem 1rem;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    select:focus {
      outline: none;
      border-color: #D91656;
      box-shadow: 0 0 0 3px rgba(217, 22, 86, 0.1);
    }

    .form-actions {
      display: flex;
      gap: 1rem;
      margin-top: 2rem;
    }

    .btn {
      padding: 0.8rem 1.5rem;
      border-radius: 6px;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.2s ease;
      text-decoration: none;
      font-size: 0.95rem;
      border: none;
      cursor: pointer;
    }

    .btn i {
      font-size: 1.1rem;
    }

    .save-btn {
      background: #D91656;
      color: white;
    }

    .save-btn:hover {
      background:rgb(115, 14, 49);
      transform: translateY(-2px);
    }

    .cancel-btn {
      background: #95a5a6;
      color: white;
    }

    .cancel-btn:hover {
      background: #7f8c8d;
      transform: translateY(-2px);
    }

    .message {
      padding: 1rem;
      border-radius: 6px;
      margin-bottom: 1.5rem;
      font-weight: 500;
    }

    .success-message {
      background-color: #d4edda;
      color: #155724;
      border-left: 4px solid #28a745;
    }

    .error-message {
      background-color: #f8d7da;
      color: #721c24;
      border-left: 4px solid #dc3545;
    }

    @media (max-width: 768px) {
      .edit-booking-container {
        padding: 1.5rem;
        margin: 1rem;
      }
      
      .form-actions {
        flex-direction: column;
      }
      
      .btn {
        width: 100%;
        justify-content: center;
      }
    }
  </style>
</head>
<body>

  <div class="sidebar">
    <div class="logo">
      <img src="Images/logo_white.png" alt="FITNEST WELLNESS Logo" class="logo-img">
    </div>
    <ul>
      <li><a href="customerDashboard.php"><i class="ri-home-4-line"></i> Dashboard</a></li>
      <li><a href="services.php"><i class="ri-calendar-check-line"></i> Classes</a></li>
      <li><a href="membership.php"><i class="ri-id-card-line"></i> Membership</a></li>
      <li><a href="customerFeedback.php"><i class="ri-feedback-line"></i> Feedback</a></li>
      <li><a href="EditProfile.php"><i class="ri-user-settings-line"></i> Edit Profile</a></li>
    </ul>
  </div>


  <div class="main-content">

    <header>
      <h1>Edit Booking</h1>
      <div class="user-profile">
        <div class="profile-section">
          <i class="ri-user-3-fill profile-icon" onclick="toggleProfileOptions(event)"></i>
          <div class="profile-options" id="profileOptions">
            <a href="EditProfile.php">Edit Profile</a>
          </div>
        </div>
        <span class="user-name">Welcome, <?php echo htmlspecialchars($name); ?></span>
        <button class="logout-btn" onclick="window.location.href='?logout=true'">
          <i class="ri-logout-circle-r-line"></i> Logout
        </button>
      </div>
    </header>

    <div class="edit-booking-container">
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

      <div class="booking-info">
        <p><strong>Class:</strong> <?php echo htmlspecialchars($booking['service_name']); ?></p>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($booking['category']); ?></p>
        <p><strong>Current Status:</strong> <?php echo htmlspecialchars($booking['status']); ?></p>
      </div>

      <form method="post">
        <div class="form-group">
          <label for="trainer_id">Trainer</label>
          <select id="trainer_id" name="trainer_id" required>
            <?php foreach ($trainers as $trainer): ?>
              <option value="<?php echo $trainer['id']; ?>" 
                <?php echo $trainer['id'] == $booking['trainer_id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($trainer['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="schedule">Schedule</label>
          <select id="schedule" name="schedule" required>
            <?php foreach ($service_schedule as $slot): 
              $slot = trim($slot);
              if (!empty($slot)): ?>
                <option value="<?php echo htmlspecialchars($slot); ?>" 
                  <?php echo $slot == $booking['schedule'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($slot); ?>
                </option>
              <?php endif;
            endforeach; ?>
          </select>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn save-btn">
            <i class="ri-save-line"></i> Save Changes
          </button>
          <a href="customerDashboard.php" class="btn cancel-btn">
            <i class="ri-close-line"></i> Cancel
          </a>
        </div>
      </form>
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