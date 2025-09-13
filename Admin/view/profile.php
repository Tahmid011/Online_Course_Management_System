<?php include __DIR__ . '/../../DB/validation_profile.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile Page</title>
    <link rel="stylesheet" href="../css/profile.css">
    <link rel="stylesheet" href="../css/sidebar.css">
</head>
<body>
<form class="profile_admin" method="POST">
    <div class="dashboard">
        <aside class="sidebar">
            <h2 class="logo">CourseReg</h2>
            <ul class="menu">
                <li><a href="profile.php">👤 Profile</a></li>
                <li><a href="manage_courses.php">📚 Manage Courses</a></li>
                <li><a href="manage_students.php">👥 Manage Students</a></li>
                <li><a href="registrations.php">📝 Registrations</a></li>
                <li><a href="admin_notifications.php">🔔 Notifications</a></li>
            </ul>
            <button type="submit" name="logout-btn" class="logout">Logout</button>
        </aside>

        <div class="main profile-main">
            <a href="dashboard.php" class="back-btn">Back to Dashboard</a>

            <?php if(!empty($errors)) : ?>
                <div class="error-box">
                    <ul>
                        <?php foreach($errors as $err) echo "<li>$err</li>"; ?>
                    </ul>
                </div>
            <?php elseif(!empty($success)) : ?>
                <div class="success-box"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="profile-section">
                <div class="profile-pic">
                    <img src="../img/default-profile.svg" alt="Profile Picture" id="profileImg">
                </div>
                <div class="profile-info">
                    <p><strong>Full Name:</strong> <?php echo $user_data['name']; ?></p>
                    <p><strong>ID:</strong> <?php echo $user_data['studentId']; ?></p>
                    <p><strong>Role:</strong> <?php echo $user_data['role']; ?></p>
                    <p><strong>Department:</strong> <?php echo $user_data['department']; ?></p>
                    <p><strong>Phone:</strong> <?php echo $user_data['phone']; ?></p>
                    <p><strong>Email:</strong> <?php echo $user_data['email']; ?></p>
                </div>
            </div>

            <div class="update-section">
                <h2>Update Information</h2>

                <div class="update-field">
                    <label for="name">Change Name:</label>
                    <input type="text" id="name" name="name" placeholder="Enter new name" value="<?php echo $user_data['name']; ?>">
                    <button type="submit" name="change-name">Update</button>
                </div>

                <div class="update-field">
                    <label for="phone">Change Phone:</label>
                    <input type="text" id="phone" name="phone" placeholder="Enter new phone" value="<?php echo $user_data['phone']; ?>">
                    <button type="submit" name="change-phone">Update</button>
                </div>

                <div class="update-field">
                    <label for="email">Change Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter new email" value="<?php echo $user_data['email']; ?>">
                    <button type="submit" name="change-email">Update</button>
                </div>

                <h2>Change Password</h2>
                <div class="update-field">
                    <label for="current-pass">Current Password:</label>
                    <input type="password" id="current-pass" name="current-pass" placeholder="Enter current password">
                </div>
                <div class="update-field">
                    <label for="new-pass">New Password:</label>
                    <input type="password" id="new-pass" name="new-pass" placeholder="Enter new password">
                </div>
                <div class="update-field">
                    <label for="confirm-pass">Confirm Password:</label>
                    <input type="password" id="confirm-pass" name="confirm-pass" placeholder="Confirm new password">
                    <button type="submit" name="change-password">Change Password</button>
                </div>
            </div>
        </div>
    </div>
</form>
</body>
</html>