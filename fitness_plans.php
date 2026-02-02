<?php
include('config.php');
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header('location:login_form.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fitness Plans - StarFitnessClub</title>
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

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .welcome-section {
            text-align: center;
            margin-bottom: 3rem;
        }

        .welcome-section h1 {
            color: var(--text-color);
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .plan-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .plan-card:hover {
            transform: translateY(-5px);
        }

        .plan-card h2 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .plan-card .price {
            color: var(--secondary-color);
            font-size: 2rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .plan-features {
            list-style: none;
            margin-bottom: 2rem;
        }

        .plan-features li {
            padding: 0.5rem 0;
            color: var(--text-color);
            display: flex;
            align-items: center;
        }

        .plan-features li i {
            color: var(--accent-color);
            margin-right: 0.5rem;
        }

        .btn {
            display: block;
            background: var(--accent-color);
            color: white;
            text-align: center;
            padding: 1rem;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: #2980b9;
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
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="index.html" class="logo">Star<span>FitnessClub</span></a>
        <div class="nav-links">
            <a href="user_page.php"><i class="fas fa-user"></i> Profile</a>
            <a href="edit_profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
            <p>Choose the perfect fitness plan for your goals</p>
        </div>

        <div class="plans-grid">
            <!-- Basic Plan -->
            <div class="plan-card">
                <h2>Basic Plan</h2>
                <div class="price">₹1000/month</div>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i> Smart Workout Plans</li>
                    <li><i class="fas fa-check"></i> At Home Workouts</li>
                    <li><i class="fas fa-check"></i> Basic Exercise Library</li>
                    <li><i class="fas fa-check"></i> Progress Tracking</li>
                </ul>
                <a href="#" class="btn">Select Plan</a>
            </div>

            <!-- Pro Plan -->
            <div class="plan-card">
                <h2>Pro Plan</h2>
                <div class="price">₹1500/month</div>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i> All Basic Features</li>
                    <li><i class="fas fa-check"></i> Advanced Workout Plans</li>
                    <li><i class="fas fa-check"></i> Nutrition Guidelines</li>
                    <li><i class="fas fa-check"></i> Weekly Progress Reports</li>
                    <li><i class="fas fa-check"></i> Community Access</li>
                </ul>
                <a href="#" class="btn">Select Plan</a>
            </div>

            <!-- Premium Plan -->
            <div class="plan-card">
                <h2>Premium Plan</h2>
                <div class="price">₹2000/month</div>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i> All Pro Features</li>
                    <li><i class="fas fa-check"></i> ELITE GYMs Access</li>
                    <li><i class="fas fa-check"></i> Personal Training</li>
                    <li><i class="fas fa-check"></i> Custom Meal Plans</li>
                    <li><i class="fas fa-check"></i> 24/7 Support</li>
                    <li><i class="fas fa-check"></i> Premium Classes</li>
                </ul>
                <a href="#" class="btn">Select Plan</a>
            </div>
        </div>

        <a href="user_page.php" class="back-link">Back to Profile</a>
    </div>
</body>
</html>