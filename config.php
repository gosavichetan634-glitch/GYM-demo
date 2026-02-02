<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'user_db';

try {
    $conn = mysqli_connect($host, $username, $password, $database);
    if (!$conn) {
        throw new Exception(mysqli_connect_error());
    }
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

