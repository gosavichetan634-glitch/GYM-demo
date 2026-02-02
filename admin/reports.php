<?php
session_start();
include('../config.php');

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])){
    header('location:admin_login.php');
    exit();
}

// Error handling function
function handleQueryError($conn, $query) {
    if (!$result = $conn->query($query)) {
        // Log the error
        error_log("Query failed: " . $conn->error);
        return false;
    }
    return $result;
}

// Get total members count
$members_query = "SELECT COUNT(*) as total FROM members";
$members_result = handleQueryError($conn, $members_query);
$total_members = $members_result ? $members_result->fetch_assoc()['total'] : 0;

// Get total revenue
$revenue_query = "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE MONTH(payment_date) = MONTH(CURRENT_DATE())";
$revenue_result = handleQueryError($conn, $revenue_query);
$monthly_revenue = $revenue_result ? $revenue_result->fetch_assoc()['total'] : 0;

// Get active trainers count
$trainers_query = "SELECT COUNT(*) as total FROM trainers WHERE status = 'active'";
$trainers_result = handleQueryError($conn, $trainers_query);
$active_trainers = $trainers_result ? $trainers_result->fetch_assoc()['total'] : 0;

// Get membership plan distribution
$plans_query = "SELECT p.plan_name, COUNT(m.id) as count 
                FROM membership_plans p 
                LEFT JOIN members m ON p.id = m.plan_id 
                GROUP BY p.id, p.plan_name";
$plans_result = handleQueryError($conn, $plans_query);

// Get recent payments
$payments_query = "SELECT p.*, m.name as member_name 
                  FROM payments p 
                  JOIN members m ON p.member_id = m.id 
                  ORDER BY payment_date DESC 
                  LIMIT 5";
$payments_result = handleQueryError($conn, $payments_query);

// Initialize arrays for chart data
$labels = [];
$data = [];
if ($plans_result) {
    while($plan = $plans_result->fetch_assoc()) {
        $labels[] = $plan['plan_name'];
        $data[] = (int)$plan['count'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - StarFitnessClub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .recent-payments {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include('sidebar.php'); ?>
        
        <div class="main-content">
            <div class="header">
                <h2>Reports & Analytics</h2>
                <div class="date-filter">
                    <select id="dateRange" onchange="updateReports()">
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month" selected>This Month</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Members</h3>
                    <div class="number"><?php echo $total_members; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Monthly Revenue</h3>
                    <div class="number">₹<?php echo number_format($monthly_revenue); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Active Trainers</h3>
                    <div class="number"><?php echo $active_trainers; ?></div>
                </div>
            </div>

            <div class="charts-container">
                <div class="chart-card">
                    <h3>Membership Distribution</h3>
                    <canvas id="membershipChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Revenue Trend</h3>
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="recent-payments">
                <h3>Recent Payments</h3>
                <?php if ($payments_result && $payments_result->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Member Name</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($payment = $payments_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['member_name']); ?></td>
                            <td>₹<?php echo number_format($payment['amount']); ?></td>
                            <td><?php echo date('d M Y', strtotime($payment['payment_date'])); ?></td>
                            <td>
                                <span class="status <?php echo strtolower($payment['status']); ?>">
                                    <?php echo $payment['status']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>No recent payments found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Membership Distribution Chart
        const membershipData = {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                data: <?php echo json_encode($data); ?>,
                backgroundColor: [
                    '#3498db',
                    '#2ecc71',
                    '#f1c40f',
                    '#e74c3c',
                    '#9b59b6'
                ]
            }]
        };

        new Chart(document.getElementById('membershipChart'), {
            type: 'pie',
            data: membershipData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Revenue Trend Chart
        const revenueData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Revenue',
                data: [30000, 35000, 32000, 40000, 38000, 42000],
                borderColor: '#3498db',
                tension: 0.4
            }]
        };

        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: revenueData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        function updateReports() {
            const dateRange = document.getElementById('dateRange').value;
            // Implement AJAX update logic here
        }
    </script>
</body>
</html>
