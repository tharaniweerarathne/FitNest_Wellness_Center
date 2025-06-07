<?php
session_start();
$db = new PDO('mysql:host=localhost;dbname=fitnestwellness', 'root', '');

$service_id = $_GET['service_id'] ?? null;
$service = [];
if ($service_id) {
    $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
}

$email = $_SESSION['email'] ?? null;
$user_name = $_SESSION['name'] ?? null;

$is_member = false;
if ($email) {
    $stmt = $db->prepare("SELECT 1 FROM memberships WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $is_member = (bool)$stmt->fetchColumn();
}

// get trainers for this service category
$trainers = [];
if ($service) {
    $stmt = $db->prepare("SELECT * FROM trainer WHERE category = ?");
    $stmt->execute([$service['category']]);
    $trainers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!$is_member) {
      $error = "You need to be a member to book a service.";
  } else {
      $trainer_id = $_POST['trainer_id'];
      $schedule = $_POST['schedule'];
      
      $stmt = $db->prepare("INSERT INTO bookings (service_id, trainer_id, schedule, status, email, name) 
                           VALUES (?, ?, ?, 'confirmed', ?, ?)");
      if ($stmt->execute([$service_id, $trainer_id, $schedule, $email, $user_name])) {
          $success = "Booking submitted successfully!";
          $show_back_button = true; // flag to show back button
      } else {
          $error = "Error submitting booking. Please try again.";
      }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book <?= htmlspecialchars($service['name'] ?? 'Service') ?></title>
    <style>
        :root {
            --primary-color: #0a0b0e;
            --primary-color-light: #1f2125;
            --primary-color-extra-light: #35373b;
            --secondary-color: #D91656;
            --secondary-color-dark: #AF1740;
            --text-light: #d1d5db;
            --white: #ffffff;
            --max-width: 1200px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 2rem;
            font-size: 2.2rem;
            text-align: center;
            position: relative;
            padding-bottom: 0.5rem;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: var(--secondary-color);
        }

        h2 {
            color: var(--primary-color-light);
            margin-bottom: 1rem;
            font-size: 1.6rem;
        }

        .service-card {
            display: flex;
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 2.5rem;
        }

        .service-image {
            width: 300px;
            height: auto;
            object-fit: cover;
        }

        .service-content {
            padding: 2rem;
            flex: 1;
        }

        .service-meta {
            display: flex;
            gap: 1.5rem;
            margin: 1rem 0;
        }

        .service-meta p {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--primary-color-light);
        }

        .service-meta strong {
            color: var(--primary-color);
        }

        .booking-form {
            background: var(--white);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--primary-color-light);
        }

        input, select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(217, 22, 86, 0.1);
        }

        input[readonly] {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }

        button {
            background-color: var(--secondary-color);
            color: var(--white);
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            width: 100%;
        }

        button:hover {
            background-color: var(--secondary-color-dark);
            transform: translateY(-2px);
        }

        .error {
            background-color: #fee;
            color: #d32f2f;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #d32f2f;
        }

        .success {
            background-color:#1f2125;
            color:white;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }

        .error a, .success a {
            color: inherit;
            text-decoration: underline;
            font-weight: 600;
        }

        .back-button {
            display: inline-block;
           padding: 0.6rem 1.5rem;
           background-color: var(--secondary-color);
           color: white;
           text-decoration: none;
           border-radius: 6px;
           font-weight: 600;
           transition: all 0.3s ease;
           text-align: center;
        }

        .back-button:hover {
           background-color: #AF1740;
           color: white
           transform: translateY(-2px);
           box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        @media (max-width: 768px) {
            .service-card {
                flex-direction: column;
            }
            
            .service-image {
                width: 100%;
                height: 250px;
            }
            
            .service-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Book <?= htmlspecialchars($service['name'] ?? 'Service') ?></h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
    <div class="success">
        <?= $success ?>
        <?php if (isset($show_back_button) && $show_back_button): ?>
            <div style="margin-top: 1.5rem;">
                <a href="customerDashboard.php" class="back-button">Back to Main Page</a>
            </div>
        <?php endif; ?>
    </div>



        <?php else: ?>
            <?php if ($service): ?>
                <div class="service-card">
                    <img src="<?= htmlspecialchars($service['image_path']) ?>" alt="<?= htmlspecialchars($service['name']) ?>" class="service-image">
                    <div class="service-content">
                        <h2><?= htmlspecialchars($service['name']) ?></h2>
                        <p><?= htmlspecialchars($service['description']) ?></p>
                        <div class="service-meta">
                            <p><strong>Price:</strong> Rs. <?= number_format($service['price'], 2) ?></p>
                            <p><strong>Category:</strong> <?= htmlspecialchars($service['category']) ?></p>
                        </div>
                    </div>
                </div>
                
                <?php if (!$is_member): ?>
                    <div class="error">
                        You need to be a member to book this service. 
                        <a href="membership.php">Register for membership here</a>.
                    </div>
                <?php else: ?>
                    <form method="post" class="booking-form">
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user_name) ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Your Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="trainer_id">Select Trainer</label>
                            <select id="trainer_id" name="trainer_id" required>
                                <option value="">-- Select a Trainer --</option>
                                <?php foreach ($trainers as $trainer): ?>
                                    <option value="<?= $trainer['id'] ?>">
                                        <?= htmlspecialchars($trainer['name']) ?> - <?= htmlspecialchars($trainer['category']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="schedule">Select Schedule</label>
                            <select id="schedule" name="schedule" required>
                                <option value="">-- Select a Time Slot --</option>
                                <?php 
                                $time_slots = explode(',', $service['schedule']);
                                foreach ($time_slots as $slot): 
                                    $slot = trim($slot);
                                    if (!empty($slot)):
                                ?>
                                    <option value="<?= htmlspecialchars($slot) ?>"><?= htmlspecialchars($slot) ?></option>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </select>
                        </div>
                        
                        <button type="submit">Book Now</button>
                    </form>
                    
                <?php endif; ?>
            <?php else: ?>
                <p>Service not found.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>