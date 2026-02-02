<?php
include('config.php');
session_start();

if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check in members table first
    $stmt = $conn->prepare("SELECT * FROM members WHERE email = ? AND status = 'active'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Check both plain password and MD5 hashed password
        if ($password === $row['password'] || md5($password) === $row['password']) {
            // Set session variables for member
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['user_type'] = 'member';

            // Redirect to member dashboard
            header("Location: member_dashboard.php");
            exit();
        } else {
            $error[] = 'Incorrect password!';
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
    <title>Login Form | Star Fitness Club</title>
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
        <p>Back to <a href="admin_page.php">User Panel</a></p>
    </form>
</div>

</body>
</html>

