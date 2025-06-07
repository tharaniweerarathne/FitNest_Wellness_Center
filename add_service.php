<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitnestwellness";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serviceName = $_POST["serviceName"];
    $serviceDescription = $_POST["serviceDescription"];
    $serviceCategory = $_POST["serviceCategory"];
    $servicePrice = $_POST["servicePrice"];
    
    // Handle file upload
    $imagePath = '';
    if (isset($_FILES["serviceImage"]) && $_FILES["serviceImage"]["error"] == UPLOAD_ERR_OK) {
        $targetDir = "Services/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileName = basename($_FILES["serviceImage"]["name"]);
        $targetFile = $targetDir . uniqid() . '_' . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        
        // Check if image file is actual image
        $check = getimagesize($_FILES["serviceImage"]["tmp_name"]);
        if ($check === false) {
            die("File is not an image.");
        }
        
        // Check file size (max 2MB)
        if ($_FILES["serviceImage"]["size"] > 2000000) {
            die("Sorry, your file is too large.");
        }
        
        // Allow certain file formats
        $allowedTypes = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $allowedTypes)) {
            die("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
        }
        
        // Upload file
        if (move_uploaded_file($_FILES["serviceImage"]["tmp_name"], $targetFile)) {
            $imagePath = $targetFile;
        } else {
            die("Sorry, there was an error uploading your file.");
        }
    }
    
    // Prepare schedule data
    $schedule = [];
    $days = $_POST["days"] ?? [];
    
    foreach ($days as $day) {
        $startTime = $_POST[strtolower($day) . "Start"];
        $endTime = $_POST[strtolower($day) . "End"];
        $schedule[] = "$day: $startTime - $endTime";
    }
    
    $scheduleString = implode(", ", $schedule);
    
    // Insert into database
    $sql = "INSERT INTO services (name, description, image_path, category, price, schedule) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssds", $serviceName, $serviceDescription, $imagePath, $serviceCategory, $servicePrice, $scheduleString);
    
    if ($stmt->execute()) {
        header("Location: InsertServices.php?success=1");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    
    $stmt->close();
}

$conn->close();
?>