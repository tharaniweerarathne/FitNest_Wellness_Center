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

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // create the trainer table if it doesn't exist (with category column)
    $pdo->exec("CREATE TABLE IF NOT EXISTS trainer (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        description TEXT,
        image_path VARCHAR(255),
        category VARCHAR(50) NOT NULL
    )");
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

$categories = ['yoga', 'personal', 'strength', 'cardio'];

// handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_trainer']) || isset($_POST['edit_trainer'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $description = $_POST['description'];
        $category = $_POST['category'];

        if (!in_array($category, $categories)) {
            die("Invalid category selected");
        }

        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'Trainer/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;
            

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array(strtolower($fileExtension), $allowedExtensions)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $imagePath = $targetPath;
                    
                    // delete old image if editing
                    if (isset($_POST['edit_trainer']) && !empty($_POST['old_image'])) {
                        @unlink($_POST['old_image']);
                    }
                }
            }
        } elseif (isset($_POST['edit_trainer']) && !empty($_POST['old_image'])) {
            $imagePath = $_POST['old_image'];
        }
        
        if (isset($_POST['add_trainer'])) {
            // ddd new trainer
            $stmt = $pdo->prepare("INSERT INTO trainer (name, email, description, image_path, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $description, $imagePath, $category]);
        } else {
            // update trainer
            $id = $_POST['id'];
            $stmt = $pdo->prepare("UPDATE trainer SET name=?, email=?, description=?, image_path=?, category=? WHERE id=?");
            $stmt->execute([$name, $email, $description, $imagePath, $category, $id]);
        }
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
} elseif (isset($_GET['delete'])) {
    // delete trainer
    $id = $_GET['delete'];
    
    // get image path before deletion
    $stmt = $pdo->prepare("SELECT image_path FROM trainer WHERE id=?");
    $stmt->execute([$id]);
    $imagePath = $stmt->fetchColumn();
    
    // delete record
    $stmt = $pdo->prepare("DELETE FROM trainer WHERE id=?");
    $stmt->execute([$id]);
    
    // delete image file
    if ($imagePath) {
        @unlink($imagePath);
    }
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// check if we're editing a trainer
$edit_mode = false;
$edit_data = [];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM trainer WHERE id=?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $edit_mode = true;
}

// get all trainers
$stmt = $pdo->query("SELECT * FROM trainer ORDER BY name");
$trainers = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
  <title>Admin Dashboard - Trainer Management</title>
  <link rel="stylesheet" href="Admin_dashboard_Css.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

</head>
<body>
  <div class="sidebar">
    <div class="logo">
        <img src="Images/logo_white.png" alt="FITNEST WELLNESS Logo" class="logo-img">
    </div>
    <ul>
    <li><a href="DashboardAdmin.php"><i class="ri-home-4-line"></i> Dashboard</a></li>
      <li><a href="ManageProfile_admin.php" ><i class="ri-user-settings-line"></i>Manage Profiles</a></li>
      <li><a href="InsertServices.php" ><i class="ri-add-circle-line"></i> Insert Services</a></li>
      <li><a href="addStaff.php" class="active"><i class="ri-user-add-line"></i>  Add Trainer</a></li>
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

    <div class="add_trainer_container">
      <h1>Trainer Management System</h1>

      <div class="add_trainer_form-container">
        <h2><?= $edit_mode ? 'Edit Trainer' : 'Add New Trainer' ?></h2>
        <form method="post" enctype="multipart/form-data">
          <?php if ($edit_mode): ?>
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
            <input type="hidden" name="old_image" value="<?= $edit_data['image_path'] ?? '' ?>">
          <?php endif; ?>
          <div class="add_trainer_form-group">
            <label for="name" class="add_trainer_label">Full Name:</label>
            <input type="text" id="name" name="name" class="add_trainer_input" required 
                  value="<?= $edit_mode ? htmlspecialchars($edit_data['name']) : '' ?>">
          </div>
          <div class="add_trainer_form-group">
            <label for="email" class="add_trainer_label">Email:</label>
            <input type="email" id="email" name="email" class="add_trainer_input" required
                  value="<?= $edit_mode ? htmlspecialchars($edit_data['email']) : '' ?>">
          </div>
          <div class="add_trainer_form-group">
            <label for="category" class="add_trainer_label">Category:</label>
            <select id="category" name="category" class="add_trainer_select" required>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat ?>" <?= ($edit_mode && $edit_data['category'] === $cat) ? 'selected' : '' ?>>
                  <?= ucfirst($cat) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="add_trainer_form-group">
            <label for="description" class="add_trainer_label">Description:</label>
            <textarea id="description" name="description" class="add_trainer_textarea"><?= $edit_mode ? htmlspecialchars($edit_data['description']) : '' ?></textarea>
          </div>
          <div class="add_trainer_form-group">
            <label for="image" class="add_trainer_label">Profile Image:</label>
            <input type="file" id="image" name="image" class="add_trainer_input" accept="image/*">
            <?php if ($edit_mode && !empty($edit_data['image_path'])): ?>
              <div class="add_trainer_image-preview">
                <p>Current Image:</p>
                <img src="<?= $edit_data['image_path'] ?>" alt="Current Trainer Image">
              </div>
            <?php endif; ?>
          </div>
          <button type="submit" name="<?= $edit_mode ? 'edit_trainer' : 'add_trainer' ?>" class="add_trainer_button">
            <?= $edit_mode ? 'Update Trainer' : 'Add Trainer' ?>
          </button>
          <?php if ($edit_mode): ?>
            <a href="<?= $_SERVER['PHP_SELF'] ?>" class="edit_trainer_button">Cancel</a>
          <?php endif; ?>
        </form>
      </div>

      <h2>Trainers</h2>
      <?php if (count($trainers) > 0): ?>
        <table class="add_trainer_table">
          <thead>
            <tr>
              <th>Image</th>
              <th>Name</th>
              <th>Email</th>
              <th>Category</th>
              <th>Description</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($trainers as $trainer): ?>
              <tr class="add_trainer_tr">
                <td>
                  <?php if (!empty($trainer['image_path'])): ?>
                    <img src="<?= $trainer['image_path'] ?>" alt="Trainer Image" class="add_trainer_image">
                  <?php else: ?>
                    No Image
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($trainer['name']) ?></td>
                <td><?= htmlspecialchars($trainer['email']) ?></td>
                <td>
                  <span class="add_trainer_category-badge add_trainer_category-<?= $trainer['category'] ?>">
                    <?= ucfirst($trainer['category']) ?>
                  </span>
                </td>
                <td class="add_trainer_description-cell" title="<?= htmlspecialchars($trainer['description']) ?>">
                  <?= htmlspecialchars($trainer['description']) ?>
                </td>
                <td class="add_trainer_action-buttons">
                  <a href="?edit=<?= $trainer['id'] ?>" class="add_trainer_button add_trainer_btn-edit">
                    <i class="ri-edit-line"></i> Edit
                  </a>
                  <a href="?delete=<?= $trainer['id'] ?>" class="add_trainer_button add_trainer_btn-delete" 
                    onclick="return confirm('Are you sure you want to delete this trainer?')">
                    <i class="ri-delete-bin-line"></i> Delete
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No trainers found. Add your first trainer above.</p>
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