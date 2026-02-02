<?php
session_start();
include('../config.php');

if(isset($_POST['submit'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    // पहिले plain password घ्या
    $password = $_POST['password'];

    // Database मधून admin ची माहिती काढा (password hash न करता)
    $select = "SELECT * FROM admin_login WHERE admin_email = ?";
    $stmt = $conn->prepare($select);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $admin = $result->fetch_assoc();
        
        /*  */// आता दोन्ही पद्धतींनी password तपासा
        if($password === $admin['admin_password'] || md5($password) === $admin['admin_password']) {
            // Login successful
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['admin_name'];
            $_SESSION['admin_email'] = $admin['admin_email'];
            
            // Last login update करा
            $update = "UPDATE admin_login SET last_login = CURRENT_TIMESTAMP WHERE admin_id = ?";
            $stmt = $conn->prepare($update);
            $stmt->bind_param("i", $admin['admin_id']);
            $stmt->execute();

            header('location:admin_dashboard.php');
            exit();
        } else {
            $error[] = 'पासवर्ड चुकीचा आहे!';
        }
    } else {
        $error[] = 'हा admin email आमच्या system मध्ये नाही!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - StarFitnessClub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        :root {
            --primary-color: #2c3e50;
            --accent-color: #45ffca;
            --error-color: #e74c3c;
            --text-color: #2c3e50;
            --light-gray: #f5f6fa;
            --white: #ffffff;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            padding: 20px;
        }

        .login-container {
            background: var(--white);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand-logo h1 {
            color: var(--primary-color);
            font-size: 28px;
            font-weight: 600;
        }

        .brand-logo span {
            color: var(--accent-color);
        }

        .brand-logo p {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .form-group input {
            width: 100%;
            padding: 15px 45px;
            border: 2px solid #eee;
            border-radius: 10px;
            font-size: 15px;
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 10px rgba(69, 255, 202, 0.1);
        }

        .error-msg {
            background: #ffd5d5;
            color: var(--error-color);
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #ffbebe;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: var(--accent-color);
            color: var(--primary-color);
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: #3ae6b5;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(69, 255, 202, 0.3);
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: var(--accent-color);
        }

        .divider {
            margin: 30px 0;
            text-align: center;
            position: relative;
        }

        .divider::before {
            content: "";
            position: absolute;
            left: 0;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #eee;
        }

        .divider::after {
            content: "";
            position: absolute;
            right: 0;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #eee;
        }

        .divider span {
            background: var(--white);
            padding: 0 15px;
            color: #666;
            font-size: 14px;
        }

    </style>
</head>
<body>
    <div class="login-container">
        <div class="brand-logo">
            <h1>Star<span>FitnessClub</span></h1>
            <p>Admin Control Panel</p>
        </div>

        <form action="" method="post">
            <?php
            if(isset($error)){
                foreach($error as $error){
                    echo '<div class="error-msg">
                            <i class="fas fa-exclamation-circle"></i> 
                            '.htmlspecialchars($error).'
                          </div>';
                }
            }
            ?>

            <div class="form-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" required placeholder="Admin Email">
            </div>

            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" required placeholder="Password">
            </div>

            <button type="submit" name="submit" class="submit-btn">
                <i class="fas fa-sign-in-alt"></i> Login to Dashboard
            </button>

            <div class="divider">
                <span>or</span>
            </div>

            <div class="back-link">
                <a href="../index.html">
                    <i class="fas fa-arrow-left"></i> Back to Website
                </a>
            </div>
        </form>
    </div>
</body>
</html>


