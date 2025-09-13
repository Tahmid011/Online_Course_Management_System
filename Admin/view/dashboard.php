<?php
session_start();
include '../../DB/db_connection.php';

if (!isset($_SESSION['studentId']) || !preg_match('/^\d{5}$/', $_SESSION['studentId'])) {
    header("Location: /Online_Course_Management_System/User_Authentication/view/login.php");
    exit();
}

$total_courses = 0;
$total_students = 0;
$total_registered = 0;

$courses_result = $conn->query("SELECT COUNT(*) as count FROM courses");
if ($courses_result && $courses_result->num_rows > 0) {
    $total_courses = $courses_result->fetch_assoc()['count'];
}

$students_result = $conn->query("SELECT COUNT(*) as count FROM register WHERE role = 'Student'");
if ($students_result && $students_result->num_rows > 0) {
    $total_students = $students_result->fetch_assoc()['count'];
}

$registered_result = $conn->query("SELECT COUNT(DISTINCT student_id) as count FROM course_registrations");
if ($registered_result && $registered_result->num_rows > 0) {
    $total_registered = $registered_result->fetch_assoc()['count'];
}

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
        <title>Admin Dashboard</title>
        <link rel="stylesheet" href="../css/dashboard.css">
        <link rel="stylesheet" href="../css/sidebar.css">

    </head>
    <body>
        <form class="dashboard_admin" method="POST">
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

                <div class="main">
                    <header class="header">
                        <h1>Welcome, Admin</h1>
                    </header>

                    <section class="content">
                        <div class="card">
                            <h3>Total Courses</h3>
                            <p><?php echo $total_courses; ?></p>
                        </div>
                        <div class="card">
                            <h3>Total Students</h3>
                            <p><?php echo $total_students; ?></p>
                        </div>
                        <div class="card">
                            <h3>Total Registered Students</h3>
                            <p><?php echo $total_registered; ?></p>
                        </div>
                    </section>

                    <section class="recent-section">
                        <h2>Quick Statistics</h2>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $total_courses; ?></div>
                                <div class="stat-label">Courses Offered</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $total_students; ?></div>
                                <div class="stat-label">Total Students</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $total_registered; ?></div>
                                <div class="stat-label">Enrolled Students</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $total_students - $total_registered; ?></div>
                                <div class="stat-label">Not Enrolled</div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </form>
    </body>
</html>
