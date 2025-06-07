<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "fitnestwellness");

if ($conn === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$email = $password = $name = $phoneNo = $gender = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $name = trim($_POST['name']);
    $phoneNo = trim($_POST['phone']);
    $gender = strtolower(trim($_POST['gender']));
    $role = 'customer';

    if (empty($email) || empty($password) || empty($name) || empty($phoneNo) || empty($gender)) {
        $errorMessage = 'All fields are required!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match("/@.*\..*com$/", $email)) {
        $errorMessage = 'Invalid email format! Email must contain @, ., and end with .com';
    } elseif (!preg_match("/^[0-9]{10}$/", $phoneNo)) {
        $errorMessage = 'Invalid phone number! It must be 10 digits long.';
    } elseif (strlen($password) < 2) {
        $errorMessage = 'Password must be at least 8 characters long!';
    } else {
        // check if email exists
        $checkEmail = "SELECT * FROM users WHERE email=?";
        $stmt = $conn->prepare($checkEmail);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errorMessage = 'Email Already Exists!';
        } else {

            $plainPassword = $password;

            $insertQuery = "INSERT INTO users(email, password, name, contactno, gender, role) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("ssssss", $email, $plainPassword, $name, $phoneNo, $gender, $role);

            if ($stmt->execute()) {  
                echo "<script>
                        alert('Registration successful!');
                        window.location.href='loginpage.php';
                      </script>";
                exit();
            } else {
                $errorMessage = 'Error: ' . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="signUp.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <title>Sign Up </title>
</head>
<body>
    <div class="login-box">
        <h1>CREATE YOUR ACCOUNT</h1>
        <p>Join FITNEST WELLNESS and start your fitness journey today</p>
        
        <form id="" action="" method="POST">
            <div class="user-box">
                <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                <label>Full Name</label>
            </div>
            
            <div class="user-box">
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required>
                <label>Email Address</label>
                <span class="email-error"></span>
            </div>
            
            <div class="user-box password-field">
                <input type="password" name="password" id="password" value="<?php echo htmlspecialchars($password); ?>" required minlength="2">
                <label for="password">Password</label>
                <i class="ri-eye-line password-toggle"></i>
                <span class="password-error"></span>
            </div>

            <div class="user-box">
                <input type="tel" name="phone" id="phone" required pattern="[0-9]{10}" title="10 digit phone number" value="<?php echo htmlspecialchars($phoneNo); ?>">
                <label for="phone">Phone Number</label>
                <span class="input-hint">10 digits only</span>
            </div>
            
            <div class="select-box">
                <label>Gender</label>
                <select name="gender" required>
                    <option value="" disabled selected>Select your gender</option>
                    <option value="male" <?php if($gender == 'male') echo 'selected'; ?>>Male</option>
                    <option value="female" <?php if($gender == 'female') echo 'selected'; ?>>Female</option>
                    <option value="prefer-not-to-say" <?php if($gender == 'prefer-not-to-say') echo 'selected'; ?>>Prefer not to say</option>
                </select>
            </div>
            
            <button type="submit" name="SignUp">SIGN UP</button>

            <div class="signup-link">
                Already have an account? <a href="login.php">Sign In here</a>
            </div>
        </form>

        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // phone number validation
            const phoneInput = document.getElementById('phone');
            if (phoneInput) {
                phoneInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    if (this.value.length > 10) {
                        this.value = this.value.slice(0, 10);
                    }
                });
            }

            // password visibility toggle
            const toggle = document.querySelector('.password-toggle');
            const passwordInput = document.getElementById('password');
            
            if (toggle && passwordInput) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.classList.toggle('ri-eye-line');
                    this.classList.toggle('ri-eye-off-line');
                });
            }

            // password validation
            const passwordError = document.querySelector('.password-error');
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    const passwordPattern = /^(?=.*[A-Za-z])(?=.*\d).{2,}$/;
                    if (!passwordPattern.test(passwordInput.value)) {
                        passwordError.textContent = "Password must be at least 2 characters with letters and numbers";
                        passwordError.style.visibility = "visible";
                    } else {
                        passwordError.textContent = "";
                        passwordError.style.visibility = "hidden";
                    }
                });
            }

            // email validation
            const emailInput = document.getElementById('email');
            const emailError = document.querySelector('.email-error');
            if (emailInput) {
                emailInput.addEventListener('input', function() {
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailPattern.test(emailInput.value)) {
                        emailError.textContent = "Please enter a valid email address";
                        emailError.style.visibility = "visible";
                    } else {
                        emailError.textContent = "";
                        emailError.style.visibility = "hidden";
                    }
                });
            }
        });
    </script>
</body>
</html>