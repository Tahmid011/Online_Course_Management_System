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
$user_role = $is_admin ? 'admin' : 'student';

if (isset($_POST['logout-btn'])) {
    session_destroy();
    header("Location: ../../User_Authentication/view/login.php");
    exit();
}

// Handle search functionality
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
}

// Build SQL query based on search
$sql = "SELECT * FROM courses";
if (!empty($search_query)) {
    $sql .= " WHERE course_name LIKE '%$search_query%' 
              OR course_code LIKE '%$search_query%' 
              OR description LIKE '%$search_query%'";
}
$sql .= " ORDER BY course_id DESC";

// Fetch available courses
$courses = [];
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $courses = $result->fetch_all(MYSQLI_ASSOC);
}

// Handle course registration for students
$registration_success = "";
$registration_error = "";

if (!$is_admin && isset($_POST['register_course'])) {
    $course_id = $_POST['course_id'];
    
    // Check if already registered
    $check_sql = "SELECT * FROM course_registrations WHERE student_id = '$student_id' AND course_id = $course_id";
    $check_result = $conn->query($check_sql);
    
    if ($check_result && $check_result->num_rows > 0) {
        $registration_error = "You are already registered for this course.";
    } else {
        // Check available seats
        $course_sql = "SELECT seats_available FROM courses WHERE course_id = $course_id";
        $course_result = $conn->query($course_sql);
        
        if ($course_result && $course_result->num_rows > 0) {
            $course = $course_result->fetch_assoc();
            
            if ($course['seats_available'] > 0) {
                // Register student
                $register_sql = "INSERT INTO course_registrations (student_id, course_id) VALUES ('$student_id', $course_id)";
                
                if ($conn->query($register_sql)) {
                    // Decrease available seats
                    $update_sql = "UPDATE courses SET seats_available = seats_available - 1 WHERE course_id = $course_id";
                    $conn->query($update_sql);
                    
                    $registration_success = "Successfully registered for the course!";
                    // Refresh to show updated seat count
                    header("Location: available_courses.php?search=" . urlencode($search_query));
                    exit();
                } else {
                    $registration_error = "Registration failed: " . $conn->error;
                }
            } else {
                $registration_error = "No seats available for this course.";
            }
        } else {
            $registration_error = "Course not found.";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Courses</title>
    <link rel="stylesheet" href="../css/available_courses.css">
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
                <h1>Available Courses</h1>
                <a href="<?php echo $is_admin ? '/Online_Course_Management_System/Admin/view/dashboard.php' : '/Online_Course_Management_System/Student/view/dashboard.php'; ?>" class="back-btn">Back to Dashboard</a>
            </div>

            <!-- Search Form -->
            <form method="GET" action="" class="search-container">
                <input type="text" name="search" class="search-input" placeholder="Search courses by name, code, or description..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="search-btn">Search</button>
                <?php if (!empty($search_query)) : ?>
                    <a href="available_courses.php" class="clear-search">Clear</a>
                <?php endif; ?>
            </form>

            <?php if (!empty($search_query)) : ?>
                <div class="search-results-info">
                    Showing results for: "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                    <?php if (count($courses) > 0) : ?>
                        - <?php echo count($courses); ?> course(s) found
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($is_admin) : ?>
                <div class="admin-message">
                    <p>As an administrator, you can view all available courses but cannot register for them.</p>
                    <p><a href="manage_courses.php">Manage Courses</a> to add, edit, or remove courses.</p>
                </div>
            <?php endif; ?>

            <?php if (!empty($registration_success)) : ?>
                <div class="message-box success"><?php echo $registration_success; ?></div>
            <?php endif; ?>

            <?php if (!empty($registration_error)) : ?>
                <div class="message-box error"><?php echo $registration_error; ?></div>
            <?php endif; ?>

            <?php if (empty($courses)) : ?>
                <div class="no-courses">
                    <?php if (!empty($search_query)) : ?>
                        <p>No courses found matching your search criteria.</p>
                        <p><a href="available_courses.php">View all courses</a></p>
                    <?php else : ?>
                        <p>No courses available at the moment. Please check back later.</p>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <div class="courses-container">
                    <?php foreach ($courses as $course) : ?>
                        <div class="course-card">
                            <div class="course-header">
                                <div class="course-code"><?php echo $course['course_code']; ?></div>
                                <h2 class="course-name"><?php echo $course['course_name']; ?></h2>
                            </div>
                            
                            <?php if (!empty($course['description'])) : ?>
                                <p class="course-description"><?php echo $course['description']; ?></p>
                            <?php endif; ?>
                            
                            <div class="course-details">
                                <div class="seats <?php echo $course['seats_available'] > 0 ? 'available' : 'full'; ?>">
                                    Seats: <?php echo $course['seats_available']; ?> available
                                </div>
                                <div class="course-id">ID: <?php echo $course['course_id']; ?></div>
                            </div>
                            
                            <?php if (!$is_admin) : ?>
                                <form action="" method="POST">
                                    <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                    <button type="submit" name="register_course" class="register-btn" 
                                        <?php echo $course['seats_available'] <= 0 ? 'disabled' : ''; ?>>
                                        <?php echo $course['seats_available'] <= 0 ? 'Course Full' : 'Register Now'; ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>