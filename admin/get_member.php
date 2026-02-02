<?php
include('../config.php');

if(isset($_GET['id'])) {
    $member_id = $_GET['id'];
    $query = "SELECT id, name, email, user_type FROM user_form WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $member = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode($member);
}
