<?php
session_start();
include('../config.php');

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])){
    header('location:admin_login.php');
    exit();
}

// Fetch all payments
$query = "SELECT p.*, m.name as member_name, pl.plan_name 
          FROM payments p 
          JOIN members m ON p.member_id = m.id 
          JOIN membership_plans pl ON m.plan_id = pl.id 
          ORDER BY p.payment_date DESC";
$result = $conn->query($query);

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="payments_report.xls"');

// Create Excel content
echo "Member Name\tPlan\tAmount\tPayment Date\tPayment Method\tStatus\n";

while($payment = $result->fetch_assoc()) {
    echo $payment['member_name'] . "\t";
    echo $payment['plan_name'] . "\t";
    echo $payment['amount'] . "\t";
    echo date('d/m/Y', strtotime($payment['payment_date'])) . "\t";
    echo $payment['payment_method'] . "\t";
    echo $payment['status'] . "\n";
}