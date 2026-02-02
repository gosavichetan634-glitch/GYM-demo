<?php
include('config.php');
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
   header('location:login_form.php');
   exit();
}

// Get user details from session
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$user_id = $_SESSION['user_id'];

// Fetch latest progress
$progress_query = "SELECT * FROM fitness_progress WHERE user_id = ? ORDER BY date DESC LIMIT 1";
$stmt = $conn->prepare($progress_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$progress_data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile - StarFitnessClub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --accent-color: #3498db;
            --light-bg: #f5f6fa;
            --text-color: #2c3e50;
        }

        body {
            background: var(--light-bg);
        }

        /* Navbar Styles */
        .navbar {
            background: var(--primary-color);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .logo {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
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
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--secondary-color);
        }

        /* Main Content Styles */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .profile-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--accent-color);
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
        }

        .welcome-text {
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .user-name {
            color: var(--secondary-color);
            font-size: 1.5rem;
            font-weight: bold;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 2rem;
        }

        .stat-card {
            background: var(--light-bg);
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        .stat-card h3 {
            font-size: 0.9rem;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .stat-card .number {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--accent-color);
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.8rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--accent-color);
            color: white;
        }

        .btn-secondary {
            background: var(--secondary-color);
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="index.html" class="logo">Star<span>FitnessClub</span></a>
        <div class="nav-links">
            <a href="index.html"><i class="fas fa-home"></i> Home</a>
            <a href="fitness_plans.php"><i class="fas fa-dumbbell"></i> Fitness Plans</a>
            <a href="membership_form.php"><i class="fas fa-star"></i> Membership</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="dashboard-grid">
            <!-- Profile Card -->
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3 class="welcome-text">Welcome back,</h3>
                    <h2 class="user-name"><?php echo htmlspecialchars($user_name); ?></h2>
                    <p><?php echo htmlspecialchars($user_email); ?></p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Membership Status</h3>
                        <div class="number">Active</div>
                    </div>
                    <div class="stat-card">
                        <h3>Days Remaining</h3>
                        <div class="number">30</div>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="edit_profile.php" class="btn btn-primary">
                        <i class="fas fa-user-edit"></i> Edit Profile
                    </a>
                    <a href="fitness_plans.php" class="btn btn-secondary">
                        <i class="fas fa-dumbbell"></i> View Plans
                    </a>
                </div>
            </div>

            <!-- Fitness Progress Card -->
            <div class="profile-card">
                <h2><i class="fas fa-chart-line"></i> Fitness Progress</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Workouts Completed</h3>
                        <div class="number">12</div>
                    </div>
                    <div class="stat-card">
                        <h3>Current Weight</h3>
                        <div class="number"><?php echo $progress_data ? $progress_data['current_weight'] . ' kg' : 'Not set'; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Target Weight</h3>
                        <div class="number"><?php echo $progress_data ? $progress_data['target_weight'] . ' kg' : 'Not set'; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>BMI</h3>
                        <div class="number"><?php echo $progress_data ? $progress_data['bmi'] : 'Not set'; ?></div>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="track_progress.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Update Progress
                    </a>
                    <a href="workout_history.php" class="btn btn-secondary">
                        <i class="fas fa-history"></i> View History
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>



