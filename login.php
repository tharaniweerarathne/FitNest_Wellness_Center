<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "fitnestwellness");

if ($conn === false) {
    die("Connection failed: " . mysqli_connect_error());
}

$email = '';
$password = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['gender'] = $row['gender'];

            if ($row['role'] == 'Admin') {
                header("Location: DashboardAdmin.php");
                exit();
            } elseif ($row['role'] == 'Staff') {
                header("Location:staffDashboard.php");
                exit();
            } elseif ($row['role'] == 'Customer') {
                header("Location: customerDashboard.php");
                exit();
            } else {
                $error_message = 'Unknown user role';
            }
        } else {
            $error_message = 'Invalid email or password';
        }
    } else {
        $error_message = 'Please enter both email and password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="loginpage.css">
    <title>Login</title>
</head>
<body>
    <div class="login-box">
        <h1>SIGN IN</h1>
        <p>Transform your body, transform your life</p>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="user-box">
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required>
                <label>Email Address</label>
                <span class="email-error"></span>
            </div>

            <div class="user-box password-field">
                <input type="password" name="password" id="password" value="<?php echo htmlspecialchars($password); ?>" required>
                <label for="password">Password</label>
                <i class="ri-eye-line password-toggle"></i>
            </div>

            <button type="submit">SIGN IN</button>

            <div class="signup-link">
                Don't have an account? <a href="signup.php">Sign up here</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.querySelector('.password-toggle');
            const passwordInput = document.getElementById('password');
            const emailInput = document.getElementById('email');
            const emailError = document.querySelector('.email-error');

            if (toggle && passwordInput) {
                toggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.classList.toggle('ri-eye-line');
                    this.classList.toggle('ri-eye-off-line');
                });
            }

            if (emailInput) {
                emailInput.addEventListener('input', function () {
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailPattern.test(emailInput.value)) {
                        emailError.textContent = "Please enter a valid email address";
                        emailError.style.visibility = "visible";
                    } else {
                        emailError.textContent = "";
                        emailError.style.visibility = "hidden";
                    }
                });
                
                // Trigger input event in case there's a value already (after form submission)
                if (emailInput.value) {
                    emailInput.dispatchEvent(new Event('input'));
                }
            }
        });
    </script>
</body>
</html>