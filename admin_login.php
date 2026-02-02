<?php
include('config.php');

if(isset($_POST['submit'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $select = "SELECT * FROM user_form WHERE email = ? AND user_type = 'admin'";
    $stmt = $conn->prepare($select);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        if($password === $row['password'] || md5($password) === $row['password']){
            header('location:admin_dashboard.php');
            exit();
        } else {
            $error[] = 'Incorrect password!';
        }
    } else {
        $error[] = 'Admin not found!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="form-container">
        <form action="" method="post">
            <h3>Admin Login</h3>
            <?php
            if(isset($error)){
                foreach($error as $error){
                    echo '<span class="error-msg">'.htmlspecialchars($error).'</span>';
                };
            };
            ?>
            <input type="email" name="email" required placeholder="Enter admin email">
            <input type="password" name="password" required placeholder="Enter admin password">
            <input type="submit" name="submit" value="Login Now" class="form-btn">
            <p>Back to <a href="admin_page.php">User Panel</a></p>
        </form>
    </div>
</body>
</html>