<?php
include('config.php');

// Assume you have an 'orders' table
$result = mysqli_query($conn, "SELECT * FROM orders");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Orders</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="dashboard">
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="index.php">Dashboard</a>
        <a href="users.php">Manage Users</a>
    </div>
    <div class="main-content">
        <h2>All Orders</h2>
        <table>
            <tr>
                <th>Order ID</th>
                <th>User Email</th>
                <th>Product</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['order_id'] ?></td>
                <td><?= $row['user_email'] ?></td>
                <td><?= $row['product_name'] ?></td>
                <td><?= $row['amount'] ?></td>
                <td><?= $row['order_date'] ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</body>
</html>
