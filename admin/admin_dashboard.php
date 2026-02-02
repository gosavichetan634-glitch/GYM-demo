<?php
session_start();
include('../config.php');

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])){
    header('location:admin_login.php');
    exit();
}

// Get admin details
$admin_name = $_SESSION['admin_name'];
?>
<!DOCTYPE html>
<html lang="en"><head>
    <meta charset="UTF-8">    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StarFitnessClub</title>    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>        :root {
            --primary-color: #2c3e50;            --secondary-color: #3498db;
            --accent-color: #e74c3c;            --text-color: #333;
            --light-bg: #f5f6fa;        }
        * {
            margin: 0;            padding: 0;
            box-sizing: border-box;            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .dashboard {            display: flex;
            min-height: 100vh;        }
        /* Sidebar Styles */
        .sidebar {            width: 280px;
            background: var(--primary-color);            padding: 20px;
            color: white;        }
        .brand {
            font-size: 24px;            text-align: center;
            padding: 20px 0;            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .brand span {            color: var(--secondary-color);
        }
        .sidebar-menu {            margin-top: 30px;
        }
        .menu-item {            display: flex;
            align-items: center;            padding: 15px;
            color: white;            text-decoration: none;
            border-radius: 8px;            margin-bottom: 10px;
            transition: all 0.3s ease;        }
        .menu-item:hover {
            background: rgba(255,255,255,0.1);        }
        .menu-item.active {
            background: var(--secondary-color);        }
        .menu-item i {
            margin-right: 15px;            font-size: 20px;
        }
        /* Main Content Styles */        .main-content {
            flex: 1;            background: var(--light-bg);
            padding: 30px;        }
        .header {
            display: flex;            justify-content: space-between;
            align-items: center;            margin-bottom: 30px;
            padding: 20px;            background: white;
            border-radius: 10px;            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header h2 {            color: var(--text-color);
        }
        .user-info {            display: flex;
            align-items: center;            gap: 15px;
        }
        .stats-grid {            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));            gap: 20px;
            margin-bottom: 30px;        }
        .stat-card {
            background: white;            padding: 20px;
            border-radius: 10px;            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-card h3 {            color: var(--text-color);
            margin-bottom: 10px;            font-size: 18px;
        }
        .stat-card .number {            font-size: 28px;
            font-weight: bold;            color: var(--secondary-color);
        }
        .recent-section {            background: white;
            padding: 20px;            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);        }
        .recent-section h3 {
            margin-bottom: 20px;            color: var(--text-color);
        }
        .table {            width: 100%;
            border-collapse: collapse;        }
        .table th, .table td {
            padding: 12px;            text-align: left;
            border-bottom: 1px solid #eee;        }
        .table th {
            background: #f8f9fa;            font-weight: 600;
        }
        .btn {            padding: 8px 15px;
            border-radius: 5px;            border: none;
            cursor: pointer;            font-size: 14px;
            transition: all 0.3s ease;        }
        .btn-danger {
            background: var(--accent-color);            color: white;
        }
        .btn-danger:hover {            background: #c0392b;
        }
        .status {            padding: 5px 10px;
            border-radius: 15px;            font-size: 12px;
        }
        .status.active {            background: #e1f6e1;
            color: #2ecc71;        }
        .status.pending {
            background: #fff3e0;            color: #f39c12;
        }    </style>
</head><body>
    <div class="dashboard">        <!-- Sidebar -->
        <div class="sidebar">            <div class="brand">
                Star<span>FitnessClub</span>            </div>
            <div class="sidebar-menu">                <a href="admin_dashboard.php" class="menu-item active">
                    <i class="fas fa-home"></i>                    <span>Dashboard</span>
                </a>                <a href="manage_members.php" class="menu-item">
                    <i class="fas fa-users"></i>                    <span>Members</span>
                </a>                <a href="manage_trainers.php" class="menu-item">
                    <i class="fas fa-dumbbell"></i>                    <span>Trainers</span>
                </a>                <a href="manage_plans.php" class="menu-item">
                    <i class="fas fa-clipboard-list"></i>                    <span>Membership Plans</span>
                                <a href="payments.php" class="menu-item">
                    <i class="fas fa-credit-card"></i>                    <span>Payments</span>
                </a>                <a href="reports.php" class="menu-item">
                    <i class="fas fa-chart-bar"></i>                    <span>Reports</span>
                </a>                <a href="settings.php" class="menu-item">
                    <i class="fas fa-cog"></i>                    <span>Settings</span>
                </a>                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>                    <span>Logout</span>
                </a>            </div>
        </div>
        <!-- Main Content -->        <div class="main-content">
            <!-- Header -->            <div class="header">
                <h2>Dashboard Overview</h2>                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($admin_name); ?></span>
                    <a href="logout.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>            </div>
            <!-- Recent Members -->
            <div class="welcome-message">
                <h1>Welcome to StarFitnessClub Admin Panel</h1>
                <p>Manage your fitness club efficiently with our comprehensive admin dashboard.</p>
                <p>Use the sidebar menu to navigate through different sections.</p>
            </div>
            <style>
            .welcome-message {
                text-align: center;
                padding: 50px;
                background: white;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                margin-top: 20px;
            }
            .welcome-message h1 {
                color: var(--primary-color);
                margin-bottom: 20px;
                font-size: 2.5em;
            }
            .welcome-message p {
                color: var(--text-color);
                font-size: 1.2em;
                line-height: 1.6;
                margin-bottom: 10px;
            }
            </style>
    </div>
</body>

