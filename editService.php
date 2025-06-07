<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitnestwellness";


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$serviceName = "";
$serviceDescription = "";
$serviceCategory = "";
$servicePrice = "";
$currentImagePath = "";
$scheduleArray = [];
$serviceId = 0;
$message = "";

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $serviceId = $_GET['edit'];

    $stmt = $conn->prepare("SELECT id, name, description, image_path, category, price, schedule FROM services WHERE id = ?");
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $serviceName = $row["name"];
        $serviceDescription = $row["description"];
        $serviceCategory = $row["category"];
        $servicePrice = $row["price"];
        $currentImagePath = $row["image_path"];

        if (!empty($row["schedule"])) {
            $scheduleItems = explode(", ", $row["schedule"]);
            foreach ($scheduleItems as $item) {
                if (strpos($item, ":") !== false) {
                    list($day, $time) = explode(": ", $item);
                    if (strpos($time, " - ") !== false) {
                        list($startTime, $endTime) = explode(" - ", $time);
                        $scheduleArray[$day] = [
                            'start' => $startTime,
                            'end' => $endTime
                        ];
                    }
                }
            }
        }
    } else {
        $message = "Service not found.";
    }
    
    $stmt->close();
} else {
    $message = "No service ID provided.";
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serviceId = $_POST["serviceId"];
    $serviceName = $_POST["serviceName"];
    $serviceDescription = $_POST["serviceDescription"];
    $serviceCategory = $_POST["serviceCategory"];
    $servicePrice = $_POST["servicePrice"];
    
    // handle new category
    if ($serviceCategory == "new" && !empty($_POST["newCategory"])) {
        $serviceCategory = $_POST["newCategory"];
    }
    
    // keep current image path unless a new image is uploaded
    $imagePath = $currentImagePath;
    
    // handle file upload if a new image is provided
    if (isset($_FILES["serviceImage"]) && $_FILES["serviceImage"]["error"] == UPLOAD_ERR_OK) {
        $targetDir = "Services/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileName = basename($_FILES["serviceImage"]["name"]);
        $targetFile = $targetDir . uniqid() . '_' . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        

        $check = getimagesize($_FILES["serviceImage"]["tmp_name"]);
        if ($check === false) {
            $message = "File is not an image.";
        } else {

            if ($_FILES["serviceImage"]["size"] > 2000000) {
                $message = "Sorry, your file is too large.";
            } else {

                $allowedTypes = ["jpg", "jpeg", "png", "gif"];
                if (!in_array($imageFileType, $allowedTypes)) {
                    $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                } else {

                    if (move_uploaded_file($_FILES["serviceImage"]["tmp_name"], $targetFile)) {
                        // delete old image if it exists and is not a default image
                        if (!empty($currentImagePath) && file_exists($currentImagePath)) {
                            unlink($currentImagePath);
                        }
                        $imagePath = $targetFile;
                    } else {
                        $message = "Sorry, there was an error uploading your file.";
                    }
                }
            }
        }
    }
    
    if (empty($message)) {
 
        $schedule = [];
        $days = $_POST["days"] ?? [];
        
        foreach ($days as $day) {
            $startTimeKey = strtolower($day) . "Start";
            $endTimeKey = strtolower($day) . "End";
            
            if (isset($_POST[$startTimeKey]) && isset($_POST[$endTimeKey])) {
                $startTime = $_POST[$startTimeKey];
                $endTime = $_POST[$endTimeKey];
                $schedule[] = "$day: $startTime - $endTime";
            }
        }
        
        $scheduleString = implode(", ", $schedule);
        

        $sql = "UPDATE services SET name = ?, description = ?, image_path = ?, category = ?, price = ?, schedule = ? WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssdsi", $serviceName, $serviceDescription, $imagePath, $serviceCategory, $servicePrice, $scheduleString, $serviceId);
        
        if ($stmt->execute()) {
            $message = "Service updated successfully.";
            
            $result = $conn->query("SELECT schedule FROM services WHERE id = $serviceId");
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                
                $scheduleArray = [];
                if (!empty($row["schedule"])) {
                    $scheduleItems = explode(", ", $row["schedule"]);
                    foreach ($scheduleItems as $item) {
                        if (strpos($item, ":") !== false) {
                            list($day, $time) = explode(": ", $item);
                            if (strpos($time, " - ") !== false) {
                                list($startTime, $endTime) = explode(" - ", $time);
                                $scheduleArray[$day] = [
                                    'start' => $startTime,
                                    'end' => $endTime
                                ];
                            }
                        }
                    }
                }
            }
        } else {
            $message = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

// fetch all categories for dropdown
$categories = [];
$categoryQuery = "SELECT DISTINCT category FROM services ORDER BY category";
$categoryResult = $conn->query($categoryQuery);
if ($categoryResult->num_rows > 0) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row["category"];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Service </title>
    <style>
        :root {
            --primary-color: #0a0b0e;
            --secondary-color: #D91656;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --border-color: #ddd;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: var(--primary-color);
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 10px;
            margin-top: 0;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        input[type="text"],
        input[type="number"],
        input[type="time"],
        input[type="file"],
        select,
        textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--secondary-color);
            outline: none;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .service-image-preview {
            max-width: 250px;
            max-height: 250px;
            margin-top: 15px;
            border-radius: 8px;
            border: 2px solid var(--light-color);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .schedule-container {
            border: 1px solid var(--border-color);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            background-color: #f8fafb;
        }

        .day-selection {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px dashed var(--border-color);
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
        }

        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            cursor: pointer;
        }

        .time-slots {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .time-slot {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid var(--secondary-color);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .time-row {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
        }

        .time-col {
            flex: 1;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #c0144b;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 8px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .hidden {
            display: none;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            .time-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Service</h2>
        
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, "successfully") !== false ? "alert-success" : "alert-danger"; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($serviceId > 0): ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?edit=" . $serviceId); ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="serviceId" value="<?php echo $serviceId; ?>">
                
                <div class="form-group">
                    <label for="serviceName">Service Name</label>
                    <input type="text" id="serviceName" name="serviceName" value="<?php echo htmlspecialchars($serviceName); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="serviceDescription">Description</label>
                    <textarea id="serviceDescription" name="serviceDescription" required><?php echo htmlspecialchars($serviceDescription); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="serviceCategory">Category</label>
                    <select id="serviceCategory" name="serviceCategory" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>" <?php echo ($category == $serviceCategory) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="new">Add New Category</option>
                    </select>
                </div>
                
                <div class="form-group hidden" id="newCategoryDiv">
                    <label for="newCategory">New Category Name</label>
                    <input type="text" id="newCategory" name="newCategory">
                </div>
                
                <div class="form-group">
                    <label for="servicePrice">Price</label>
                    <input type="number" id="servicePrice" name="servicePrice" step="0.01" value="<?php echo htmlspecialchars($servicePrice); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="serviceImage">Service Image</label>
                    <input type="file" id="serviceImage" name="serviceImage" accept="image/*">
                    <small>Leave empty to keep current image</small>
                    
                    <?php if (!empty($currentImagePath)): ?>
                        <div>
                            <p>Current Image:</p>
                            <img src="<?php echo htmlspecialchars($currentImagePath); ?>" alt="Current Service Image" class="service-image-preview">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="schedule-container">
                    <h4>Service Schedule</h4>
                    
                    <div class="day-selection">
                        <p>Select Days:</p>
                        <div class="checkbox-group">
                            <?php
                            $daysOfWeek = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
                            foreach ($daysOfWeek as $day):
                                $checked = array_key_exists($day, $scheduleArray) ? 'checked' : '';
                                $startTime = array_key_exists($day, $scheduleArray) ? $scheduleArray[$day]['start'] : '09:00';
                                $endTime = array_key_exists($day, $scheduleArray) ? $scheduleArray[$day]['end'] : '17:00';
                            ?>
                                <div class="checkbox-item">
                                    <input class="day-checkbox" type="checkbox" name="days[]" id="<?php echo strtolower($day); ?>Checkbox" value="<?php echo $day; ?>" <?php echo $checked; ?>>
                                    <label for="<?php echo strtolower($day); ?>Checkbox"><?php echo $day; ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="time-slots">
                        <?php foreach ($daysOfWeek as $day):
                            $startTime = array_key_exists($day, $scheduleArray) ? $scheduleArray[$day]['start'] : '09:00';
                            $endTime = array_key_exists($day, $scheduleArray) ? $scheduleArray[$day]['end'] : '17:00';
                            $display = array_key_exists($day, $scheduleArray) ? 'block' : 'none';
                        ?>
                            <div class="time-slot" id="<?php echo strtolower($day); ?>TimeSlot" style="display: <?php echo $display; ?>">
                                <label><?php echo $day; ?> Hours:</label>
                                <div class="time-row">
                                    <div class="time-col">
                                        <label>Start Time:</label>
                                        <input type="time" name="<?php echo strtolower($day); ?>Start" value="<?php echo $startTime; ?>">
                                    </div>
                                    <div class="time-col">
                                        <label>End Time:</label>
                                        <input type="time" name="<?php echo strtolower($day); ?>End" value="<?php echo $endTime; ?>">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Update Service</button>
                    <a href="InsertServices.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-warning">
                <?php echo $message; ?>
                <p><a href="InsertServices.php" class="btn btn-primary">Back to Service List</a></p>
            </div>
        <?php endif; ?>
    </div>
    
    <script>

        document.addEventListener('DOMContentLoaded', function() {

            const dayCheckboxes = document.querySelectorAll('.day-checkbox');
            dayCheckboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    const day = this.value.toLowerCase();
                    const timeSlot = document.getElementById(day + 'TimeSlot');
                    
                    if (this.checked) {
                        timeSlot.style.display = 'block';
                    } else {
                        timeSlot.style.display = 'none';
                    }
                });
            });
            
            const categorySelect = document.getElementById('serviceCategory');
            const newCategoryDiv = document.getElementById('newCategoryDiv');
            
            categorySelect.addEventListener('change', function() {
                if (this.value === 'new') {
                    newCategoryDiv.classList.remove('hidden');
                } else {
                    newCategoryDiv.classList.add('hidden');
                }
            });

            const imageInput = document.getElementById('serviceImage');
            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {

                        const oldPreview = document.querySelector('.service-image-preview');
                        if (oldPreview) {
                            oldPreview.src = e.target.result;
                        } else {

                            const previewDiv = document.createElement('div');
                            
                            const previewText = document.createElement('p');
                            previewText.textContent = 'New Image Preview:';
                            
                            const previewImg = document.createElement('img');
                            previewImg.src = e.target.result;
                            previewImg.className = 'service-image-preview';
                            previewImg.alt = 'Service Image Preview';
                            
                            previewDiv.appendChild(previewText);
                            previewDiv.appendChild(previewImg);
                            
                            imageInput.parentNode.appendChild(previewDiv);
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
            
            if (categorySelect.value === 'new') {
                newCategoryDiv.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>