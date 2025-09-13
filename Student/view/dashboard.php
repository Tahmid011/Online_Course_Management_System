<?php
session_start();
include '../../DB/db_connection.php';

// Check if user is logged in as student
if (!isset($_SESSION['studentId']) || preg_match('/^\d{5}$/', $_SESSION['studentId'])) {
    header("Location: /Online_Course_Management_System/User_Authentication/view/login.php");
    exit();
}

$student_id = $_SESSION['studentId'];

// Get statistics from database
$available_courses = 0;
$my_enrollments = 0;
$new_notifications = 0;

// Get available courses (courses with seats available)
$courses_result = $conn->query("SELECT COUNT(*) as count FROM courses WHERE seats_available > 0");
if ($courses_result && $courses_result->num_rows > 0) {
    $available_courses = $courses_result->fetch_assoc()['count'];
}

// Get student's enrollments
$enrollments_result = $conn->query("SELECT COUNT(*) as count FROM course_registrations WHERE student_id = '$student_id'");
if ($enrollments_result && $enrollments_result->num_rows > 0) {
    $my_enrollments = $enrollments_result->fetch_assoc()['count'];
}

// Get new notifications (notifications from last 7 days)
$notifications_result = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
if ($notifications_result && $notifications_result->num_rows > 0) {
    $new_notifications = $notifications_result->fetch_assoc()['count'];
}

// Get student name for welcome message
$student_name = "Student";
$name_result = $conn->query("SELECT name FROM register WHERE studentId = '$student_id'");
if ($name_result && $name_result->num_rows > 0) {
    $student_data = $name_result->fetch_assoc();
    $student_name = $student_data['name'];
}

// Handle logout
if (isset($_POST['logout-btn'])) {
    session_destroy();
    header("Location: ../../User_Authentication/view/login.php");
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Student Dashboard</title>
        <link rel="stylesheet" href="../css/sidebar.css">
        <style>
            * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    background-color: #f5f7fa;
    color: #333;
}

.dashboard {
    display: flex;
    min-height: 100vh;
}

.main {
    flex: 1;
    padding: 30px;
}

.header {
    margin-bottom: 30px;
}

.header h1 {
    color: #2c3e50;
    font-size: 32px;
    margin-bottom: 10px;
}

.header p {
    color: #7f8c8d;
    font-size: 16px;
}

.content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.card {
    background-color: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.3s, box-shadow 0.3s;
    cursor: pointer;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.card h3 {
    color: #7f8c8d;
    font-size: 16px;
    margin-bottom: 15px;
    font-weight: 600;
}

.card p {
    color: #2c3e50;
    font-size: 36px;
    font-weight: bold;
    margin: 0;
}

.card:nth-child(1) {
    border-top: 4px solid #3498db;
}

.card:nth-child(1):hover {
    background-color: #e3f2fd;
}

.card:nth-child(2) {
    border-top: 4px solid #2ecc71;
}

.card:nth-child(2):hover {
    background-color: #e8f5e8;
}

.card:nth-child(3) {
    border-top: 4px solid #f39c12;
}

.card:nth-child(3):hover {
    background-color: #fff3e0;
}

.recent-section {
    background-color: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.recent-section h2 {
    color: #2c3e50;
    font-size: 24px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f1f1f1;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.stat-item {
    text-align: center;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 8px;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 14px;
    color: #7f8c8d;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.action-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 12px;
    text-align: center;
    text-decoration: none;
    transition: transform 0.3s;
}

.action-card:hover {
    transform: translateY(-3px);
    text-decoration: none;
    color: white;
}

.action-card h3 {
    margin-bottom: 10px;
    font-size: 18px;
}

.action-card p {
    font-size: 14px;
    opacity: 0.9;
}

@media (max-width: 768px) {
    .content {
        grid-template-columns: 1fr;
    }
    
    .main {
        padding: 20px;
    }
    
    .header h1 {
        font-size: 28px;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
}
        </style>
        
    </head>
    <body>
        <form class="dashboard_student" method="POST">
            <div class="dashboard">
                <aside class="sidebar">
                    <h2 class="logo">CourseReg</h2>
                    <ul class="menu">
                        <li><a href="student_profile.php">👤 Profile</a></li>
                        <li><a href="available_courses.php">📚 Available Courses</a></li>
                        <li><a href="my_enrollments.php">📝 My Enrollments</a></li>
                        <li><a href="student_notifications.php">🔔 Notifications</a></li>
                    </ul>

                    <button type="submit" name="logout-btn" class="logout">Logout</button>
                </aside>

                <div class="main">
                    <header class="header">
                        <h1>Welcome, Student</h1>
                    </header>

                    <section class="content">
                        <div class="card">
                            <h3>Available Courses</h3>
                            <p><?php echo $available_courses; ?></p>
                        </div>
                        <div class="card">
                            <h3>My Enrollments</h3>
                            <p><?php echo $my_enrollments; ?></p>
                        </div>
                        <div class="card">
                            <h3>Notifications</h3>
                            <p><?php echo $new_notifications; ?> New</p>
                        </div>
                    </section>

                    <section class="recent-section">
                        <h2>Quick Statistics</h2>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $available_courses; ?></div>
                                <div class="stat-label">Courses Available</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $my_enrollments; ?></div>
                                <div class="stat-label">Your Enrollments</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $new_notifications; ?></div>
                                <div class="stat-label">New Notifications</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $available_courses - $my_enrollments; ?></div>
                                <div class="stat-label">Courses You Can Take</div>
                            </div>
                        </div>
                    </section>

                    <div class="quick-actions">
                        <a href="available_courses.php" class="action-card">
                            <h3>📚 Browse Courses</h3>
                            <p>Explore available courses and enroll in new ones</p>
                        </a>
                        <a href="my_enrollments.php" class="action-card">
                            <h3>📝 View Enrollments</h3>
                            <p>Check your current course enrollments</p>
                        </a>
                        <a href="student_notifications.php" class="action-card">
                            <h3>🔔 View Notifications</h3>
                            <p>Check latest updates from administration</p>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </body>
</html>
