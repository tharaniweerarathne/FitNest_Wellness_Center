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

// feedback reply submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_reply'])) {
    $feedback_id = $_POST['feedback_id'];
    $reply = $_POST['reply'];
    $replied_by = $name;
    
    $stmt = $conn->prepare("INSERT INTO feedback_replies (feedback_id, reply, replied_by) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $feedback_id, $reply, $replied_by);
    
    if ($stmt->execute()) {
        $success = "Reply submitted successfully!";
    } else {
        $error = "Error submitting reply: " . $stmt->error;
    }
    $stmt->close();
}

// reply deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_reply'])) {
    $reply_id = $_POST['reply_id'];
    
    $stmt = $conn->prepare("DELETE FROM feedback_replies WHERE id = ?");
    $stmt->bind_param("i", $reply_id);
    
    if ($stmt->execute()) {
        $success = "Reply deleted successfully!";
    } else {
        $error = "Error deleting reply: " . $stmt->error;
    }
    $stmt->close();
}

// reply editing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_reply'])) {
    $reply_id = $_POST['reply_id'];
    $updated_reply = $_POST['updated_reply'];
    
    $stmt = $conn->prepare("UPDATE feedback_replies SET reply = ? WHERE id = ?");
    $stmt->bind_param("si", $updated_reply, $reply_id);
    
    if ($stmt->execute()) {
        $success = "Reply updated successfully!";
    } else {
        $error = "Error updating reply: " . $stmt->error;
    }
    $stmt->close();
}

// fetch all feedback
$feedback_query = "SELECT id, email, message FROM feedback ORDER BY id DESC";
$feedback_result = $conn->query($feedback_query);

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
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Feedback Reply</title>
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
      <li><a href="InsertServices.php"><i class="ri-add-circle-line"></i> Insert Services</a></li>
      <li><a href="addStaff.php"><i class="ri-user-add-line"></i>  Add Trainer</a></li>
      <li><a href="feedbackReply.php" class="active"><i class="ri-feedback-line"></i> Feedback Reply</a></li>
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

    <div class="feedback-container">
      <h2 class="feedback-title"><i class="ri-feedback-line"></i> Customer Feedback</h2>
      
      <?php if (isset($success)): ?>
        <div class="alert alert-success">
          <i class="ri-check-line"></i> <?php echo $success; ?>
        </div>
      <?php endif; ?>
      
      <?php if (isset($error)): ?>
        <div class="alert alert-danger">
          <i class="ri-error-warning-line"></i> <?php echo $error; ?>
        </div>
      <?php endif; ?>
      
      <?php if ($feedback_result->num_rows > 0): ?>
        <?php $feedback_result->data_seek(0); ?>
        <?php while ($feedback = $feedback_result->fetch_assoc()): ?>
          <div class="feedback-item">
            <p><strong>Feedback:</strong> <?= htmlspecialchars($feedback['message']) ?></p>
            <p><small><i class="ri-user-line"></i> Submitted by: <?= $feedback['email'] ?></small></p>
            
            <form method="POST" action="" class="reply-form">
              <input type="hidden" name="feedback_id" value="<?= $feedback['id'] ?>">
              <textarea name="reply" placeholder="Enter your professional reply here..." required></textarea>
              <button type="submit" name="submit_reply">
                <i class="ri-send-plane-line"></i> Submit Reply
              </button>
            </form>
            
            <?php if (!empty($replies[$feedback['id']])): ?>
              <div class="replies">
                <h3><i class="ri-question-answer-line"></i> Admin Replies:</h3>
                <?php foreach ($replies[$feedback['id']] as $reply): ?>
                  <div class="reply-item" id="reply-<?= $reply['id'] ?>">
                    <p><?= htmlspecialchars($reply['reply']) ?></p>
                    <p><small><i class="ri-user-3-line"></i> Replied by: <?= $reply['replied_by'] ?> on <?= date('M j, Y g:i A', strtotime($reply['replied_at'])) ?></small></p>
                    
                    <div class="reply-actions">
                      <button type="button" class="edit-reply-btn" title="Edit Reply" onclick="showEditForm(<?= $reply['id'] ?>)">
                        <i class="ri-edit-line"></i>
                      </button>
                      
                      <button type="button" class="delete-reply-btn" title="Delete Reply" onclick="showDeleteConfirmation(<?= $reply['id'] ?>)">
                        <i class="ri-delete-bin-line"></i>
                      </button>
                    </div>
                    

                    <div class="edit-form" id="edit-form-<?= $reply['id'] ?>">
                      <form method="POST" action="">
                        <input type="hidden" name="reply_id" value="<?= $reply['id'] ?>">
                        <textarea name="updated_reply" required><?= htmlspecialchars($reply['reply']) ?></textarea>
                        <div class="edit-form-buttons">
                          <button type="submit" name="edit_reply" class="save-btn">
                            <i class="ri-check-line"></i> Save
                          </button>
                          <button type="button" class="cancel-btn" onclick="hideEditForm(<?= $reply['id'] ?>)">
                            <i class="ri-close-line"></i> Cancel
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="no-feedback">
          <i class="ri-emotion-sad-line"></i>
          <p>No feedback submitted yet.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
  
  <!-- delete confirmation modal -->
  <div class="modal" id="delete-confirmation-modal">
    <div class="modal-content">
      <h3>Confirm Deletion</h3>
      <p>Are you sure you want to delete this reply? This action cannot be undone.</p>
      <div class="modal-buttons">
        <form method="POST" action="" id="delete-form">
          <input type="hidden" name="reply_id" id="delete-reply-id" value="">
          <button type="submit" name="delete_reply" class="modal-confirm">Delete</button>
        </form>
        <button class="modal-cancel" onclick="hideDeleteConfirmation()">Cancel</button>
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
    
    // handle edit form display
    function showEditForm(replyId) {
      const editForm = document.getElementById(`edit-form-${replyId}`);
      editForm.classList.add('active');
    }
    
    function hideEditForm(replyId) {
      const editForm = document.getElementById(`edit-form-${replyId}`);
      editForm.classList.remove('active');
    }
    
    // handle delete confirmation modal
    function showDeleteConfirmation(replyId) {
      const modal = document.getElementById('delete-confirmation-modal');
      document.getElementById('delete-reply-id').value = replyId;
      modal.classList.add('active');
    }
    
    function hideDeleteConfirmation() {
      const modal = document.getElementById('delete-confirmation-modal');
      modal.classList.remove('active');
    }
    
    // close alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
      const alerts = document.querySelectorAll('.alert');
      if (alerts.length > 0) {
        setTimeout(() => {
          alerts.forEach(alert => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            alert.style.transition = 'opacity 0.5s, transform 0.5s';
            setTimeout(() => {
              alert.style.display = 'none';
            }, 500);
          });
        }, 5000);
      }
    });
  </script>

</body>
</html>