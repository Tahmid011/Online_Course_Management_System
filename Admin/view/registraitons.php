<?php
session_start();
include '../../DB/db_connection.php';

if (!isset($_SESSION['studentId']) || !preg_match('/^\d{5}$/', $_SESSION['studentId'])) {
    header("Location: /Online_Course_Management_System/User_Authentication/view/login.php");
    exit();
}

$admin_id = $_SESSION['studentId'];
$errors = [];
$success = "";

if (isset($_POST['logout-btn'])) {
    session_destroy();
    header("Location: ../../User_Authentication/view/login.php");
    exit();
}

$students = [];
$students_result = $conn->query("SELECT studentId, name FROM register WHERE role = 'Student' ORDER BY name");
if ($students_result && $students_result->num_rows > 0) {
    $students = $students_result->fetch_all(MYSQLI_ASSOC);
}

$courses = [];
$courses_result = $conn->query("SELECT * FROM courses WHERE seats_available > 0 ORDER BY course_name");
if ($courses_result && $courses_result->num_rows > 0) {
    $courses = $courses_result->fetch_all(MYSQLI_ASSOC);
}

if (isset($_POST['enroll_student'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    
    if (empty($student_id) || empty($course_id)) {
        $errors[] = "Please select both a student and a course.";
    } else {
        $check_sql = "SELECT * FROM course_registrations WHERE student_id = '$student_id' AND course_id = $course_id";
        $check_result = $conn->query($check_sql);
        
        if ($check_result && $check_result->num_rows > 0) {
            $errors[] = "This student is already enrolled in the selected course.";
        } else {
            $seats_sql = "SELECT seats_available FROM courses WHERE course_id = $course_id";
            $seats_result = $conn->query($seats_sql);
            
            if ($seats_result && $seats_result->num_rows > 0) {
                $course_data = $seats_result->fetch_assoc();
                
                if ($course_data['seats_available'] > 0) {
                    $conn->begin_transaction();
                    
                    try {
                        $enroll_sql = "INSERT INTO course_registrations (student_id, course_id) VALUES ('$student_id', $course_id)";
                        if (!$conn->query($enroll_sql)) {
                            throw new Exception("Error enrolling student: " . $conn->error);
                        }
                        
                        $update_sql = "UPDATE courses SET seats_available = seats_available - 1 WHERE course_id = $course_id";
                        if (!$conn->query($update_sql)) {
                            throw new Exception("Error updating seats: " . $conn->error);
                        }
                        
                        $conn->commit();
                        $success = "Student successfully enrolled in the course!";
                        
                    } catch (Exception $e) {
                        $conn->rollback();
                        $errors[] = $e->getMessage();
                    }
                } else {
                    $errors[] = "No seats available for the selected course.";
                }
            } else {
                $errors[] = "Course not found.";
            }
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Enrollment</title>
    <link rel="stylesheet" href="../css/registrations.css">
    <link rel="stylesheet" href="../css/sidebar.css">
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <h2 class="logo">CourseReg</h2>
            <ul class="menu">
                <li><a href="profile.php">👤 Profile</a></li>
                <li><a href="manage_courses.php">📚 Manage Courses</a></li>
                <li><a href="manage_students.php">👥 Manage Students</a></li>
                <li><a href="registrations.php" class="active">📝 Registrations</a></li>
                <li><a href="admin_notifications.php">🔔 Notifications</a></li>
            </ul>
            <form action="" method="POST">
                <button type="submit" name="logout-btn" class="logout">Logout</button>
            </form>
        </aside>

        <div class="main">
            <div class="page-header">
                <h1>Course Enrollment</h1>
                <a href="/Online_Course_Management_System/Admin/view/dashboard.php" class="back-btn">Back to Dashboard</a>
            </div>

            <?php if (!empty($errors)) : ?>
                <div class="message-box error">
                    <ul>
                        <?php foreach ($errors as $error) : ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)) : ?>
                <div class="message-box success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="stats-container">
                <div class="stat-card stat-total">
                    <div class="stat-number"><?php echo count($students); ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card stat-available">
                    <div class="stat-number"><?php echo count($courses); ?></div>
                    <div class="stat-label">Available Courses</div>
                </div>
            </div>

            <div class="enrollment-form">
                <h2 class="form-title">Enroll Student in Course</h2>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="student_id">Select Student</label>
                        <select id="student_id" name="student_id" required>
                            <option value="">-- Select a Student --</option>
                            <?php foreach ($students as $student) : ?>
                                <option value="<?php echo $student['studentId']; ?>">
                                    <?php echo htmlspecialchars($student['name'] . ' (' . $student['studentId'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="course_id">Select Course</label>
                        <select id="course_id" name="course_id" required>
                            <option value="">-- Select a Course --</option>
                            <?php foreach ($courses as $course) : ?>
                                <option value="<?php echo $course['course_id']; ?>" data-seats="<?php echo $course['seats_available']; ?>">
                                    <?php echo htmlspecialchars($course['course_name'] . ' (' . $course['course_code'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="course-details" id="course-details">
                            Select a course to see available seats
                        </div>
                    </div>
                    
                    <button type="submit" name="enroll_student" class="submit-btn">
                        Enroll Student
                    </button>
                </form>
            </div>

            <?php if (empty($students) || empty($courses)) : ?>
                <div class="no-data">
                    <h2>Cannot Process Enrollments</h2>
                    <p>
                        <?php
                        if (empty($students) && empty($courses)) {
                            echo "No students and no courses available. Please add students and courses first.";
                        } elseif (empty($students)) {
                            echo "No students available. Please add students first.";
                        } else {
                            echo "No courses with available seats. Please add courses or check seat availability.";
                        }
                        ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.getElementById('course_id').addEventListener('change', function() {
            const courseDetails = document.getElementById('course-details');
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption.value) {
                const seats = selectedOption.getAttribute('data-seats');
                if (seats > 0) {
                    courseDetails.innerHTML = `<span class="seats-available">${seats} seat(s) available</span>`;
                } else {
                    courseDetails.innerHTML = '<span class="no-seats">No seats available</span>';
                }
            } else {
                courseDetails.innerHTML = 'Select a course to see available seats';
            }
        });

        document.querySelector('form').addEventListener('submit', function(e) {
            const studentId = document.getElementById('student_id').value;
            const courseId = document.getElementById('course_id').value;
            const seats = document.getElementById('course_id').options[document.getElementById('course_id').selectedIndex].getAttribute('data-seats');
            
            if (!studentId || !courseId) {
                e.preventDefault();
                alert('Please select both a student and a course.');
                return false;
            }
            
            if (parseInt(seats) <= 0) {
                e.preventDefault();
                alert('This course has no available seats. Please select a different course.');
                return false;
            }
        });
    </script>
</body>
</html>