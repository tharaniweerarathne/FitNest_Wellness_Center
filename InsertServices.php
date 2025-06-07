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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Insert Services</title>
  <link rel="stylesheet" href="Admin_dashboard_Css.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

  <div class="sidebar">
    <div class="logo">
        <img src="Images/logo_white.png" alt="FITNEST WELLNESS Logo" class="logo-img">
    </div>
    <ul>
    <li><a href="DashboardAdmin.php"><i class="ri-home-4-line"></i> Dashboard</a></li>
      <li><a href="ManageProfile_admin.php" ><i class="ri-user-settings-line"></i>Manage Profiles</a></li>
      <li><a href="InsertServices.php" class="active"><i class="ri-add-circle-line"></i> Insert Services</a></li>
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
        <span>Welcome, <?php echo htmlspecialchars($name); ?></span>

        <button class="logout-btn" onclick="window.location.href='?logout=true'">
        <i class="ri-logout-circle-r-line"></i>
        Logout
    </button>

      </div>
    </header>

    <div class="services-section">
      <div class="section-header">
        <h2>Add New Service</h2>
        <button class="btn-add" id="addServiceBtn"><i class="ri-add-line"></i> Add Service</button>
      </div>

      <div class="service-form" id="serviceForm">
        <form action="add_service.php" method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label for="serviceName">Service Name</label>
            <input type="text" id="serviceName" name="serviceName" required>
          </div>
          <div class="form-group">
            <label for="serviceDescription">Description</label>
            <textarea id="serviceDescription" name="serviceDescription" rows="4" required></textarea>
          </div>

          <div class="form-group">
            <label for="serviceImage">Service Image</label>
            <input type="file" id="serviceImage" name="serviceImage" accept="image/*">
            <div class="image-preview" id="imagePreview">
              <img src="" alt="Image Preview" class="preview-image" id="previewImage">
              <span class="default-text">No image selected</span>
            </div>
          </div>

          <div class="form-group">
            <label for="serviceCategory">Category</label>
            <select id="serviceCategory" name="serviceCategory" required>
              <option value="">Select Category</option>
              <option value="Cardio">Cardio Training</option>
              <option value="Strength">Strength Training</option>
              <option value="Personal">Personal Training</option>
              <option value="Yoga">Yoga</option>
            </select>
          </div>

          <div class="form-group">
            <label for="servicePrice">Price (Rs)</label>
            <input type="number" id="servicePrice" name="servicePrice" min="0" step="0.01" required>
          </div>
          <div class="form-group">
            <label>Schedule</label>
            <div class="schedule-container">
              <div class="schedule-day">
                <input type="checkbox" id="monday" name="days[]" value="Monday">
                <label for="monday">Monday</label>
                <input type="time" name="mondayStart">
                <span>to</span>
                <input type="time" name="mondayEnd">
              </div>
              <div class="schedule-day">
                <input type="checkbox" id="tuesday" name="days[]" value="Tuesday">
                <label for="tuesday">Tuesday</label>
                <input type="time" name="tuesdayStart">
                <span>to</span>
                <input type="time" name="tuesdayEnd">
              </div>
              <div class="schedule-day">
                <input type="checkbox" id="wednesday" name="days[]" value="Wednesday">
                <label for="wednesday">Wednesday</label>
                <input type="time" name="wednesdayStart">
                <span>to</span>
                <input type="time" name="wednesdayEnd">
              </div>
              <div class="schedule-day">
                <input type="checkbox" id="thursday" name="days[]" value="Thursday">
                <label for="thursday">Thursday</label>
                <input type="time" name="thursdayStart">
                <span>to</span>
                <input type="time" name="thursdayEnd">
              </div>
              <div class="schedule-day">
                <input type="checkbox" id="friday" name="days[]" value="Friday">
                <label for="friday">Friday</label>
                <input type="time" name="fridayStart">
                <span>to</span>
                <input type="time" name="fridayEnd">
              </div>
              <div class="schedule-day">
                <input type="checkbox" id="saturday" name="days[]" value="Saturday">
                <label for="saturday">Saturday</label>
                <input type="time" name="saturdayStart">
                <span>to</span>
                <input type="time" name="saturdayEnd">
              </div>
              <div class="schedule-day">
                <input type="checkbox" id="sunday" name="days[]" value="Sunday">
                <label for="sunday">Sunday</label>
                <input type="time" name="sundayStart">
                <span>to</span>
                <input type="time" name="sundayEnd">
              </div>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-save">Save Service</button>
            <button type="button" class="btn-cancel" id="cancelServiceBtn">Cancel</button>
          </div>
        </form>
      </div>


      <div class="services-list">
        <h2>Current Services</h2>
        <div class="service-table-container">
          <table class="service-table">
            <thead>
              <tr>
                <th>Image</th>
                <th>Service Name</th>
                <th>Description </th>
                <th>Category</th>
                <th>Price</th>
                <th>Schedule</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php include 'get_services.php'; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script>

    // toggle service form visibility
    document.getElementById('addServiceBtn').addEventListener('click', function() {
      document.getElementById('serviceForm').style.display = 'block';
    });
    
    document.getElementById('cancelServiceBtn').addEventListener('click', function() {
      document.getElementById('serviceForm').style.display = 'none';
      document.getElementById('previewImage').src = '';
      document.querySelector('.default-text').style.display = 'block';
      document.getElementById('serviceImage').value = ''; // Clear file input
    });

    // Image preview functionality
    document.getElementById('serviceImage').addEventListener('change', function(event) {
      const file = event.target.files[0];
      const preview = document.getElementById('previewImage');
      const defaultText = document.querySelector('.default-text');
      const imagePreview = document.getElementById('imagePreview');
      
      if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
          preview.src = e.target.result;
          preview.style.display = 'block';
          defaultText.style.display = 'none';
          imagePreview.classList.add('has-image');
        }
        
        reader.readAsDataURL(file);
      } else {
        preview.src = '';
        preview.style.display = 'none';
        defaultText.style.display = 'block';
        imagePreview.classList.remove('has-image');
      }

      
    });


  
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