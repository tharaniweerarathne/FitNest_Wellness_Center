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

// adding new staff
if (isset($_POST['add_staff'])) {
    $staff_name = mysqli_real_escape_string($conn, $_POST['staff_name']);
    $staff_email = mysqli_real_escape_string($conn, $_POST['staff_email']);
    $staff_password = mysqli_real_escape_string($conn, $_POST['staff_password']);
    $staff_phone = mysqli_real_escape_string($conn, $_POST['staff_phone']);
    $staff_gender = mysqli_real_escape_string($conn, $_POST['staff_gender']);

    // inserting new staff details 
    $insert_sql = "INSERT INTO users (email, password, name, contactno, gender, role) 
                   VALUES ('$staff_email', '$staff_password', '$staff_name', '$staff_phone', '$staff_gender', 'Staff')";
    
    if (mysqli_query($conn, $insert_sql)) {
        echo "<script>alert('Staff added successfully!'); window.location='ManageProfile_admin.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error adding staff. Please try again.'); window.location='ManageProfile_admin.php';</script>";
        exit();
    }
}

// editing staff
if (isset($_POST['edit_staff'])) {
    $original_email = mysqli_real_escape_string($conn, $_POST['original_email']);
    $staff_name = mysqli_real_escape_string($conn, $_POST['staff_name']);
    $staff_email = mysqli_real_escape_string($conn, $_POST['staff_email']);
    $staff_phone = mysqli_real_escape_string($conn, $_POST['staff_phone']);
    $staff_gender = mysqli_real_escape_string($conn, $_POST['staff_gender']);

    $update_sql = "UPDATE users SET 
                  name = '$staff_name',
                  email = '$staff_email',
                  contactno = '$staff_phone',
                  gender = '$staff_gender'
                  WHERE email = '$original_email' AND role = 'Staff'";
    
    if (mysqli_query($conn, $update_sql)) {
        echo "<script>alert('Staff updated successfully!'); window.location='ManageProfile_admin.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error updating staff. Please try again.'); window.location='ManageProfile_admin.php';</script>";
        exit();
    }
}

// deleting staff
if (isset($_GET['delete_staff'])) {
    $staff_email = mysqli_real_escape_string($conn, $_GET['delete_staff']);
    $delete_sql = "DELETE FROM users WHERE email = '$staff_email' AND role = 'Staff'";
    if (mysqli_query($conn, $delete_sql)) {
        echo "<script>alert('Staff deleted successfully!'); window.location='ManageProfile_admin.php';</script>";
        exit();
    }
}

// deleting customers
if (isset($_GET['delete_customer'])) {
    $customer_email = mysqli_real_escape_string($conn, $_GET['delete_customer']);
    $delete_sql = "DELETE FROM users WHERE email = '$customer_email' AND role = 'Customer'";
    if (mysqli_query($conn, $delete_sql)) {
        echo "<script>alert('Customer deleted successfully!'); window.location='ManageProfile_admin.php';</script>";
        exit();
    }
}

$editing_staff = null;
if (isset($_GET['edit_staff'])) {
    $staff_email = mysqli_real_escape_string($conn, $_GET['edit_staff']);
    $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$staff_email' AND role = 'Staff'");
    $editing_staff = mysqli_fetch_assoc($result);
}

// fetch only staff users
$staff_result = mysqli_query($conn, "SELECT * FROM users WHERE role = 'Staff'");

// fetch customers 
$customer_result = mysqli_query($conn, "SELECT name, email, contactno, gender FROM users WHERE role = 'Customer'");

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
  <title>Manage Profiles</title>
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
      <li><a href="ManageProfile_admin.php" class="active"><i class="ri-user-settings-line"></i>Manage Profiles</a></li>
      <li><a href="InsertServices.php"><i class="ri-add-circle-line"></i> Insert Services</a></li>
      <li><a href="addStaff.php"><i class="ri-user-add-line"></i>  Add Trainer</a></li>
      <li><a href="feedbackReply.php"><i class="ri-feedback-line"></i> Feedback Reply</a></li>
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

    <div class="manageProfile">
        <?php if ($editing_staff): ?>
            <h2>Edit Staff Member</h2>
            <form method="POST" class="Add_staff">
                <input type="hidden" name="original_email" value="<?php echo htmlspecialchars($editing_staff['email']); ?>">
                <input type="text" name="staff_name" placeholder="Enter Name" value="<?php echo htmlspecialchars($editing_staff['name']); ?>" required>
                <input type="email" name="staff_email" placeholder="Enter Email" value="<?php echo htmlspecialchars($editing_staff['email']); ?>" required>
                <input type="text" name="staff_phone" placeholder="Enter Phone No" value="<?php echo htmlspecialchars($editing_staff['contactno']); ?>" required>
                <select name="staff_gender">
                    <option value="male" <?php echo ($editing_staff['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                    <option value="female" <?php echo ($editing_staff['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                </select>
                <button type="submit" name="edit_staff">Update Staff</button>
                <button type="button" onclick="window.location.href='ManageProfile_admin.php'" style="background-color: #6c757d; margin-left: 10px;">Cancel</button>
            </form>
        <?php else: ?>
            <h2>Add Staff</h2>
            <form method="POST" class="Add_staff">
                <input type="text" name="staff_name" placeholder="Enter Name" required>
                <input type="email" name="staff_email" placeholder="Enter Email" required>
                <input type="password" name="staff_password" placeholder="Enter Password" required>
                <input type="text" name="staff_phone" placeholder="Enter Phone No" required>
                <select name="staff_gender">
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
                <button type="submit" name="add_staff">Add Staff</button>
            </form>
        <?php endif; ?>

        <h2>Staff Members</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone No</th>
                    <th>Gender</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                mysqli_data_seek($staff_result, 0); 
                while ($staff = mysqli_fetch_assoc($staff_result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($staff['name']); ?></td>
                        <td><?php echo htmlspecialchars($staff['email']); ?></td>
                        <td><?php echo htmlspecialchars($staff['contactno']); ?></td>
                        <td><?php echo htmlspecialchars($staff['gender']); ?></td>
                        <td>
                            <a href="?edit_staff=<?php echo urlencode($staff['email']); ?>" class="edit">
                                <i class="ri-edit-box-line"></i><span class="edit-text">Edit</span>
                            </a>
                            <a href="?delete_staff=<?php echo urlencode($staff['email']); ?>" class="delete-btn">
                                <i class="ri-delete-bin-line"></i><span class="delete-text">Delete</span>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <h2>Customers</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone No</th>
                    <th>Gender</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                mysqli_data_seek($customer_result, 0);
                while ($customer = mysqli_fetch_assoc($customer_result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td><?php echo htmlspecialchars($customer['contactno']); ?></td>
                        <td><?php echo htmlspecialchars($customer['gender']); ?></td>
                        <td>
                            <a href="?delete_customer=<?php echo urlencode($customer['email']); ?>" class="delete-btn">
                                <i class="ri-delete-bin-line"></i><span class="delete-text">Delete</span>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
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