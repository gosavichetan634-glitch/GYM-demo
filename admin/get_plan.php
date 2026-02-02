<?php
include('../config.php');

if(isset($_GET['id'])) {
    $plan_id = $_GET['id'];
    $query = "SELECT * FROM membership_plans WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $plan_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $plan = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode($plan);
}