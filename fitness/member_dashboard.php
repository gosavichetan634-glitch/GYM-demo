<?php
session_start();
include('config.php');

// Check if user is logged in as member
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'member') {
    header('location:login_form.php');
    exit();
}

// Get member details
$member_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT m.*, p.plan_name 
                       FROM members m 
                       LEFT JOIN membership_plans p ON m.plan_id = p.id 
                       WHERE m.id = ?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - StarFitnessClub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
        
        <div class="membership-info">
            <h2>Your Membership Details</h2>
            <p>Plan: <?php echo htmlspecialchars($member['plan_name']); ?></p>
            <p>Join Date: <?php echo htmlspecialchars($member['join_date']); ?></p>
            <p>Status: <?php echo htmlspecialchars($member['status']); ?></p>
        </div>

        <div class="actions">
            <a href="update_profile.php" class="btn">Update Profile</a>
            <a href="view_schedule.php" class="btn">View Schedule</a>
            <a href="logout.php" class="btn">Logout</a>
        </div>
    </div>
</body>
</html>
