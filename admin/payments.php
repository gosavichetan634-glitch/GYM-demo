<?php
session_start();
include('../config.php');

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])){
    header('location:admin_login.php');
    exit();
}

// Handle payment status update
if(isset($_POST['update_status'])) {
    $payment_id = $_POST['payment_id'];
    $new_status = $_POST['new_status'];
    
    // First check if the payment exists
    $check_query = "SELECT id FROM payments WHERE id = ?";
    $check_stmt = $conn->prepare($check_query);
    if(!$check_stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $check_stmt->bind_param("i", $payment_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if($check_result->num_rows > 0) {
        $update_query = "UPDATE payments SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        if(!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("si", $new_status, $payment_id);
        if(!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $_SESSION['success_message'] = "Payment status updated successfully!";
    } else {
        $_SESSION['error_message'] = "Payment not found!";
    }
    
    header('Location: payments.php');
    exit();
}

// Handle new payment addition
if(isset($_POST['add_payment'])) {
    $member_id = $_POST['member_id'];
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $status = $_POST['status'];
    
    $insert_query = "INSERT INTO payments (member_id, amount, payment_method, status, payment_date) 
                    VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($insert_query);
    if(!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("idss", $member_id, $amount, $payment_method, $status);
    if(!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    
    $_SESSION['success_message'] = "Payment added successfully!";
    header('Location: payments.php');
    exit();
}

// Display success/error messages
if(isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
if(isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}

// Fetch all members for dropdown
$members_query = "SELECT id, name FROM members";
$members_result = $conn->query($members_query);

// Date range filter
$date_filter = "";
if(isset($_GET['from_date']) && isset($_GET['to_date'])) {
    $from_date = $_GET['from_date'];
    $to_date = $_GET['to_date'];
    $date_filter = " WHERE p.payment_date BETWEEN '$from_date' AND '$to_date'";
}

// Fetch all payments with member details
$payments_query = "SELECT p.*, m.name as member_name, pl.plan_name 
                  FROM payments p 
                  JOIN members m ON p.member_id = m.id 
                  JOIN membership_plans pl ON m.plan_id = pl.id" 
                  . $date_filter . 
                  " ORDER BY p.payment_date DESC";
$payments_result = $conn->query($payments_query);

// Calculate total revenue
$revenue_query = "SELECT SUM(amount) as total FROM payments";
$revenue_result = $conn->query($revenue_query);
$total_revenue = $revenue_result->fetch_assoc()['total'];

// Get pending payments count
$pending_query = "SELECT COUNT(*) as count FROM payments WHERE status = 'Pending'";
$pending_result = $conn->query($pending_query);
$pending_count = $pending_result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - StarFitnessClub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --text-color: #333;
            --light-bg: #f5f6fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            background: var(--light-bg);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 28px;
            font-weight: bold;
            color: var(--secondary-color);
        }

        .payments-table {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }

        .status.completed {
            background: #e1f6e1;
            color: #2ecc71;
        }

        .status.pending {
            background: #fff3e0;
            color: #f39c12;
        }

        .status.failed {
            background: #fee;
            color: #e74c3c;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--secondary-color);
            color: white;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .search-box input {
            padding: 10px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        /* Add new styles for modals */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            background: white;
            width: 90%;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 10px;
            position: relative;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 24px;
            cursor: pointer;
        }

        .filter-section {
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 10px;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .filter-section input[type="date"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .export-btn {
            background: #27ae60;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include('sidebar.php'); ?>
        
        <div class="main-content">
            <div class="header">
                <h2>Payment Management</h2>
                <button onclick="openAddModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Payment
                </button>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Revenue</h3>
                    <div class="number">₹<?php echo number_format($total_revenue); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Pending Payments</h3>
                    <div class="number"><?php echo $pending_count; ?></div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <form action="" method="GET" id="dateFilterForm">
                    <input type="date" name="from_date" value="<?php echo isset($_GET['from_date']) ? $_GET['from_date'] : ''; ?>">
                    <input type="date" name="to_date" value="<?php echo isset($_GET['to_date']) ? $_GET['to_date'] : ''; ?>">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
                <button onclick="exportToExcel()" class="export-btn">
                    <i class="fas fa-file-excel"></i> Export Report
                </button>
            </div>

            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search by member name..." onkeyup="searchTable()">
            </div>

            <div class="payments-table">
                <table class="table" id="paymentsTable">
                    <thead>
                        <tr>
                            <th>Member Name</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Payment Date</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($payment = $payments_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['member_name']); ?></td>
                            <td><?php echo htmlspecialchars($payment['plan_name']); ?></td>
                            <td>₹<?php echo number_format($payment['amount']); ?></td>
                            <td><?php echo date('d M Y', strtotime($payment['payment_date'])); ?></td>
                            <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                            <td>
                                <span class="status <?php echo strtolower($payment['status']); ?>">
                                    <?php echo $payment['status']; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-primary" onclick="viewPaymentDetails(<?php echo $payment['id']; ?>)">
                                    View Details
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Payment Modal -->
    <div id="addPaymentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddModal()">&times;</span>
            <h3>Add New Payment</h3>
            
            <form action="" method="POST">
                <div class="form-group">
                    <label>Select Member</label>
                    <select name="member_id" required>
                        <?php while($member = $members_result->fetch_assoc()): ?>
                            <option value="<?php echo $member['id']; ?>">
                                <?php echo htmlspecialchars($member['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Amount (₹)</label>
                    <input type="number" name="amount" required>
                </div>

                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" required>
                        <option value="Cash">Cash</option>
                        <option value="Card">Card</option>
                        <option value="UPI">UPI</option>
                        <option value="Net Banking">Net Banking</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="Completed">Completed</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>

                <button type="submit" name="add_payment" class="btn btn-primary">
                    Add Payment
                </button>
            </form>
        </div>
    </div>

    <!-- Payment Details Modal -->
    <div id="paymentDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDetailsModal()">&times;</span>
            <h3>Payment Details</h3>
            <div id="paymentDetails"></div>
        </div>
    </div>

    <script>
        function searchTable() {
            var input = document.getElementById("searchInput");
            var filter = input.value.toUpperCase();
            var table = document.getElementById("paymentsTable");
            var tr = table.getElementsByTagName("tr");

            for (var i = 1; i < tr.length; i++) {
                var td = tr[i].getElementsByTagName("td")[0];
                if (td) {
                    var txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        function openAddModal() {
            document.getElementById('addPaymentModal').style.display = 'block';
        }

        function closeAddModal() {
            document.getElementById('addPaymentModal').style.display = 'none';
        }

        function viewPaymentDetails(paymentId) {
            fetch(`get_payment_details.php?id=${paymentId}`)
                .then(response => response.json())
                .then(payment => {
                    const detailsHtml = `
                        <div class="payment-details">
                            <p><strong>Member:</strong> ${payment.member_name}</p>
                            <p><strong>Amount:</strong> ₹${payment.amount}</p>
                            <p><strong>Date:</strong> ${payment.payment_date}</p>
                            <p><strong>Method:</strong> ${payment.payment_method}</p>
                            <p><strong>Status:</strong> ${payment.status}</p>
                        </div>
                        <form action="" method="POST">
                            <input type="hidden" name="payment_id" value="${payment.id}">
                            <select name="new_status">
                                <option value="Completed">Completed</option>
                                <option value="Pending">Pending</option>
                                <option value="Failed">Failed</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary">
                                Update Status
                            </button>
                        </form>
                    `;
                    document.getElementById('paymentDetails').innerHTML = detailsHtml;
                    document.getElementById('paymentDetailsModal').style.display = 'block';
                });
        }

        function closeDetailsModal() {
            document.getElementById('paymentDetailsModal').style.display = 'none';
        }

        function exportToExcel() {
            // Redirect to export script
            window.location.href = 'export_payments.php';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>



