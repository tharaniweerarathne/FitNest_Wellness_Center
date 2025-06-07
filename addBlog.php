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

$message = "";
$blog = null;
$isEditMode = false;

// handle blog deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $img_query = "SELECT image FROM blogs WHERE id = ?";
    $stmt = $conn->prepare($img_query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $image_path = $row['image'];
        // delete the image file if it exists
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // delete the blog record
    $delete_query = "DELETE FROM blogs WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Blog deleted successfully!";
    } else {
        $message = "Error deleting blog: " . $conn->error;
    }
}

// check if editing a blog
if (isset($_GET['edit'])) {
    $blog_id = $_GET['edit'];
    $isEditMode = true;

    $query = "SELECT * FROM blogs WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $blog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $blog = $result->fetch_assoc();
    } else {
        $message = "Blog not found!";
        $isEditMode = false;
    }
}

// handle blog submission or update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    $author = $_POST['author'];
    
    if (isset($_POST['blog_id'])) {
        // update existing blog
        $blog_id = $_POST['blog_id'];
        
        // Check if a new image was uploaded
        if ($_FILES["image"]["size"] > 0) {
            // handle image upload
            $target_dir = "blog/";
            
            // create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $target_file = $target_dir . time() . "." . $imageFileType;
            
            // check if image file is valid
            $valid_extensions = array("jpg", "jpeg", "png", "gif");
            if (in_array($imageFileType, $valid_extensions)) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    // get old image path
                    $img_query = "SELECT image FROM blogs WHERE id = ?";
                    $stmt = $conn->prepare($img_query);
                    $stmt->bind_param("i", $blog_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($row = $result->fetch_assoc()) {
                        $old_image = $row['image'];
                        // delete old image if it exists
                        if (file_exists($old_image)) {
                            unlink($old_image);
                        }
                    }
                    
                    // update blog data with new image
                    $sql = "UPDATE blogs SET title = ?, content = ?, image = ?, category = ?, author = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssssi", $title, $content, $target_file, $category, $author, $blog_id);
                } else {
                    $message = "Error uploading image.";
                }
            } else {
                $message = "Only JPG, JPEG, PNG & GIF files are allowed.";
            }
        } else {
            // update blog data without changing the image
            $sql = "UPDATE blogs SET title = ?, content = ?, category = ?, author = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $title, $content, $category, $author, $blog_id);
        }
        
        if (isset($stmt) && $stmt->execute()) {
            $message = "Blog updated successfully!";
            $isEditMode = false;
        } else if (isset($stmt)) {
            $message = "Error: " . $stmt->error;
        }
    } else {
        // add new blog
        $target_dir = "blog/";

        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $target_file = $target_dir . time() . "." . $imageFileType;
        
        $valid_extensions = array("jpg", "jpeg", "png", "gif");
        if (in_array($imageFileType, $valid_extensions)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // insert blog data into database
                $sql = "INSERT INTO blogs (title, content, image, category, author) 
                        VALUES (?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $title, $content, $target_file, $category, $author);
                
                if ($stmt->execute()) {
                    $message = "Blog added successfully!";
                } else {
                    $message = "Error: " . $stmt->error;
                }
            } else {
                $message = "Error uploading image.";
            }
        } else {
            $message = "Only JPG, JPEG, PNG & GIF files are allowed.";
        }
    }
}

