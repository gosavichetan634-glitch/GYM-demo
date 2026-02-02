<?php
session_start();
include('../config.php');

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])){
    header('location:admin_login.php');
    exit();
}

// Handle class deletion
if(isset($_GET['delete'])) {
    $class_id = $_GET['delete'];
    $delete_query = "DELETE FROM fitness_classes WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
}

// Handle class addition/update
if(isset($_POST['submit'])) {
    $class_name = $_POST['class_name'];
    $trainer_id = $_POST['trainer_id'];
    $schedule_time = $_POST['schedule_time'];
    $duration = $_POST['duration'];
    $capacity = $_POST['capacity'];
    $description = $_POST['description'];

    if(isset($_POST['class_id'])) {
        // Update existing class
        $class_id = $_POST['class_id'];
        $update_query = "UPDATE fitness_classes SET 
                        class_name = ?, 
                        trainer_id = ?,
                        schedule_time = ?,
                        duration = ?,
                        capacity = ?,
                        description = ?
                        WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sissiis", $class_name, $trainer_id, $schedule_time, $duration, $capacity, $description, $class_id);
    } else {
        // Add new class
        $insert_query = "INSERT INTO fitness_classes (class_name, trainer_id, schedule_time, duration, capacity, description) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sissss", $class_name, $trainer_id, $schedule_time, $duration, $capacity, $description);
    }
    $stmt->execute();
}

// Fetch all trainers for dropdown
$trainers_query = "SELECT id, name FROM trainers WHERE status = 'active'";
$trainers_result = $conn->query($trainers_query);
if (!$trainers_result) {
    die("Error fetching trainers: " . $conn->error);
}

// Fetch all classes
$classes_query = "SELECT c.*, t.name as trainer_name 
                 FROM fitness_classes c 
                 LEFT JOIN trainers t ON c.trainer_id = t.id 
                 ORDER BY c.schedule_time ASC";
$classes_result = $conn->query($classes_query);
if (!$classes_result) {
    die("Error fetching classes: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Classes - StarFitnessClub</title>
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
        }

        .btn-add {
            background: var(--secondary-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .classes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .class-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .class-card h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .class-info {
            margin: 15px 0;
            color: var(--text-color);
        }

        .class-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-edit, .btn-delete {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-edit {
            background: var(--secondary-color);
            color: white;
        }

        .btn-delete {
            background: var(--accent-color);
            color: white;
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

        .form-group input, .form-group select, .form-group textarea {
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
                <h2>Manage Fitness Classes</h2>
                <button onclick="openModal()" class="btn-add">
                    <i class="fas fa-plus"></i> Add New Class
                </button>
            </div>

            <div class="classes-grid">
                <?php while($class = $classes_result->fetch_assoc()): ?>
                    <div class="class-card">
                        <h3><?php echo htmlspecialchars($class['class_name']); ?></h3>
                        <div class="class-info">
                            <p><i class="fas fa-user"></i> Trainer: <?php echo htmlspecialchars($class['trainer_name']); ?></p>
                            <p><i class="fas fa-clock"></i> Time: <?php echo htmlspecialchars($class['schedule_time']); ?></p>
                            <p><i class="fas fa-hourglass-half"></i> Duration: <?php echo htmlspecialchars($class['duration']); ?> minutes</p>
                            <p><i class="fas fa-users"></i> Capacity: <?php echo htmlspecialchars($class['capacity']); ?> people</p>
                        </div>
                        <div class="class-actions">
                            <button onclick="editClass(<?php echo $class['id']; ?>)" class="btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button onclick="deleteClass(<?php echo $class['id']; ?>)" class="btn-delete">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Modal for Add/Edit Class -->
            <div id="classModal" class="modal">
                <div class="modal-content">
                    <h3 id="modalTitle">Add New Class</h3>
                    <form action="" method="post">
                        <input type="hidden" name="class_id" id="class_id">
                        
                        <div class="form-group">
                            <label>Class Name</label>
                            <input type="text" name="class_name" id="class_name" required>
                        </div>

                        <div class="form-group">
                            <label>Trainer</label>
                            <select name="trainer_id" id="trainer_id" required>
                                <?php while($trainer = $trainers_result->fetch_assoc()): ?>
                                    <option value="<?php echo $trainer['id']; ?>">
                                        <?php echo htmlspecialchars($trainer['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Schedule Time</label>
                            <input type="datetime-local" name="schedule_time" id="schedule_time" required>
                        </div>

                        <div class="form-group">
                            <label>Duration (minutes)</label>
                            <input type="number" name="duration" id="duration" required>
                        </div>

                        <div class="form-group">
                            <label>Capacity</label>
                            <input type="number" name="capacity" id="capacity" required>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" id="description" rows="4"></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" name="submit" class="btn-add">Save Class</button>
                            <button type="button" onclick="closeModal()" class="btn-delete">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('classModal').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Add New Class';
            document.getElementById('class_id').value = '';
            // Reset form fields
            document.getElementById('class_name').value = '';
            document.getElementById('trainer_id').selectedIndex = 0;
            document.getElementById('schedule_time').value = '';
            document.getElementById('duration').value = '';
            document.getElementById('capacity').value = '';
            document.getElementById('description').value = '';
        }

        function closeModal() {
            document.getElementById('classModal').style.display = 'none';
        }

        function editClass(classId) {
            // Here you would typically fetch the class details from the server
            // For now, we'll just show the modal
            document.getElementById('classModal').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Edit Class';
            document.getElementById('class_id').value = classId;
        }

        function deleteClass(classId) {
            if(confirm('Are you sure you want to delete this class?')) {
                window.location.href = `?delete=${classId}`;
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('classModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>
