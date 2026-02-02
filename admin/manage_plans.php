<?php
session_start();
include('../config.php');

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])){
    header('location:admin_login.php');
    exit();
}

// Handle plan deletion
if(isset($_GET['delete'])) {
    $plan_id = $_GET['delete'];
    $delete_query = "DELETE FROM membership_plans WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $plan_id);
    $stmt->execute();
}

// Handle plan addition/update
if(isset($_POST['submit'])) {
    $plan_name = $_POST['plan_name'];
    $duration = $_POST['duration'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $features = $_POST['features'];

    if(isset($_POST['plan_id'])) {
        // Update existing plan
        $plan_id = $_POST['plan_id'];
        $query = "UPDATE membership_plans SET plan_name=?, duration=?, price=?, description=?, features=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssdssi", $plan_name, $duration, $price, $description, $features, $plan_id);
    } else {
        // Add new plan
        $query = "INSERT INTO membership_plans (plan_name, duration, price, description, features) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssdss", $plan_name, $duration, $price, $description, $features);
    }
    $stmt->execute();
}

// Fetch all membership plans
$query = "SELECT * FROM membership_plans ORDER BY price ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Membership Plans - StarFitnessClub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --text-color: #333;
            --light-bg: #f5f6fa;
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

        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .plan-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .plan-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .plan-name {
            font-size: 20px;
            color: var(--primary-color);
            font-weight: bold;
        }

        .plan-price {
            font-size: 24px;
            color: var(--secondary-color);
            font-weight: bold;
        }

        .plan-duration {
            color: #666;
            margin-bottom: 10px;
        }

        .plan-description {
            margin: 15px 0;
            color: var(--text-color);
        }

        .plan-features {
            list-style: none;
            padding: 0;
        }

        .plan-features li {
            margin: 5px 0;
            color: var(--text-color);
        }

        .plan-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: var(--secondary-color);
            color: white;
        }

        .btn-delete {
            background: var(--accent-color);
            color: white;
        }

        .add-plan-btn {
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            margin: 50px auto;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-color);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include('sidebar.php'); ?>
        
        <div class="main-content">
            <div class="header">
                <h2>Manage Membership Plans</h2>
                <button onclick="openModal()" class="add-plan-btn">
                    <i class="fas fa-plus"></i> Add New Plan
                </button>
            </div>

            <div class="plans-grid">
                <?php while($plan = $result->fetch_assoc()): ?>
                    <div class="plan-card">
                        <div class="plan-header">
                            <div class="plan-name"><?php echo htmlspecialchars($plan['plan_name']); ?></div>
                            <div class="plan-price">₹<?php echo number_format($plan['price']); ?></div>
                        </div>
                        <div class="plan-duration"><?php echo htmlspecialchars($plan['duration']); ?> month</div>
                        <div class="plan-description"><?php echo htmlspecialchars($plan['description']); ?></div>
                        <ul class="plan-features">
                            <?php 
                            $features = explode("\n", $plan['features']);
                            foreach($features as $feature): 
                            ?>
                                <li><i class="fas fa-check"></i> <?php echo htmlspecialchars($feature); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="plan-actions">
                            <button onclick="editPlan(<?php echo $plan['id']; ?>)" class="btn btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button onclick="deletePlan(<?php echo $plan['id']; ?>)" class="btn btn-delete">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Modal for Add/Edit Plan -->
    <div id="planModal" class="modal">
        <div class="modal-content">
            <h3>Add/Edit Membership Plan</h3>
            <form action="" method="post">
                <input type="hidden" name="plan_id" id="plan_id">
                
                <div class="form-group">
                    <label for="plan_name">Plan Name</label>
                    <input type="text" id="plan_name" name="plan_name" required>
                </div>

                <div class="form-group">
                    <label for="duration">Duration (days)</label>
                    <input type="number" id="duration" name="duration" required>
                </div>

                <div class="form-group">
                    <label for="price">Price (₹)</label>
                    <input type="number" id="price" name="price" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" required></textarea>
                </div>

                <div class="form-group">
                    <label for="features">Features (one per line)</label>
                    <textarea id="features" name="features" rows="5" required></textarea>
                </div>

                <div class="form-group">
                    <button type="submit" name="submit" class="btn btn-edit">Save Plan</button>
                    <button type="button" onclick="closeModal()" class="btn btn-delete">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('planModal').style.display = 'block';
            document.getElementById('plan_id').value = '';
            document.getElementById('plan_name').value = '';
            document.getElementById('duration').value = '';
            document.getElementById('price').value = '';
            document.getElementById('description').value = '';
            document.getElementById('features').value = '';
        }

        function closeModal() {
            document.getElementById('planModal').style.display = 'none';
        }

        function editPlan(planId) {
            // Fetch plan details and populate modal
            fetch(`get_plan.php?id=${planId}`)
                .then(response => response.json())
                .then(plan => {
                    document.getElementById('plan_id').value = plan.id;
                    document.getElementById('plan_name').value = plan.plan_name;
                    document.getElementById('duration').value = plan.duration;
                    document.getElementById('price').value = plan.price;
                    document.getElementById('description').value = plan.description;
                    document.getElementById('features').value = plan.features;
                    openModal();
                });
        }

        function deletePlan(planId) {
            if(confirm('Are you sure you want to delete this plan?')) {
                window.location.href = `?delete=${planId}`;
            }
        }
    </script>
</body>
</html>