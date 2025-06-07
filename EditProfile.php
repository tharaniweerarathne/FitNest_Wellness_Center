<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "fitnestwellness");

if (!$conn) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// fetch user details
$email = $_SESSION['email']; 
$sql = "SELECT name, contactno, email FROM users WHERE email='$email'";
$result = mysqli_query($conn, $sql);

if ($result->num_rows > 0) {
    $row = mysqli_fetch_assoc($result);
    $name = $row['name'];
    $phoneNo = $row['contactno'];
    $currentEmail = $row['email']; 
} else {
    echo "User not found.";
    exit();
}

// form submission
if (isset($_POST['updateProfile'])) {
    $newName = mysqli_real_escape_string($conn, $_POST['name']);
    $newPhoneNo = mysqli_real_escape_string($conn, $_POST['phone']);
    $newPassword = mysqli_real_escape_string($conn, $_POST['password']);
    $newEmail = mysqli_real_escape_string($conn, $_POST['email']);

    // update query
    if (!empty($newPassword)) {
        $updateSql = "UPDATE users SET name='$newName', contactno='$newPhoneNo', password='$newPassword', email='$newEmail' WHERE email='$email'";
    } else {
        $updateSql = "UPDATE users SET name='$newName', contactno='$newPhoneNo', email='$newEmail' WHERE email='$email'";
    }

    if (mysqli_query($conn, $updateSql)) {
        $_SESSION['email'] = $newEmail;
        echo "<script>alert('Profile updated successfully!');</script>";
        header("Refresh:0");
    } else {
        echo "<script>alert('Error updating profile.');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="editProfile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="edit-container">
        <div class="edit-card">
            <div class="edit-header">
               <div class="profile_icon">
                   <i class="fas fa-user"></i>
                </div>
                <h1>Edit Profile</h1>
                <p>Update your account details</p>
            </div>
            
            <form method="POST" class="edit-form">
                <div class="form-row">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" id="name" name="name" class="form-input" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
                
                <div class="form-row">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" id="phone" name="phone" class="form-input" value="<?php echo htmlspecialchars($phoneNo); ?>" required>
                </div>
                
                <div class="form-row">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($currentEmail); ?>" required>
                </div>
                
                <div class="form-row">
                    <label for="password" class="form-label">New Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" class="form-input" placeholder="Leave blank to keep current">
                        <i class="far fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>
                
                <button type="submit" name="updateProfile" class="save-btn">
                    Save Changes
                </button>
            </form>
        </div>
    </div>

    <script>
        // toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>