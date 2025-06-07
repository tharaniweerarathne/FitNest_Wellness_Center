<?php
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['name'])) {
    header("Location: SignIn.php");
    exit();
}

$name = $_SESSION['name'];
$email = $_SESSION['email'];

if (!isset($_GET['plan'])) {
    die("No membership plan selected.");
}

$selected_plan = $_GET['plan'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitnestwellness";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$check = $conn->prepare("SELECT * FROM memberships WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

$message = "";
if ($result->num_rows > 0) {
    $message = "You are already registered for a membership.";
} else {
    $stmt = $conn->prepare("INSERT INTO memberships (name, email, plan) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $selected_plan);

    if ($stmt->execute()) {
        $message = "You have successfully registered for the <strong>$selected_plan</strong> membership!";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Membership Confirmation</title>
  <style>
    :root {
      --primary-color: #4361ee;
      --success-color: #10b981;
      --dark-color: #333;
      --light-color: #f8fafc;
      --border-radius: 12px;
      --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      --transition: all 0.3s ease;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
      background: linear-gradient(135deg, #f6f9ff 0%, #e9f0ff 100%);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      color: var(--dark-color);
      padding: 20px;
    }
    
    .message-container {
      background-color: white;
      max-width: 500px;
      width: 100%;
      padding: 40px;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      text-align: center;
      animation: fadeIn 0.6s ease-out;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .message-container .icon {
      width: 80px;
      height: 80px;
      background-color: rgba(16, 185, 129, 0.1);
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 0 auto 24px;
    }
    
    .icon svg {
      width: 40px;
      height: 40px;
      fill: var(--success-color);
    }
    
    .message-container h2 {
      color: var(--dark-color);
      font-size: 28px;
      font-weight: 600;
      margin-bottom: 16px;
    }
    
    .message-container p {
      font-size: 18px;
      line-height: 1.6;
      color: #4b5563;
      margin-bottom: 30px;
    }
    
    .back-link {
      display: inline-block;
      padding: 14px 32px;
      background-color: var(--primary-color);
      color: white;
      text-decoration: none;
      font-weight: 500;
      border-radius: 8px;
      transition: var(--transition);
      font-size: 16px;
      letter-spacing: 0.5px;
    }
    
    .back-link:hover {
      background-color: #3651d1;
      transform: translateY(-2px);
      box-shadow: 0 8px 15px rgba(67, 97, 238, 0.3);
    }
    
    .logo {
      margin-top: 30px;
      font-size: 20px;
      font-weight: 700;
      color: var(--primary-color);
      letter-spacing: 1px;
    }

    @media (max-width: 600px) {
      .message-container {
        padding: 30px 20px;
      }
      
      .message-container h2 {
        font-size: 24px;
      }
      
      .message-container p {
        font-size: 16px;
      }
    }
  </style>
</head>
<body>

  <div class="message-container">
    <div class="icon">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
      </svg>
    </div>
    <h2>Membership Status</h2>
    <p><?php echo $message; ?></p>
    <a class="back-link" href="membership.php">Back to Membership Page</a>
    <div class="logo">FITNEST</div>
  </div>

</body>
</html>
