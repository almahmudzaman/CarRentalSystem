<?php
session_start(); 

if (!isset($_SESSION["username"])) {
    header("Location: login.html"); 
    exit();
}

$username = $_SESSION["username"]; 

$conn = mysqli_connect('localhost', 'root', '', 'rentalcar', '3307');

if (!$conn) {
    echo "<div style='color: white; background-color: red; padding: 10px;'>Database connection failed.</div>";
    header("refresh: 2; url=index.html");
    exit();
}

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['fullName']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    $phone = str_replace('+880 ', '', $phone);

    $update_sql = "UPDATE Registration SET 
                  Name = '$name',
                  `Contact No` = '$phone',
                  Address = '$address'
                  WHERE Email = '$username'";
    
    if (mysqli_query($conn, $update_sql)) {
        $success_message = "Profile updated successfully!";
    } else {
        $error_message = "Error updating profile: " . mysqli_error($conn);
    }
}

$uname = mysqli_real_escape_string($conn, $username);

$sql = "SELECT Name, `Date of Birth`, Role, Address, `Contact No`, Status, Email FROM Registration WHERE Email = '$uname' LIMIT 1";
$result = mysqli_query($conn, $sql);

$user_data = null; 

if ($result && mysqli_num_rows($result) === 1) {
    $user_data = mysqli_fetch_assoc($result);
} else {
    session_unset();
    session_destroy();
    echo "<div style='color: white; background-color: red; padding: 10px;'>User data not found. Please log in again.</div>";
    header("refresh: 3; url=login.html");
    exit();
}

mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - CarRentalPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background:rgb(227, 232, 247);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: rgb(51, 51, 51);
        }

        .profile-container {
            width: 100%;
            max-width: 1000px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            background: rgb(67, 97, 238);
            padding: 40px;
            color: white;
            text-align: center;
            position: relative;
        }

        .profile-header h1 {
            font-weight: 500;
            margin-bottom: 10px;
            font-size: 2.2rem;
        }

        .profile-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .profile-content {
            display: flex;
            padding: 0;
        }

        .sidebar {
            width: 300px;
            background: #f8f9ff;
            padding: 30px;
            border-right: 1px solid rgb(224, 224, 224);
        }

        .main-content {
            flex: 1;
            padding: 30px;
        }

        .profile-picture {
            position: relative;
            width: 150px;
            height: 150px;
            margin: -100px auto 20px;
            border-radius: 50%;
            border: 5px solid white;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-picture img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-picture .edit-icon {
            position: absolute;
            bottom: 0;
            right: 0;
            background: rgb(67, 97, 238);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .profile-picture .edit-icon:hover {
            background: rgb(63, 55, 201);
            transform: scale(1.1);
        }

        .role-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            margin-top: 15px;
            font-size: 0.9rem;
        }

        .role-badge.admin {
            background: rgba(247, 37, 133, 0.15);
            color: rgb(247, 37, 133);
        }

        .role-badge.customer {
            background: rgba(76, 201, 240, 0.15);
            color: rgb(76, 201, 240);
        }

        .role-badge.carowner {
            background: rgba(67, 97, 238, 0.15);
            color: rgb(67, 97, 238);
        }

        .verification {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-top: 25px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .verification h3 {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: rgb(51, 51, 51);
        }

        .verification-status {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
        }

        .verification-status.verified {
            color: rgb(76, 201, 240);
        }

        .verification-status.pending {
            color: rgb(247, 37, 133);
        }

        .verification-status.not-verified {
            color: rgb(230, 57, 70);
        }

        .delete-account {
            margin-top: 30px;
            text-align: center;
        }

        .delete-btn {
            background: rgba(230, 57, 70, 0.1);
            color: rgb(230, 57, 70);
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .delete-btn:hover {
            background: rgba(230, 57, 70, 0.2);
        }

        .section {
            margin-bottom: 30px;
        }

        .section h2 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgb(224, 224, 224);
            color: rgb(67, 97, 238);
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-item label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color:rgb(108, 117, 125);
            font-size: 0.9rem;
        }

        .info-item input, .info-item textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgb(224, 224, 224);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .info-item input:read-only {
            background: #f8f9ff;
            border-color: #e6e9ff;
            cursor: not-allowed;
        }

        .info-item input:focus, .info-item textarea:focus {
            border-color: rgb(67, 97, 238);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            align-items: center;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: rgb(67, 97, 238);
            color: white;
        }

        .btn-primary:hover {
            background: rgb(63, 55, 201);
        }

        .btn-secondary {
            background: #f0f2f5;
            color: rgb(51, 51, 51);
        }

        .btn-secondary:hover {
            background: #e4e6e9;
        }

        .password-form {
            background: #f8f9ff;
            padding: 25px;
            border-radius: 10px;
        }

        .password-form .info-grid {
            grid-template-columns: 1fr;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            width: 90%;
            max-width: 500px;
            border-radius: 15px;
            overflow: hidden;
        }

        .modal-header {
            background: rgb(230, 57, 70);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .modal-header h3 {
            font-weight: 500;
        }

        .modal-body {
            padding: 30px;
            text-align: center;
        }

        .modal-body p {
            margin-bottom: 20px;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .modal-footer {
            padding: 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .btn-danger {
            background: rgb(230, 57, 70);
            color: white;
        }

        .btn-danger:hover {
            background: #c53030;
        }

        .action-panel {
            margin: 20px 0 30px;
            padding-left: 100px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .action-btn {
            padding: 10px 20px;
            border: 2px solid rgb(224, 224, 224);
            border-radius: 8px;
            background: white;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .action-btn:hover{
            color: white;
            background: black;
        }

                
        .forgot-password {
            color:rgb(118, 75, 162);
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            margin-left: 260px;
            transition: color 0.3s;
        }
        
        .forgot-password:hover {
            color:rgb(202, 202, 229);
            text-decoration: underline;
        }
        

 
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1>User Profile</h1>
            <p>Manage your personal information and account settings</p>
        </div>
        
        <div class="action-panel">
            <button class="action-btn" onclick="window.location.href='homepage.html'">Home</button>
            <button class="action-btn" onclick="window.location.href='homepage.html'">Dashboard</button>
            <button class="action-btn">Verification</button>
            <form action="logout.php" method="post">
                <button class="action-btn">Logout</button>
            </form>

        </div>
        
        <div class="profile-content">
            <div class="sidebar">
                <div class="profile-picture">
                    <div class="edit-icon">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>

               <?php if ($user_data['Role'] == 'Admin'): ?>
                    <div class="role-badge admin">Administrator</div>
                <?php elseif ($user_data['Role'] == 'Customer'): ?>
                    <div class="role-badge customer">Customer</div>
                <?php else: ?>
                    <div class="role-badge carowner">Car Owner</div>
                <?php endif; ?>

                <div class="verification">
                    <h3>Verification Status</h3>
                    <div class="verification-status verified">
                        <i class="fas fa-check-circle"></i> Email Verified
                    </div>
                    <div class="verification-status verified">
                        <i class="fas fa-check-circle"></i> Phone Verified
                    </div>
                    <div class="verification-status verified">
                        <i class="fas fa-check-circle"></i> ID Verified
                    </div>
                    <div class="verification-status verified">
                        <i class="fas fa-check-circle"></i> Payment Method Verified
                    </div>
                </div>
                
                <div class="delete-account">
                    <button class="delete-btn" id="deleteBtn">
                        <i class="fas fa-trash-alt"></i> Delete Account
                    </button>
                </div>
            </div>
            
            <div class="main-content">
                <div class="section">
                    <h2>Personal Information</h2>
                    <?php if ($user_data): ?>
                        <form method="POST" action="profile.php">
                            <div class="info-grid">
                                <div class="info-item">
                                    <label for="fullName">Full Name</label>
                                    <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($user_data['Name'] ?? ''); ?>" readonly>
                                </div>
                                <div class="info-item">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" value="<?php echo htmlspecialchars($user_data['Email'] ?? ''); ?>" readonly>
                                </div>
                                <div class="info-item">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" value="<?php echo '+880 '.htmlspecialchars($user_data['Contact No'] ?? ''); ?>" readonly>
                                </div>
                                <div class="info-item">
                                    <label for="dob">Date of Birth</label>
                                    <input type="date" id="dob" value="<?php echo htmlspecialchars($user_data['Date of Birth'] ?? ''); ?>" readonly>
                                </div>
                                <div class="info-item" style="grid-column: span 2;">
                                    <label for="address">Address</label>
                                    <textarea id="address" name="address" rows="2" readonly><?php echo htmlspecialchars($user_data['Address'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="action-buttons">
                                <button type="button" class="btn btn-secondary" id="editBtn">
                                    <i class="fas fa-edit"></i> Edit Information
                                </button>
                                <button type="submit" class="btn btn-primary" id="saveBtn" name="update_profile" style="display: none;">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                                <a href="forgetPass.php" class="forgot-password action-btn">Update password</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <p class="text-red-500 text-lg">Could not retrieve user details. Please try logging in again.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Delete Account</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete your account? This action cannot be undone.</p>
                <p>All your data, including bookings and payment information, will be permanently removed from our systems.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelDelete">
                    Cancel
                </button>
                <button class="btn btn-danger" id="confirmDelete">
                    Delete Account
                </button>
            </div>
        </div>
    </div>
    
    <script>
 
        
        // Edit profile functionality
        const editBtn = document.getElementById('editBtn');
        const saveBtn = document.getElementById('saveBtn');
        const editableFields = ['fullName', 'phone', 'address'];
        
        editBtn.addEventListener('click', () => {
            editableFields.forEach(field => {
                document.getElementById(field).removeAttribute('readonly');
            });
            
            editBtn.style.display = 'none';
            saveBtn.style.display = 'flex';
        });
        
        saveBtn.addEventListener('click', () => {
            editableFields.forEach(field => {
                document.getElementById(field).setAttribute('readonly', true);
            });
            
            // Show success message
            alert('Profile information updated successfully!');
            
            // Toggle buttons
            saveBtn.style.display = 'none';
            editBtn.style.display = 'flex';
        });
        // Add this to your JavaScript
        document.getElementById('phone').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
        });
        // Delete account modal
        const deleteBtn = document.getElementById('deleteBtn');
        const deleteModal = document.getElementById('deleteModal');
        const cancelDelete = document.getElementById('cancelDelete');
        const confirmDelete = document.getElementById('confirmDelete');
        
        deleteBtn.addEventListener('click', () => {
            deleteModal.style.display = 'flex';
        });
        
        cancelDelete.addEventListener('click', () => {
            deleteModal.style.display = 'none';
        });
        
        confirmDelete.addEventListener('click', () => {
            deleteModal.style.display = 'none';
            alert('Account deletion request submitted. Our team will contact you for confirmation.');
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
        });
    </script>
</body>
</html>