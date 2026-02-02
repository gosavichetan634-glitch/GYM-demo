<?php
include('config.php');
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header('location:login_form.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Fetch current progress data
$fetch_query = "SELECT * FROM fitness_progress WHERE user_id = ? ORDER BY date DESC LIMIT 1";
$stmt = $conn->prepare($fetch_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$current_progress = $result->fetch_assoc();

// Handle form submission
if(isset($_POST['update_progress'])) {
    $current_weight = mysqli_real_escape_string($conn, $_POST['current_weight']);
    $target_weight = mysqli_real_escape_string($conn, $_POST['target_weight']);
    $height = mysqli_real_escape_string($conn, $_POST['height']);
    
    // Calculate BMI
    $height_in_meters = $height / 100; // Convert cm to meters
    $bmi = round($current_weight / ($height_in_meters * $height_in_meters), 1);

    // Insert new progress record
    $insert_query = "INSERT INTO fitness_progress (user_id, current_weight, target_weight, height, bmi, date) 
                     VALUES (?, ?, ?, ?, ?, CURDATE())";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("idddd", $user_id, $current_weight, $target_weight, $height, $bmi);

    if($stmt->execute()) {
        $success_msg = "Progress updated successfully!";
    } else {
        $error_msg = "Error updating progress. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Track Progress - StarFitnessClub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --accent-color: #3498db;
            --light-bg: #f5f6fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: var(--light-bg);
        }

        .navbar {
            background: var(--primary-color);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .logo span {
            color: var(--secondary-color);
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-title {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .btn-submit {
            background: var(--accent-color);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
            margin-top: 1rem;
        }

        .btn-submit:hover {
            background: #2980b9;
        }

        .success-msg {
            background: #2ecc71;
            color: white;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .error-msg {
            background: #e74c3c;
            color: white;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: var(--accent-color);
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="index.html" class="logo">Star<span>FitnessClub</span></a>
        <div class="nav-links">
            <a href="user_page.php"><i class="fas fa-user"></i> Profile</a>
            <a href="fitness_plans.php"><i class="fas fa-dumbbell"></i> Fitness Plans</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <h2 class="form-title">Track Your Progress</h2>
        
        <?php if($success_msg): ?>
            <div class="success-msg"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <?php if($error_msg): ?>
            <div class="error-msg"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="current_weight">Current Weight (kg)</label>
                <input type="number" step="0.1" class="form-control" id="current_weight" name="current_weight" 
                       value="<?php echo $current_progress ? $current_progress['current_weight'] : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="target_weight">Target Weight (kg)</label>
                <input type="number" step="0.1" class="form-control" id="target_weight" name="target_weight"
                       value="<?php echo $current_progress ? $current_progress['target_weight'] : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="height">Height (cm)</label>
                <input type="number" step="0.1" class="form-control" id="height" name="height"
                       value="<?php echo $current_progress ? $current_progress['height'] : ''; ?>" required>
            </div>

            <button type="submit" name="update_progress" class="btn-submit">Update Progress</button>
        </form>

        <a href="user_page.php" class="back-link">Back to Profile</a>
    </div>
</body>
</html>