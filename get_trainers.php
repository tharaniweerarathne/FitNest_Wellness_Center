<?php

header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitnestwellness";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Connection failed: ' . $conn->connect_error
    ]));
}

if (!isset($_POST['category']) || empty($_POST['category'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Category is required'
    ]);
    exit;
}

$category = $_POST['category'];

$stmt = $conn->prepare("SELECT id, name, category FROM trainer WHERE category = ?");
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();

$trainers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $trainers[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'trainers' => $trainers
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No trainers found for this category',
        'trainers' => []
    ]);
}

$stmt->close();
$conn->close();
?>