<?php
include('config.php');

if (isset($_POST['submit'])) {
    try {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $pass = md5($_POST['password']); // Using MD5 for password hashing
        $cpass = md5($_POST['cpassword']);
        $user_type = 'user'; // Fixed user type

        $select = "SELECT * FROM user_form WHERE email = ?";
        $stmt = $conn->prepare($select);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error[] = 'User already exists!';
        } else {
            if ($pass != $cpass) {
                $error[] = 'Passwords do not match!';
            } else {
                $insert = "INSERT INTO user_form(name, email, password, user_type) VALUES(?, ?, ?, ?)";
                $stmt = $conn->prepare($insert);
                $stmt->bind_param("ssss", $name, $email, $pass, $user_type);
                $stmt->execute();
                header('location:login_form.php');
                exit();
            }
        }
    } catch (Exception $e) {
        $error[] = 'Registration failed: ' . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
<div class="form-container">
    <form action="" method="post">
        <h3>Register Now</h3>
        <?php
        if (isset($error)) {
            foreach ($error as $errorMsg) {
                echo '<span class="error-msg">' . htmlspecialchars($errorMsg) . '</span>';
            }
        }
        ?>
        <input type="text" name="name" required placeholder="Enter your name">
        <input type="email" name="email" required placeholder="Enter your email">
        <input type="password" name="password" required placeholder="Enter your password">
        <input type="password" name="cpassword" required placeholder="Confirm your password">
        <input type="submit" name="submit" value="Register Now" class="form-btn">
        <p>Already have an account? <a href="login_form.php">Login now</a></p>
    </form>
</div>
</body>
</html>


