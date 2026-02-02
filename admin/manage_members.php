<?php
session_start();
include('../config.php');

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])){
    header('location:admin_login.php');
    exit();
}

// Handle member addition/update
if(isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = md5($_POST['password']); // Hash the password
    $user_type = 'user'; // Default type for new users

    if(isset($_POST['member_id']) && !empty($_POST['member_id'])) {
        // Update existing member
        $member_id = $_POST['member_id'];
        if(!empty($_POST['password'])) {
            // If password is provided, update it
            $query = "UPDATE user_form SET name=?, email=?, password=? WHERE id=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssi", $name, $email, $password, $member_id);
        } else {
            // If no password provided, don't update password
            $query = "UPDATE user_form SET name=?, email=? WHERE id=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $name, $email, $member_id);
        }
    } else {
        // Add new member
        $query = "INSERT INTO user_form (name, email, password, user_type) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $name, $email, $password, $user_type);
    }

    if($stmt->execute()) {
        $_SESSION['success_message'] = "Member saved successfully!";
        header('Location: manage_members.php');
        exit();
    } else {
        $_SESSION['error_message'] = "Error saving member: " . $conn->error;
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

// Fetch all users (excluding admins)
$members_query = "SELECT * FROM user_form WHERE user_type = 'user' ORDER BY id DESC";
$members_result = $conn->query($members_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members - StarFitnessClub</title>
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
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .members-grid {
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

        .status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }

        .status.active {
            background: #e1f6e1;
            color: #2ecc71;
        }

        .status.inactive {
            background: #fee5e5;
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

        .btn-danger {
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

        .action-buttons {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include('sidebar.php'); ?>
        
        <div class="main-content">
            <div class="header">
                <h2>Manage Members</h2>
                <button onclick="openModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Member
                </button>
            </div>

            <div class="members-grid">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>User Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if($members_result && $members_result->num_rows > 0) {
                            while($member = $members_result->fetch_assoc()): 
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($member['id']); ?></td>
                                <td><?php echo htmlspecialchars($member['name']); ?></td>
                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                <td><?php echo htmlspecialchars($member['user_type']); ?></td>
                                <td>
                                    <button onclick="editMember(<?php echo $member['id']; ?>)" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteMember(<?php echo $member['id']; ?>)" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button onclick="resetPassword(<?php echo $member['id']; ?>)" class="btn btn-sm btn-warning">
                                        <i class="fas fa-key"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        } else {
                            echo "<tr><td colspan='5'>No members found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Member Modal -->
    <div id="memberModal" class="modal">
        <div class="modal-content">
            <h3>Member Details</h3>
            <form method="POST">
                <input type="hidden" id="member_id" name="member_id">
                
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Leave blank to keep current password">
                </div>

                <div class="form-group">
                    <button type="submit" name="submit" class="btn btn-primary">Save Member</button>
                    <button type="button" onclick="closeModal()" class="btn btn-danger">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('memberModal').style.display = 'block';
            document.getElementById('member_id').value = '';
            document.getElementById('name').value = '';
            document.getElementById('email').value = '';
            document.getElementById('password').value = '';
        }

        function closeModal() {
            document.getElementById('memberModal').style.display = 'none';
        }

        function editMember(memberId) {
            fetch(`get_member.php?id=${memberId}`)
                .then(response => response.json())
                .then(member => {
                    document.getElementById('member_id').value = member.id;
                    document.getElementById('name').value = member.name;
                    document.getElementById('email').value = member.email;
                    document.getElementById('memberModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error fetching member details');
                });
        }

        function deleteMember(memberId) {
            if(confirm('Are you sure you want to delete this member?')) {
                window.location.href = 'manage_members.php?delete=' + memberId;
            }
        }

        function resetPassword(memberId) {
            if(confirm('Are you sure you want to reset this member\'s password?')) {
                window.location.href = 'reset_password.php?id=' + memberId;
            }
        }
    </script>
</body>
</html>

