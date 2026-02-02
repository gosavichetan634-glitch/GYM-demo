<?php
include('config.php');
session_start();

if(!isset($_SESSION['user_id'])) {
    header('location:login_form.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get active membership
$query = "SELECT * FROM memberships WHERE user_id = ? AND status = 'active' AND end_date >= CURDATE()";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$membership = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Membership Status - StarFitnessClub</title>
    <style>
        /* Add your CSS styles here */
        .membership-status {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .status-active {
            color: #2ecc71;
        }

        .status-inactive {
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="membership-status">
        <h2>Your Membership Status</h2>
        
        <?php if($membership): ?>
            <p class="status-active">Active Membership</p>
            <p>Plan: <?php echo htmlspecialchars($membership['plan_name']); ?></p>
            <p>Start Date: <?php echo htmlspecialchars($membership['start_date']); ?></p>
            <p>End Date: <?php echo htmlspecialchars($membership['end_date']); ?></p>
        <?php else: ?>
            <p class="status-inactive">No active membership found</p>
            <p>Would you like to <a href="membership_form.php">activate a membership</a>?</p>
        <?php endif; ?>
    </div>
</body>
</html>