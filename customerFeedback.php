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

// feedback form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_id'])) {
        // delete
        $delete_id = $_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        
        if ($stmt->execute()) {
            $success = "Feedback deleted successfully!";
        } else {
            $error = "Error deleting feedback: " . $stmt->error;
        }
        $stmt->close();
    } elseif (isset($_POST['edit_id'])) {
        // edit
        $edit_id = $_POST['edit_id'];
        $rating = $_POST['rating'];
        $message = $_POST['message'];
        
        $stmt = $conn->prepare("UPDATE feedback SET rating = ?, message = ? WHERE id = ?");
        $stmt->bind_param("isi", $rating, $message, $edit_id);
        
        if ($stmt->execute()) {
            $success = "Feedback updated successfully!";
        } else {
            $error = "Error updating feedback: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // new feedback
        $rating = $_POST['rating'];
        $message = $_POST['message'];
        
        // insert into database
        $stmt = $conn->prepare("INSERT INTO feedback (name, email, rating, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $name, $email, $rating, $message);
        
        if ($stmt->execute()) {
            $success = "Thank you for your feedback!";
        } else {
            $error = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

// fetch existing feedback
$feedback_query = "SELECT id, name, email, rating, message FROM feedback WHERE email = ? ORDER BY id DESC";
$stmt = $conn->prepare($feedback_query);
$stmt->bind_param("s", $email);
$stmt->execute();
$feedback_result = $stmt->get_result();
$stmt->close();

//check if editing a specific feedback
$editing = false;
$edit_feedback = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT id, name, email, rating, message FROM feedback WHERE id = ? AND email = ?");
    $stmt->bind_param("is", $edit_id, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $editing = true;
        $edit_feedback = $result->fetch_assoc();
    }
    $stmt->close();
}


// fetch all replies
$replies = array();
$replies_query = "SELECT id, feedback_id, reply, replied_by, replied_at FROM feedback_replies ORDER BY replied_at";
$replies_result = $conn->query($replies_query);
while ($reply = $replies_result->fetch_assoc()) {
    if (!isset($replies[$reply['feedback_id']])) {
        $replies[$reply['feedback_id']] = array();
    }
    $replies[$reply['feedback_id']][] = $reply;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Feedback</title>
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
      <li><a href="membership.php" ><i class="ri-id-card-line"></i> Membership</a></li>
      <li><a href="customerFeedback.php" class="active"><i class="ri-feedback-line"></i> Feedback</a></li>
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

    <div class="review-form">
        <h2 class="feedback-title"><?php echo $editing ? 'Edit Your Feedback' : 'Share Your Experience'; ?></h2>
        
        <?php if (isset($success)): ?>
            <div class="review-alert review-alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="review-alert review-alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <?php if ($editing): ?>
                <input type="hidden" name="edit_id" value="<?php echo $edit_feedback['id']; ?>">
            <?php endif; ?>
            
            <div class="review-form-group">
                <label for="name" class="review-label">Name</label>
                <input type="text" id="name" name="name" class="review-input" 
                       value="<?php echo htmlspecialchars($name); ?>" readonly>
            </div>
            
            <div class="review-form-group">
                <label for="email" class="review-label">Email</label>
                <input type="email" id="email" name="email" class="review-input" 
                       value="<?php echo htmlspecialchars($email); ?>" readonly>
            </div>
            
            <div class="review-form-group">
                <label class="review-label">Rating</label>
                <div class="review-rating-container">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" 
                               class="review-rating-input" <?php echo ($editing && $edit_feedback['rating'] == $i) ? 'checked' : ''; ?> 
                               <?php echo ($i == 1 && !$editing) ? 'required' : ''; ?>>
                        <label for="star<?php echo $i; ?>" class="review-rating-label"><i class="ri-star-fill"></i></label>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div class="review-form-group">
                <label for="message" class="review-label">Your Feedback</label>
                <textarea id="message" name="message" class="review-textarea" required><?php 
                    echo $editing ? htmlspecialchars($edit_feedback['message']) : ''; 
                ?></textarea>
            </div>
            
            <button type="submit" class="review-btn <?php echo $editing ? 'review-btn-warning' : ''; ?>">
                <i class="ri-send-plane-fill"></i> <?php echo $editing ? 'Update Feedback' : 'Submit Feedback'; ?>
            </button>
            
            <?php if ($editing): ?>
                <a href="customerFeedback.php" class="review-btn review-btn-danger">
                    <i class="ri-close-line"></i> Cancel
                </a>
            <?php endif; ?>
        </form>
    </div>
    
<h2 class="feedback-title">Your Feedback History</h2>

<div class="review-list">
    <?php if ($feedback_result && $feedback_result->num_rows > 0): ?>
        <?php while($row = $feedback_result->fetch_assoc()): ?>
            <div class="review-card">
                <div class="review-header">
                    <div class="review-name"><?php echo htmlspecialchars($row['name']); ?></div>
                    <div class="review-actions">
                        <a href="customerFeedback.php?edit=<?php echo $row['id']; ?>" class="review-action-btn review-edit-btn" title="Edit">
                            <i class="ri-edit-line"></i>
                        </a>
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="review-action-btn review-delete-btn" title="Delete" 
                                    onclick="return confirm('Are you sure you want to delete this feedback?');">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="review-rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="ri-star-fill" style="color: <?php echo $i <= $row['rating'] ? '#ffc107' : '#ddd'; ?>"></i>
                    <?php endfor; ?>
                    <span>(<?php echo $row['rating']; ?>/5)</span>
                </div>
                
                <div class="review-message">
                    <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                </div>
                
                <!-- staff Replies Section -->
                <?php if (isset($replies[$row['id']])): ?>
                    <div class="staff-replies">
                        <h4 class="replies-title">Staff Responses</h4>
                        <?php foreach ($replies[$row['id']] as $reply): ?>
                            <div class="staff-reply">
                                <div class="reply-header">

                                    <span class="reply-date"><?php echo date('M j, Y g:i a', strtotime($reply['replied_at'])); ?></span>
                                </div>
                                <div class="reply-message">
                                    <?php echo nl2br(htmlspecialchars($reply['reply'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="review-empty">
            <i class="ri-emotion-sad-line" style="font-size: 50px; color: #ddd;"></i>
            <p>No feedback yet. Be the first to share your experience!</p>
        </div>
    <?php endif; ?>
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
<?php

$conn->close();
?>