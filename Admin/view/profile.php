<?php
session_start();
if (isset($_POST['logout-btn'])) {
    session_destroy();
    header("Location: ../../User_Authentication/view/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile Page</title>
    <link rel="stylesheet" href="../css/profile.css">
</head>
<body>
    <form class="profile_admin" method="POST">
        <div class="dashboard">
            <aside class="sidebar">
                <h2 class="logo">CourseReg</h2>
                <ul class="menu">
                    <li><a href="profile.php">👤 Profile</a></li>
                    <li><a href="manage_courses.php">📚 Manage Courses</a></li>
                    <li><a href="#">👥 Manage Students</a></li>
                    <li><a href="#">📝 Registrations</a></li>
                    <li><a href="#">📊 Reports</a></li>
                    <li><a href="#">🔔 Notifications</a></li>
                </ul>
                <button type="submit" name="logout-btn" class="logout">Logout</button>
            </aside>

            <div class="main profile-main">
                <a href="dashboard.php" class="back-btn">Back</a>
                <div class="profile-section">
                    <div class="profile-pic">
                        <img src="../img/default-profile.svg" alt="Profile Picture" id="profileImg">
                    </div>

                    <div class="profile-info">
                        <p><strong>Full Name:</strong> Md. Tahmidul Biswas</p>
                        <p><strong>ID:</strong> 22-48616-3</p>
                        <p><strong>Department:</strong> CSE</p>
                        <p><strong>Phone:</strong> 01531554187</p>
                        <p><strong>Email:</strong> biswastahmidul@gmail.com</p>
                    </div>
                </div>

                <div class="update-section">
                    <h2>Update Information</h2>

                    <div class="update-field">
                        <label for="name">Change Name:</label>
                        <input type="text" id="name" name="name" placeholder="Enter new name">
                        <button type="submit" name="change-name">Change</button>
                    </div>

                    <div class="update-field">
                        <label for="phone">Change Phone:</label>
                        <input type="text" id="phone" name="phone" placeholder="Enter new phone">
                        <button type="submit" name="change-phone">Change</button>
                    </div>

                    <div class="update-field">
                        <label for="email">Change Email:</label>
                        <input type="email" id="email" name="email" placeholder="Enter new email">
                        <button type="submit" name="change-email">Change</button>
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
                        <button type="submit" name="change-password">Change</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</body>
</html>