$query = "SELECT * FROM blogs ORDER BY created_at DESC";
$blogs_result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php echo $isEditMode ? 'Edit Blog' : 'Add Blog'; ?></title>
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
      <li><a href="InsertServices.php" ><i class="ri-add-circle-line"></i> Insert Services</a></li>
      <li><a href="addStaff.php" ><i class="ri-user-add-line"></i>  Add Trainer</a></li>
      <li><a href="feedbackReply.php" ><i class="ri-feedback-line"></i> Feedback Reply</a></li>
      <li><a href="addBlog.php" class="active"><i class="ri-article-line"></i> Add Blog</a></li>
      <li><a href="EditProfile.php"><i class="ri-user-settings-line"></i> Edit Profile</a></li>
        </ul>
    </div>

    <div class="main-content">

        <header>
            <h1><?php echo $isEditMode ? 'Edit Blog' : 'Manage Blogs'; ?></h1>
            <div class="user-profile">
                <div class="profile-section">
                    <i class="ri-user-3-fill profile-icon" onclick="toggleProfileOptions(event)"></i>
                    <div class="profile-options" id="profileOptions">
                        <a href="EditProfile.php">Edit Profile</a>
                    </div>
                </div>
                <span>Welcome, <?php echo htmlspecialchars($name); ?></span>
                <button class="logout-btn" onclick="window.location.href='?logout=true'">
                    <i class="ri-logout-circle-r-line"></i> Logout
                </button>
            </div>
        </header>

        <?php if (!empty($message)): ?>
            <div class="message" style="margin: 20px;"><?php echo $message; ?></div>
        <?php endif; ?>


        <div class="blog-form">
            <h2 class="section-title"><?php echo $isEditMode ? 'Edit Blog' : 'Add New Blog'; ?></h2>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?><?php echo $isEditMode ? '?edit=' . $blog['id'] : ''; ?>" method="post" enctype="multipart/form-data">
                <?php if ($isEditMode): ?>
                    <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="title">Blog Title:</label>
                    <input type="text" id="title" name="title" value="<?php echo $isEditMode ? htmlspecialchars($blog['title']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="author">Author Name:</label>
                    <input type="text" id="author" name="author" value="<?php echo $isEditMode ? htmlspecialchars($blog['author']) : htmlspecialchars($name); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="workout_plans" <?php echo ($isEditMode && $blog['category'] == 'workout_plans') ? 'selected' : ''; ?>>Workout Plans</option>
                        <option value="healthy_meal_plans" <?php echo ($isEditMode && $blog['category'] == 'healthy_meal_plans') ? 'selected' : ''; ?>>Healthy Meal Plans</option>
                        <option value="healthy_recipes" <?php echo ($isEditMode && $blog['category'] == 'healthy_recipes') ? 'selected' : ''; ?>>Healthy Recipes</option>
                        <option value="success_stories" <?php echo ($isEditMode && $blog['category'] == 'success_stories') ? 'selected' : ''; ?>>Success Stories</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="image">Blog Image<?php echo $isEditMode ? ' (leave empty to keep current image)' : ''; ?>:</label>
                    <input type="file" id="image" name="image" onchange="previewImage(this)" <?php echo $isEditMode ? '' : 'required'; ?>>
                    <img id="imagePreview" src="#" alt="Image Preview" />
                    
                    <?php if ($isEditMode && isset($blog['image'])): ?>
                        <p>Current image:</p>
                        <img src="<?php echo htmlspecialchars($blog['image']); ?>" alt="Current Blog Image" class="current-image">
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="content">Blog Content:</label>
                    <textarea id="content" name="content" required><?php echo $isEditMode ? htmlspecialchars($blog['content']) : ''; ?></textarea>
                </div>
                
                <?php if ($isEditMode): ?>
                    <a href="AddBlog.php" class="cancel-btn">Cancel</a>
                <?php endif; ?>
                
                <button type="submit" class="submit-btn">
                    <?php echo $isEditMode ? 'Update Blog' : 'Add Blog'; ?>
                </button>
            </form>
        </div>

        <!-- Blog List -->
        <div class="blog-list">
            <h2 class="section-title">All Blogs</h2>
            
            <table class="blog-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Content</th>
                        <th>Author</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($blogs_result->num_rows > 0): ?>
                        <?php while ($row = $blogs_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="Blog Image" class="blog-image-small">
                                </td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td>
                                    <?php 
                                    $category = str_replace('_', ' ', $row['category']);
                                    echo ucwords($category);
                                    ?>
                                </td>

                                <td><?php echo htmlspecialchars($row['content']); ?></td>

                                <td><?php echo htmlspecialchars($row['author']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <a href="AddBlog.php?edit=<?php echo $row['id']; ?>" class="action-btn edit-btn">Edit</a>
                                    <a href="AddBlog.php?delete=<?php echo $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this blog?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No blogs found</td>
                        </tr>
                    <?php endif; ?>
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
        
        // image preview function
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>