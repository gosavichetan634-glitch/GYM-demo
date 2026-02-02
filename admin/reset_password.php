<?php
session_start();
include('../config.php');

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])){
    header('location:admin_login.php');
    exit();
}

if(isset($_GET['id'])) {
    $member_id = $_GET['id'];
    
    // Generate a random password
    $new_password = substr(md5(rand()), 0, 8);
    $hashed_password = md5($new_password);
    
    $query = "UPDATE members SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $hashed_password, $member_id);
    
    if($stmt->execute()) {
        $_SESSION['success_message'] = "Password reset successful. New password: " . $new_password;
    } else {
        $_SESSION['error_message'] = "Error resetting password: " . $conn->error;
    }
    
    header('Location: manage_members.php');
    exit();
}
?>