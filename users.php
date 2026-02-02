<?php
include('config.php');

$result = mysqli_query($conn, "SELECT * FROM user_form");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="dashboard">
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="index.php">Dashboard</a>
        <a href="orders.php">View Orders</a>
    </div>
    <div class="main-content">
        <h2>All Users</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>User Type</th>
                <th>Action</th>
            </tr>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['user_type'] ?></td>
                <td><a href="delete_user.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this user?')">Delete</a></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</body>
</html>
