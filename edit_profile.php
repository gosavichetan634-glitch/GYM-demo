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

// Fetch current user data
$stmt = $conn->prepare("SELECT name, email FROM user_form WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Handle form submission
if(isset($_POST['update_profile'])) {
    $new_name = mysqli_real_escape_string($conn, $_POST['name']);
    $new_email = mysqli_real_escape_string($conn, $_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password
    $verify_stmt = $conn->prepare("SELECT password FROM user_form WHERE id = ?");
    $verify_stmt->bind_param("i", $user_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    $current_data = $verify_result->fetch_assoc();

    if(md5($current_password) === $current_data['password']) {
        // Update name and email
        $update_stmt = $conn->prepare("UPDATE user_form SET name = ?, email = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $new_name, $new_email, $user_id);
        
        // If password change is requested
        if(!empty($new_password)) {
            if($new_password === $confirm_password) {
                $hashed_password = md5($new_password);
                $update_stmt = $conn->prepare("UPDATE user_form SET name = ?, email = ?, password = ? WHERE id = ?");
                $update_stmt->bind_param("sssi", $new_name, $new_email, $hashed_password, $user_id);
            } else {
                $error_msg = "New passwords do not match!";
            }
        }

        if(empty($error_msg)) {
            if($update_stmt->execute()) {
                $_SESSION['user_name'] = $new_name;
                $_SESSION['user_email'] = $new_email;
                $success_msg = "Profile updated successfully!";
                $user_data['name'] = $new_name;
                $user_data['email'] = $new_email;
            } else {
                $error_msg = "Failed to update profile!";
            }
        }
    } else {
        $error_msg = "Current password is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - StarFitnessClub</title>
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
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-title {
            text-align: center;
            color: var(--text-color);
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            outline: none;
        }

        .password-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #ddd;
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
            transition: background 0.3s ease;
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
        <h2 class="form-title">Edit Profile</h2>
        
        <?php if($success_msg): ?>
            <div class="success-msg"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <?php if($error_msg): ?>
            <div class="error-msg"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
            </div>

            <div class="password-section">
                <h3>Change Password</h3>
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password (leave blank if no change)</label>
                    <input type="password" class="form-control" id="new_password" name="new_password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                </div>
            </div>

            <button type="submit" name="update_profile" class="btn-submit">Update Profile</button>
        </form>

        <a href="user_page.php" class="back-link">Back to Profile</a>
    </div>
</body>
</html>