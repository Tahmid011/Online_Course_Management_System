<?php
session_start();
include '../../DB/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['studentId'])) {
    header("Location: /Online_Course_Management_System/User_Authentication/view/login.php");
    exit();
}

$student_id = $_SESSION['studentId'];

// Determine if user is admin or student based on ID format
$is_admin = preg_match('/^\d{5}$/', $student_id);

// Redirect admin users (they shouldn't have enrollments)
if ($is_admin) {
    header("Location: /Online_Course_Management_System/Admin/view/dashboard.php");
    exit();
}

if (isset($_POST['logout-btn'])) {
    session_destroy();
    header("Location: ../../User_Authentication/view/login.php");
    exit();
}

// Fetch enrolled courses
$enrolled_courses = [];
$result = $conn->query("
    SELECT c.*, cr.registration_date 
    FROM courses c 
    INNER JOIN course_registrations cr ON c.course_id = cr.course_id 
    WHERE cr.student_id = '$student_id' 
    ORDER BY cr.registration_date DESC
");

if ($result && $result->num_rows > 0) {
    $enrolled_courses = $result->fetch_all(MYSQLI_ASSOC);
}

// Handle course withdrawal
$withdrawal_success = "";
$withdrawal_error = "";

if (isset($_POST['withdraw_course'])) {
    $course_id = $_POST['course_id'];
    
    // Delete the registration
    $delete_sql = "DELETE FROM course_registrations WHERE student_id = '$student_id' AND course_id = $course_id";
    
    if ($conn->query($delete_sql)) {
        // Increase available seats
        $update_sql = "UPDATE courses SET seats_available = seats_available + 1 WHERE course_id = $course_id";
        $conn->query($update_sql);
        
        $withdrawal_success = "Successfully withdrawn from the course!";
        
        // Refresh the page to update the list
        header("Refresh: 2"); // Refresh after 2 seconds
    } else {
        $withdrawal_error = "Withdrawal failed: " . $conn->error;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Enrollments</title>
    <link rel="stylesheet" href="../css/my_enrollments.css">
    <link rel="stylesheet" href="../css/sidebar.css">
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <h2 class="logo">CourseReg</h2>
            <ul class="menu">
                <li><a href="student_profile.php">👤 Profile</a></li>
                <li><a href="available_courses.php">📚 Available Courses</a></li>
                <li><a href="my_enrollments.php">📝 My Enrollments</a></li>
                <li><a href="student_notifications.php">🔔 Notifications</a></li>
            </ul>
            <form action="" method="POST">
                <button type="submit" name="logout-btn" class="logout">Logout</button>
            </form>
        </aside>

        <div class="main">
            <div class="page-header">
                <h1>My Enrollments</h1>
                <a href="/Online_Course_Management_System/Student/view/dashboard.php" class="back-btn">Back to Dashboard</a>
            </div>

            <?php if (!empty($withdrawal_success)) : ?>
                <div class="message-box success"><?php echo $withdrawal_success; ?></div>
            <?php endif; ?>

            <?php if (!empty($withdrawal_error)) : ?>
                <div class="message-box error"><?php echo $withdrawal_error; ?></div>
            <?php endif; ?>

            <?php if (empty($enrolled_courses)) : ?>
                <div class="no-enrollments">
                    <h2>No Course Enrollments</h2>
                    <p>You haven't enrolled in any courses yet.</p>
                    <p>Browse our <a href="available_courses.php">available courses</a> to get started!</p>
                </div>
            <?php else : ?>
                <div class="enrollments-container">
                    <?php foreach ($enrolled_courses as $course) : ?>
                        <div class="enrollment-card">
                            <span class="enrollment-status status-active">Enrolled</span>
                            
                            <div class="enrollment-header">
                                <div class="course-code"><?php echo $course['course_code']; ?></div>
                                <h2 class="course-name"><?php echo $course['course_name']; ?></h2>
                            </div>
                            
                            <?php if (!empty($course['description'])) : ?>
                                <p class="course-description"><?php echo $course['description']; ?></p>
                            <?php endif; ?>
                            
                            <div class="enrollment-details">
                                <div class="detail-item">
                                    <span class="detail-label">Course ID:</span>
                                    <span class="detail-value"><?php echo $course['course_id']; ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Seats Available:</span>
                                    <span class="detail-value"><?php echo $course['seats_available']; ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Enrollment Date:</span>
                                    <span class="detail-value"><?php echo date('M j, Y', strtotime($course['registration_date'])); ?></span>
                                </div>
                            </div>
                            
                            <form action="" method="POST" onsubmit="return confirm('Are you sure you want to withdraw from this course?');">
                                <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                <button type="submit" name="withdraw_course" class="withdraw-btn">
                                    Withdraw from Course
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>