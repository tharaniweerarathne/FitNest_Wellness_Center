<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitnestwellness";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// fetch services from the database
$sql = "SELECT id, name, description, image_path, category, price, schedule FROM services";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // convert schedule times to AM/PM format
        $schedule = $row["schedule"];
        

        if ($schedule && is_string($schedule)) {
            $scheduleData = json_decode($schedule, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $formattedSchedule = "";
                foreach ($scheduleData as $day => $times) {
                    if (!empty($times['start']) && !empty($times['end'])) {
                        $startTime = date("g:i A", strtotime($times['start']));
                        $endTime = date("g:i A", strtotime($times['end']));
                        $formattedSchedule .= "$day: $startTime - $endTime<br>";
                    }
                }
                $schedule = $formattedSchedule ?: "Not scheduled";
            } else {

                $schedule = preg_replace_callback('/(\d{1,2}:\d{2})/', function($matches) {
                    return date("g:i A", strtotime($matches[0]));
                }, $schedule);
            }
        }

        echo "<tr>
                <td><img src='" . htmlspecialchars($row["image_path"]) . "' width='50' height='50' alt='Service Image'></td>
                <td>" . htmlspecialchars($row["name"]) . "</td>
                <td>" . htmlspecialchars($row["description"]) . "</td>
                <td>" . htmlspecialchars($row["category"]) . "</td>
                <td>Rs " . number_format($row["price"], 2) . "</td>
                <td>" . $schedule . "</td>
                <td style='display: flex; gap: 5px;'>
                    <form action='editService.php' method='GET'>
                        <input type='hidden' name='edit' value='" . $row["id"] . "'>
                        <button type='submit' class='btn-edit' style='background-color:#0e8a42; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: background-color 0.2s ease; font-size: 14px;'>
                            <i class='ri-edit-line'></i> Edit
                        </button>
                    </form>



                    <form action='delete_service.php' method='POST' onsubmit='return confirm(\"Are you sure you want to delete this service?\");'>
                        <input type='hidden' name='service_id' value='" . $row["id"] . "'>
                        <button type='submit' class='btn-delete' style='background-color: #e74c3c; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: background-color 0.2s ease; font-size: 14px;'>
                            <i class='ri-delete-bin-line'></i> Delete
                        </button>
                    </form>
                    
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='7'>No services found</td></tr>";
}

$conn->close();
?>