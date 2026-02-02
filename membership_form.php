<?php
include('config.php');
session_start();

if(!isset($_SESSION['user_id'])) {
    header('location:login_form.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if user already has an active membership
$check_query = "SELECT * FROM memberships WHERE user_id = ? AND status = 'active' AND end_date >= CURDATE()";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();
$active_membership = $result->fetch_assoc();

if(isset($_POST['submit'])) {
    // Double check to prevent duplicate active memberships
    if($active_membership) {
        $error_msg = "You already have an active membership plan. Please wait until it expires to choose a new plan.";
    } else {
        $plan_name = mysqli_real_escape_string($conn, $_POST['plan']);
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+1 month'));
        
        // Set amount based on plan
        switch($plan_name) {
            case 'Basic':
                $amount = 1000;
                break;
            case 'Pro':
                $amount = 1500;
                break;
            case 'Premium':
                $amount = 2000;
                break;
            default:
                $amount = 0;
        }

        $insert = "INSERT INTO memberships (user_id, plan_name, start_date, end_date, amount, status) 
                   VALUES (?, ?, ?, ?, ?, 'active')";
        $stmt = $conn->prepare($insert);
        $stmt->bind_param("isssd", $user_id, $plan_name, $start_date, $end_date, $amount);
        
        if($stmt->execute()) {
            $success_msg = "Membership activated successfully!";
            // Redirect to membership status page after successful activation
            header("Location: check_membership.php");
            exit();
        } else {
            $error_msg = "Error activating membership. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gym Membership - StarFitnessClub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --accent-color: #3498db;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f5f6fa;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }

        .membership-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .form-title {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--primary-color);
        }

        select, input[type="submit"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .submit-btn {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 12px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #2980b9;
        }

        .success-msg {
            background: #2ecc71;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .error-msg {
            background: #e74c3c;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .plan-details {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .plan-price {
            font-size: 24px;
            color: var(--secondary-color);
            margin: 10px 0;
        }

        .active-membership-notice {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }

        .active-membership-notice h3 {
            color: #2ecc71;
            margin-bottom: 15px;
        }

        .active-membership-notice p {
            margin-bottom: 10px;
            color: #333;
        }

        .active-membership-notice .btn {
            display: inline-block;
            padding: 10px 20px;
            background: var(--accent-color);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }

        .active-membership-notice .btn:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="membership-form">
            <h2 class="form-title">Gym Membership</h2>
            
            <?php if(isset($success_msg)): ?>
                <div class="success-msg"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <?php if(isset($error_msg)): ?>
                <div class="error-msg"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <?php if($active_membership): ?>
                <div class="active-membership-notice">
                    <h3>You have an active membership</h3>
                    <p>Plan: <?php echo htmlspecialchars($active_membership['plan_name']); ?></p>
                    <p>Valid until: <?php echo htmlspecialchars($active_membership['end_date']); ?></p>
                    <p>Please wait until your current plan expires to choose a new plan.</p>
                    <a href="check_membership.php" class="btn">View Membership Status</a>
                </div>
            <?php else: ?>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="plan">Select Membership Plan:</label>
                        <select name="plan" id="plan" required>
                            <option value="">Choose a plan</option>
                            <option value="Basic">Basic Plan - ₹1000/month</option>
                            <option value="Pro">Pro Plan - ₹1500/month</option>
                            <option value="Premium">Premium Plan - ₹2000/month</option>
                        </select>
                    </div>

                    <div class="plan-details" id="planDetails">
                        <!-- Plan details will be shown here via JavaScript -->
                    </div>

                    <input type="submit" name="submit" value="Activate Membership" class="submit-btn">
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const planSelect = document.getElementById('plan');
        const planDetails = document.getElementById('planDetails');

        const plans = {
            'Basic': {
                price: '₹1000/month',
                features: [
                    'Smart Workout Plans',
                    'At Home Workouts',
                    'Basic Exercise Library',
                    'Progress Tracking'
                ]
            },
            'Pro': {
                price: '₹1500/month',
                features: [
                    'All Basic Features',
                    'Advanced Workout Plans',
                    'Nutrition Guidelines',
                    'Weekly Progress Reports',
                    'Community Access'
                ]
            },
            'Premium': {
                price: '₹2000/month',
                features: [
                    'All Pro Features',
                    'ELITE GYMs Access',
                    'Personal Training',
                    'Custom Meal Plans',
                    '24/7 Support',
                    'Premium Classes'
                ]
            }
        };

        planSelect.addEventListener('change', function() {
            const selectedPlan = this.value;
            if(selectedPlan && plans[selectedPlan]) {
                const plan = plans[selectedPlan];
                planDetails.innerHTML = `
                    <h3>${selectedPlan} Plan</h3>
                    <div class="plan-price">${plan.price}</div>
                    <ul>
                        ${plan.features.map(feature => `<li>${feature}</li>`).join('')}
                    </ul>
                `;
            } else {
                planDetails.innerHTML = '';
            }
        });
    </script>
</body>
</html>


