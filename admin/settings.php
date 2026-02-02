<?php
session_start();
include('../config.php');

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])){
    header('location:admin_login.php');
    exit();
}

// Handle form submission
if(isset($_POST['update_profile'])) {
    $admin_id = $_SESSION['admin_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    
    // Verify current password
    $verify_query = "SELECT admin_password FROM admin_login WHERE admin_id = ?";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    
    if($current_password === $admin['admin_password'] || md5($current_password) === $admin['admin_password']) {
        // Update profile
        $update_query = "UPDATE admin_login SET admin_name = ?, admin_email = ?";
        $params = [$name, $email];
        
        // If new password is provided, update it
        if(!empty($new_password)) {
            $hashed_password = md5($new_password);
            $update_query .= ", admin_password = ?";
            $params[] = $hashed_password;
        }
        
        $update_query .= " WHERE admin_id = ?";
        $params[] = $admin_id;
        
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param(str_repeat("s", count($params)), ...$params);
        
        if($stmt->execute()) {
            $_SESSION['admin_name'] = $name;
            $_SESSION['admin_email'] = $email;
            $success_msg = "Profile updated successfully!";
        } else {
            $error_msg = "Error updating profile!";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - StarFitnessClub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --text-color: #333;
            --light-bg: #f5f6fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            background: var(--light-bg);
        }

        .settings-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--secondary-color);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Additional styles for better UI */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 24px;
            color: var(--primary-color);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }

        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include('sidebar.php'); ?>
        
        <div class="main-content">
            <div class="settings-container">
                <div class="page-header">
                    <h2 class="page-title">Account Settings</h2>
                </div>

                <?php if(isset($success_msg)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($error_msg)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <h3 style="margin-bottom: 20px;">Profile Settings</h3>
                    <form action="" method="POST">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" value="<?php echo $_SESSION['admin_name']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" value="<?php echo $_SESSION['admin_email']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" required>
                        </div>

                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password">
                            <div class="password-requirements">
                                Leave blank to keep your current password
                            </div>
                        </div>

                        <button type="submit" name="update_profile" class="btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

