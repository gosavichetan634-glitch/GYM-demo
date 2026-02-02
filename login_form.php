<?php
include('config.php');
session_start();

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, name, password, user_type FROM user_form WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $db_password, $user_type);
        $stmt->fetch();

        // Check password (plain or md5)
        if ($password === $db_password || md5($password) === $db_password) {
            // Set session variables
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_type'] = $user_type;

            // Redirect based on user_type
            if ($user_type === 'admin') {
                header("Location: admin/admin_dashboard.php");
                exit();
            } elseif ($user_type === 'user') {
                header("Location: user_page.php");
                exit();
            } else {
                $error[] = 'Unknown user type!';
            }
        } else {
            $error[] = 'Incorrect email or password!';
        }
    } else {
        $error[] = 'Incorrect email or password!';
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Form</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

<div class="form-container">
    <form action="" method="post">
        <h3>Login Now</h3>
        <?php
        if (isset($error)) {
            foreach ($error as $err) {
                echo '<span class="error-msg">' . htmlspecialchars($err) . '</span>';
            }
        }
        ?>
        <input type="email" name="email" required placeholder="Enter your email">
        <input type="password" name="password" required placeholder="Enter your password">
        <input type="submit" name="submit" value="Login Now" class="form-btn">
        <p>Don't have an account? <a href="register_form.php">Register now</a></p>
    </form>
</div>

</body>
</html>


