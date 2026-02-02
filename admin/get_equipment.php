<?php
include('../config.php');

if(isset($_GET['id'])) {
    $equipment_id = $_GET['id'];
    $query = "SELECT * FROM equipment WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $equipment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $equipment = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode($equipment);
}