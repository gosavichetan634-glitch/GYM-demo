<?php
include('../config.php');

if(isset($_GET['id'])) {
    $payment_id = $_GET['id'];
    
    $query = "SELECT p.*, m.name as member_name 
              FROM payments p 
              JOIN members m ON p.member_id = m.id 
              WHERE p.id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode($payment);
}