<?php
session_start();
include('../config.php');

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])){
    header('location:admin_login.php');
    exit();
}

// Handle equipment deletion
if(isset($_GET['delete'])) {
    $equipment_id = $_GET['delete'];
    $delete_query = "DELETE FROM equipment WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $equipment_id);
    $stmt->execute();
}

// Handle equipment addition/update
if(isset($_POST['submit'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $condition = $_POST['condition'];
    $purchase_date = $_POST['purchase_date'];
    $maintenance_date = $_POST['maintenance_date'];
    $status = $_POST['status'];

    if(isset($_POST['equipment_id'])) {
        // Update existing equipment
        $equipment_id = $_POST['equipment_id'];
        $update_query = "UPDATE equipment SET name=?, category=?, quantity=?, condition_status=?, purchase_date=?, last_maintenance=?, status=? WHERE id=?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssssssi", $name, $category, $quantity, $condition, $purchase_date, $maintenance_date, $status, $equipment_id);
    } else {
        // Add new equipment
        $insert_query = "INSERT INTO equipment (name, category, quantity, condition_status, purchase_date, last_maintenance, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sssssss", $name, $category, $quantity, $condition, $purchase_date, $maintenance_date, $status);
    }
    $stmt->execute();
}

// Fetch all equipment
$query = "SELECT * FROM equipment ORDER BY name ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Equipment - StarFitnessClub</title>
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

        .btn {
            padding: 10px 20px;
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

        .btn-danger {
            background: var(--accent-color);
            color: white;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .table th, .table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
        }

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
            width: 90%;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }

        .status-active {
            background: #e1f6e1;
            color: #2ecc71;
        }

        .status-maintenance {
            background: #fff3e0;
            color: #f39c12;
        }

        .status-inactive {
            background: #ffe0e0;
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include('sidebar.php'); ?>
        
        <div class="main-content">
            <div class="header">
                <h2>Manage Equipment</h2>
                <button onclick="openModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Equipment
                </button>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Condition</th>
                        <th>Purchase Date</th>
                        <th>Last Maintenance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($equipment = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($equipment['name']); ?></td>
                        <td><?php echo htmlspecialchars($equipment['category']); ?></td>
                        <td><?php echo htmlspecialchars($equipment['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($equipment['condition_status']); ?></td>
                        <td><?php echo htmlspecialchars($equipment['purchase_date']); ?></td>
                        <td><?php echo htmlspecialchars($equipment['last_maintenance']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($equipment['status']); ?>">
                                <?php echo htmlspecialchars($equipment['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button onclick="editEquipment(<?php echo $equipment['id']; ?>)" class="btn btn-primary">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteEquipment(<?php echo $equipment['id']; ?>)" class="btn btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Add/Edit Equipment -->
    <div id="equipmentModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle">Add New Equipment</h2>
            <form action="" method="post">
                <input type="hidden" id="equipment_id" name="equipment_id">
                
                <div class="form-group">
                    <label for="name">Equipment Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="Cardio">Cardio</option>
                        <option value="Strength">Strength</option>
                        <option value="Free Weights">Free Weights</option>
                        <option value="Accessories">Accessories</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" required>
                </div>

                <div class="form-group">
                    <label for="condition">Condition</label>
                    <select id="condition" name="condition" required>
                        <option value="Excellent">Excellent</option>
                        <option value="Good">Good</option>
                        <option value="Fair">Fair</option>
                        <option value="Poor">Poor</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="purchase_date">Purchase Date</label>
                    <input type="date" id="purchase_date" name="purchase_date" required>
                </div>

                <div class="form-group">
                    <label for="maintenance_date">Last Maintenance Date</label>
                    <input type="date" id="maintenance_date" name="maintenance_date" required>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="Active">Active</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <button type="submit" name="submit" class="btn btn-primary">Save Equipment</button>
                <button type="button" onclick="closeModal()" class="btn btn-danger">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('modalTitle').textContent = 'Add New Equipment';
            document.getElementById('equipment_id').value = '';
            document.getElementById('equipmentModal').style.display = 'block';
            // Clear form fields
            document.getElementById('name').value = '';
            document.getElementById('category').value = 'Cardio';
            document.getElementById('quantity').value = '';
            document.getElementById('condition').value = 'Excellent';
            document.getElementById('purchase_date').value = '';
            document.getElementById('maintenance_date').value = '';
            document.getElementById('status').value = 'Active';
        }

        function closeModal() {
            document.getElementById('equipmentModal').style.display = 'none';
        }

        function editEquipment(equipmentId) {
            // Fetch equipment details and populate modal
            fetch(`get_equipment.php?id=${equipmentId}`)
                .then(response => response.json())
                .then(equipment => {
                    document.getElementById('modalTitle').textContent = 'Edit Equipment';
                    document.getElementById('equipment_id').value = equipment.id;
                    document.getElementById('name').value = equipment.name;
                    document.getElementById('category').value = equipment.category;
                    document.getElementById('quantity').value = equipment.quantity;
                    document.getElementById('condition').value = equipment.condition_status;
                    document.getElementById('purchase_date').value = equipment.purchase_date;
                    document.getElementById('maintenance_date').value = equipment.last_maintenance;
                    document.getElementById('status').value = equipment.status;
                    document.getElementById('equipmentModal').style.display = 'block';
                });
        }

        function deleteEquipment(equipmentId) {
            if(confirm('Are you sure you want to delete this equipment?')) {
                window.location.href = `?delete=${equipmentId}`;
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('equipmentModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>