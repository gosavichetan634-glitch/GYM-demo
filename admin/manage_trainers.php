<?php
session_start();
include('../config.php');

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])){
    header('location:admin_login.php');
    exit();
}

// Handle trainer addition/update
if(isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $specialization = $_POST['specialization'];
    $experience = $_POST['experience'];
    $status = $_POST['status'];

    if(isset($_POST['trainer_id']) && !empty($_POST['trainer_id'])) {
        // Update existing trainer
        $trainer_id = $_POST['trainer_id'];
        $update_query = "UPDATE trainers SET name=?, email=?, phone=?, specialization=?, experience=?, status=? WHERE id=?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssssi", $name, $email, $phone, $specialization, $experience, $status, $trainer_id);
    } else {
        // Add new trainer
        $insert_query = "INSERT INTO trainers (name, email, phone, specialization, experience, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssssss", $name, $email, $phone, $specialization, $experience, $status);
    }

    if($stmt->execute()) {
        $_SESSION['success_message'] = "Trainer saved successfully!";
        header('Location: manage_trainers.php');
        exit();
    } else {
        $_SESSION['error_message'] = "Error saving trainer: " . $conn->error;
    }
}

// Handle trainer deletion
if(isset($_GET['delete'])) {
    $trainer_id = $_GET['delete'];
    $delete_query = "DELETE FROM trainers WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $trainer_id);
    if($stmt->execute()) {
        $_SESSION['success_message'] = "Trainer deleted successfully!";
        header('Location: manage_trainers.php');
        exit();
    }
}

// Display success/error messages if they exist
if(isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
if(isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}

// Fetch all trainers
$query = "SELECT * FROM trainers ORDER BY name ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Trainers - StarFitnessClub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Reuse existing styles from admin_dashboard.php */
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
                <h2>Manage Trainers</h2>
                <button onclick="openModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Trainer
                </button>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Specialization</th>
                        <th>Experience</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($trainer = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($trainer['name']); ?></td>
                        <td><?php echo htmlspecialchars($trainer['email']); ?></td>
                        <td><?php echo htmlspecialchars($trainer['phone']); ?></td>
                        <td><?php echo htmlspecialchars($trainer['specialization']); ?></td>
                        <td><?php echo htmlspecialchars($trainer['experience']); ?></td>
                        <td>
                            <span class="status-badge <?php echo $trainer['status'] == 'Active' ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo htmlspecialchars($trainer['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button onclick="editTrainer(<?php echo $trainer['id']; ?>)" class="btn btn-primary">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteTrainer(<?php echo $trainer['id']; ?>)" class="btn btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Add/Edit Trainer -->
    <div id="trainerModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle">Add New Trainer</h2>
            <form action="" method="post">
                <input type="hidden" id="trainer_id" name="trainer_id">
                
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>

                <div class="form-group">
                    <label for="specialization">Specialization</label>
                    <input type="text" id="specialization" name="specialization" required>
                </div>

                <div class="form-group">
                    <label for="experience">Experience (years)</label>
                    <input type="number" id="experience" name="experience" required>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <button type="submit" name="submit" class="btn btn-primary">Save Trainer</button>
                <button type="button" onclick="closeModal()" class="btn btn-danger">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('modalTitle').textContent = 'Add New Trainer';
            document.getElementById('trainer_id').value = '';
            document.getElementById('trainerModal').style.display = 'block';
            // Clear form fields
            document.getElementById('name').value = '';
            document.getElementById('email').value = '';
            document.getElementById('phone').value = '';
            document.getElementById('specialization').value = '';
            document.getElementById('experience').value = '';
            document.getElementById('status').value = 'Active';
        }

        function closeModal() {
            document.getElementById('trainerModal').style.display = 'none';
        }

        function editTrainer(trainerId) {
            // Fetch trainer details and populate modal
            fetch(`get_trainer.php?id=${trainerId}`)
                .then(response => response.json())
                .then(trainer => {
                    document.getElementById('modalTitle').textContent = 'Edit Trainer';
                    document.getElementById('trainer_id').value = trainer.id;
                    document.getElementById('name').value = trainer.name;
                    document.getElementById('email').value = trainer.email;
                    document.getElementById('phone').value = trainer.phone;
                    document.getElementById('specialization').value = trainer.specialization;
                    document.getElementById('experience').value = trainer.experience;
                    document.getElementById('status').value = trainer.status;
                    document.getElementById('trainerModal').style.display = 'block';
                });
        }

        function deleteTrainer(trainerId) {
            if(confirm('Are you sure you want to delete this trainer?')) {
                window.location.href = `?delete=${trainerId}`;
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('trainerModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>


