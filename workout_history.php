<?php
include('config.php');
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header('location:login_form.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all progress records for the user, ordered by date
$history_query = "SELECT * FROM fitness_progress WHERE user_id = ? ORDER BY date DESC";
$stmt = $conn->prepare($history_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Workout History - StarFitnessClub</title>
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
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .history-title {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--primary-color);
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .history-table th,
        .history-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .history-table th {
            background-color: var(--primary-color);
            color: white;
        }

        .history-table tr:nth-child(even) {
            background-color: var(--light-bg);
        }

        .history-table tr:hover {
            background-color: #f1f1f1;
        }

        .no-records {
            text-align: center;
            padding: 2rem;
            color: var(--secondary-color);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 2rem;
            color: var(--accent-color);
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .progress-trend {
            color: var(--accent-color);
            font-weight: bold;
        }

        .trend-up {
            color: #2ecc71;
        }

        .trend-down {
            color: #e74c3c;
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
        <h2 class="history-title">Your Workout History</h2>

        <?php if($result->num_rows > 0): ?>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Current Weight (kg)</th>
                        <th>Target Weight (kg)</th>
                        <th>Height (cm)</th>
                        <th>BMI</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $prev_weight = null;
                    while($row = $result->fetch_assoc()): 
                        // Calculate weight change trend
                        $trend = '';
                        if($prev_weight !== null) {
                            if($row['current_weight'] < $prev_weight) {
                                $trend = '<span class="progress-trend trend-down">↓</span>';
                            } elseif($row['current_weight'] > $prev_weight) {
                                $trend = '<span class="progress-trend trend-up">↑</span>';
                            }
                        }
                        $prev_weight = $row['current_weight'];
                    ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($row['date'])); ?></td>
                            <td><?php echo $row['current_weight'] . ' ' . $trend; ?></td>
                            <td><?php echo $row['target_weight']; ?></td>
                            <td><?php echo $row['height']; ?></td>
                            <td><?php echo $row['bmi']; ?></td>
                            <td>
                                <?php
                                $weight_diff = $row['target_weight'] - $row['current_weight'];
                                if(abs($weight_diff) < 0.5) {
                                    echo "Target achieved!";
                                } else {
                                    echo number_format(abs($weight_diff), 1) . " kg to " . 
                                         ($weight_diff > 0 ? "gain" : "lose");
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-records">
                <p>No workout history found. Start tracking your progress!</p>
            </div>
        <?php endif; ?>

        <a href="user_page.php" class="back-link">Back to Profile</a>
    </div>
</body>
</html>