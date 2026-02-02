<?php
include('config.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    mysqli_query($conn, "DELETE FROM user_form WHERE id = $id");
}

header("Location: users.php");
exit();
