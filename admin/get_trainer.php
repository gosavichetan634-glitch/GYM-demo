<?php
include('../config.php');

if(isset($_GET['id'])) {
    $trainer_id = $_GET['id'];
    $query = "SELECT * FROM trainers WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $trainer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $trainer = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode($trainer);
}