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

if (isset($_POST['delete_student'])) {
    $student_id = $_POST['student_id'];
    
    $conn->begin_transaction();
    
    try {
        $delete_registrations = "DELETE FROM course_registrations WHERE student_id = '$student_id'";
        if (!$conn->query($delete_registrations)) {
            throw new Exception("Error deleting registrations: " . $conn->error);
        }
        
        $delete_student = "DELETE FROM register WHERE studentId = '$student_id' AND role = 'Student'";
        if (!$conn->query($delete_student)) {
            throw new Exception("Error deleting student: " . $conn->error);
        }
        
        $conn->commit();
        $success = "Student account and all associated data deleted successfully!";
        
        header("Refresh: 2");
        
    } catch (Exception $e) {
        $conn->rollback();
        $errors[] = $e->getMessage();
    }
}

$search_query = "";
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
}

$sql = "SELECT * FROM register WHERE role = 'Student'";

if (!empty($search_query)) {
    $sql .= " AND (name LIKE '%$search_query%' 
                  OR studentId LIKE '%$search_query%' 
                  OR email LIKE '%$search_query%'
                  OR department LIKE '%$search_query%')";
}
$sql .= " ORDER BY name ASC";

$students = [];
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $students = $result->fetch_all(MYSQLI_ASSOC);
}

$enrollment_counts = [];
$enrollment_details = [];

if (!empty($students)) {
    $student_ids = array_column($students, 'studentId');
    $student_ids_str = "'" . implode("','", $student_ids) . "'";
    
    $enrollment_sql = "SELECT student_id, COUNT(*) as course_count 
                      FROM course_registrations 
                      WHERE student_id IN ($student_ids_str) 
                      GROUP BY student_id";
    $enrollment_result = $conn->query($enrollment_sql);
    
    if ($enrollment_result && $enrollment_result->num_rows > 0) {
        while ($row = $enrollment_result->fetch_assoc()) {
            $enrollment_counts[$row['student_id']] = $row['course_count'];
        }
    }
    
    $details_sql = "SELECT cr.student_id, c.course_name, c.course_code, cr.registration_date
                   FROM course_registrations cr
                   JOIN courses c ON cr.course_id = c.course_id
                   WHERE cr.student_id IN ($student_ids_str)
                   ORDER BY cr.registration_date DESC";
    $details_result = $conn->query($details_sql);
    
    if ($details_result && $details_result->num_rows > 0) {
        while ($row = $details_result->fetch_assoc()) {
            $enrollment_details[$row['student_id']][] = $row;
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students</title>
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

.page-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 30px;
	flex-wrap: wrap;
	gap: 15px;
}

h1 {
	color: #2c3e50;
	font-size: 28px;
	margin-right: auto;
}

.back-btn {
	background-color: #3498db;
	color: white;
	padding: 10px 20px;
	border-radius: 5px;
	text-decoration: none;
	transition: background-color 0.3s;
	white-space: nowrap;
}

.back-btn:hover {
	background-color: #2980b9;
}

.search-container {
	display: flex;
	justify-content: center;
	width: 100%;
	max-width: 600px;
	margin: 0 auto 20px auto;
}

.search-input {
	flex: 1;
	padding: 12px 15px;
	border: 1px solid #ddd;
	border-radius: 5px 0 0 5px;
	font-size: 16px;
	outline: none;
}

.search-btn {
	padding: 12px 20px;
	background-color: #2c3e50;
	color: white;
	border: none;
	border-radius: 0 5px 5px 0;
	cursor: pointer;
	transition: background-color 0.3s;
}

.search-btn:hover {
	background-color: #1a2530;
}

.clear-search {
	margin-left: 10px;
	padding: 12px 15px;
	background-color: #95a5a6;
	color: white;
	border: none;
	border-radius: 5px;
	cursor: pointer;
	transition: background-color 0.3s;
}

.clear-search:hover {
	background-color: #7f8c8d;
}

.search-results-info {
	text-align: center;
	margin-bottom: 20px;
	color: #7f8c8d;
	font-style: italic;
}

.message-box {
	padding: 15px;
	border-radius: 5px;
	margin-bottom: 25px;
	width: 100%;
	max-width: 900px;
	margin-left: auto;
	margin-right: auto;
}

.success {
	background-color: #ddffdd;
	border-left: 5px solid #2ecc71;
	color: #27ae60;
}

.error {
	background-color: #ffdddd;
	border-left: 5px solid #e74c3c;
	color: #c0392b;
}

.error ul {
	margin: 0;
	padding-left: 20px;
}

.students-stats {
	display: flex;
	gap: 15px;
	margin-bottom: 20px;
	justify-content: center;
	flex-wrap: wrap;
}

.stat-card {
	background: white;
	padding: 15px 20px;
	border-radius: 8px;
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
	text-align: center;
	min-width: 150px;
}

.stat-number {
	font-size: 24px;
	font-weight: bold;
	color: #2c3e50;
}

.stat-label {
	font-size: 14px;
	color: #7f8c8d;
}

.stat-total {
	border-left: 4px solid #3498db;
}

.stat-enrolled {
	border-left: 4px solid #2ecc71;
}

.stat-not-enrolled {
	border-left: 4px solid #e74c3c;
}

.students-container {
	display: grid;
	gap: 20px;
	max-width: 1200px;
	margin: 0 auto;
}

.student-card {
	background-color: white;
	border-radius: 10px;
	padding: 25px;
	box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
	border-left: 4px solid #3498db;
	position: relative;
}

.student-card.has-courses {
	border-left-color: #2ecc71;
}

.student-header {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	margin-bottom: 15px;
	flex-wrap: wrap;
	gap: 15px;
}

.student-info {
	flex: 1;
}

.student-name {
	font-size: 20px;
	color: #2c3e50;
	margin-bottom: 5px;
	font-weight: 600;
}

.student-details {
	color: #7f8c8d;
	font-size: 14px;
	line-height: 1.5;
}

.student-details strong {
	color: #2c3e50;
}

.enrollment-badge {
	padding: 8px 15px;
	border-radius: 20px;
	font-size: 14px;
	font-weight: bold;
	white-space: nowrap;
}

.badge-enrolled {
	background-color: #e8f5e8;
	color: #27ae60;
}

.badge-not-enrolled {
	background-color: #ffebee;
	color: #e74c3c;
}

.courses-section {
	margin-top: 20px;
	padding-top: 20px;
	border-top: 1px solid #eee;
}

.courses-title {
	font-size: 18px;
	color: #2c3e50;
	margin-bottom: 15px;
	font-weight: 600;
}

.courses-list {
	display: grid;
	gap: 12px;
}

.course-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 12px;
	background-color: #f8f9fa;
	border-radius: 6px;
	border-left: 3px solid #3498db;
}

.course-info {
	flex: 1;
	min-width: 0;
}

.course-name {
	font-weight: 600;
	color: #2c3e50;
	margin-bottom: 4px;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

.course-code {
	font-size: 14px;
	color: #7f8c8d;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

.enrollment-date {
	font-size: 12px;
	color: #95a5a6;
	text-align: right;
	min-width: 120px;
	white-space: nowrap;
}

.no-courses {
	padding: 20px;
	text-align: center;
	background-color: #f8f9fa;
	border-radius: 6px;
	color: #7f8c8d;
	font-style: italic;
}

.no-students {
	text-align: center;
	padding: 60px 40px;
	color: #7f8c8d;
	font-size: 18px;
	background-color: white;
	border-radius: 10px;
	box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.student-actions {
	display: flex;
	gap: 10px;
	margin-top: 15px;
	justify-content: flex-end;
}

.delete-btn {
	background-color: #e74c3c;
	color: white;
	border: none;
	padding: 10px 20px;
	border-radius: 5px;
	cursor: pointer;
	font-weight: 500;
	transition: background-color 0.3s;
}

.delete-btn:hover {
	background-color: #c0392b;
}

.delete-form {
	margin: 0;
}

@media (max-width: 768px) {
	.students-container {
		grid-template-columns: 1fr;
	}
	
	.page-header {
		flex-direction: column;
		align-items: flex-start;
	}
	
	.search-container {
		flex-direction: column;
		align-items: center;
	}
	
	.search-input {
		border-radius: 5px;
		margin-bottom: 10px;
		width: 100%;
	}
	
	.search-btn {
		border-radius: 5px;
		width: 100%;
	}
	
	.clear-search {
		margin-left: 0;
		margin-top: 10px;
		width: 100%;
	}
	
	.students-stats {
		flex-direction: column;
		align-items: center;
	}
	
	.stat-card {
		width: 100%;
		max-width: 250px;
	}
	
	.student-header {
		flex-direction: column;
		align-items: flex-start;
	}
	
	.enrollment-date {
		text-align: left;
		margin-top: 8px;
	}
	
	.course-item {
		flex-direction: column;
		align-items: flex-start;
		gap: 8px;
	}
	
	.student-actions {
		justify-content: center;
	}
	
	.delete-btn {
		width: 100%;
	}
}
    </style>
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <h2 class="logo">CourseReg</h2>
            <ul class="menu">
                <li><a href="profile.php">👤 Profile</a></li>
                <li><a href="manage_courses.php">📚 Manage Courses</a></li>
                <li><a href="manage_students.php" class="active">👥 Manage Students</a></li>
                <li><a href="registrations.php">📝 Registrations</a></li>
                <li><a href="admin_notifications.php">🔔 Notifications</a></li>
            </ul>
            <form action="" method="POST">
                <button type="submit" name="logout-btn" class="logout">Logout</button>
            </form>
        </aside>

        <div class="main">
            <div class="page-header">
                <h1>Manage Students</h1>
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

            <div class="students-stats">
                <div class="stat-card stat-total">
                    <div class="stat-number"><?php echo count($students); ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card stat-enrolled">
                    <div class="stat-number"><?php echo count($enrollment_counts); ?></div>
                    <div class="stat-label">Enrolled Students</div>
                </div>
                <div class="stat-card stat-not-enrolled">
                    <div class="stat-number"><?php echo count($students) - count($enrollment_counts); ?></div>
                    <div class="stat-label">Not Enrolled</div>
                </div>
            </div>

            <form method="GET" action="" class="search-container">
                <input type="text" name="search" class="search-input" placeholder="Search students by name, ID, email, or department..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="search-btn">Search</button>
                <?php if (!empty($search_query)) : ?>
                    <a href="manage_students.php" class="clear-search">Clear</a>
                <?php endif; ?>
            </form>

            <?php if (!empty($search_query)) : ?>
                <div class="search-results-info">
                    Showing results for: "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                    <?php if (count($students) > 0) : ?>
                        - <?php echo count($students); ?> student(s) found
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($students)) : ?>
                <div class="no-students">
                    <h2>No Students Found</h2>
                    <p>No students are registered in the system yet.</p>
                </div>
            <?php else : ?>
                <div class="students-container">
                    <?php foreach ($students as $student) : 
                        $enrollment_count = $enrollment_counts[$student['studentId']] ?? 0;
                        $has_courses = $enrollment_count > 0;
                    ?>
                        <div class="student-card <?php echo $has_courses ? 'has-courses' : ''; ?>">
                            <div class="student-header">
                                <div class="student-info">
                                    <h3 class="student-name"><?php echo htmlspecialchars($student['name']); ?></h3>
                                    <div class="student-details">
                                        <div><strong>ID:</strong> <?php echo htmlspecialchars($student['studentId']); ?></div>
                                        <div><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></div>
                                        <div><strong>Department:</strong> <?php echo htmlspecialchars($student['department']); ?></div>
                                        <div><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone']); ?></div>
                                    </div>
                                </div>
                                
                                <div class="enrollment-badge <?php echo $has_courses ? 'badge-enrolled' : 'badge-not-enrolled'; ?>">
                                    <?php echo $has_courses ? "Enrolled in $enrollment_count course(s)" : "Not enrolled"; ?>
                                </div>
                            </div>

                            <?php if ($has_courses) : ?>
                                <div class="courses-section">
                                    <h4 class="courses-title">Enrolled Courses</h4>
                                    <div class="courses-list">
                                        <?php foreach ($enrollment_details[$student['studentId']] as $enrollment) : ?>
                                            <div class="course-item">
                                                <div class="course-info">
                                                    <div class="course-name"><?php echo htmlspecialchars($enrollment['course_name']); ?></div>
                                                    <div class="course-code"><?php echo htmlspecialchars($enrollment['course_code']); ?></div>
                                                </div>
                                                <div class="enrollment-date">
                                                    Enrolled: <?php echo date('M j, Y', strtotime($enrollment['registration_date'])); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else : ?>
                                <div class="no-courses">
                                    This student hasn't enrolled in any courses yet.
                                </div>
                            <?php endif; ?>

                            <div class="student-actions">
                                <form class="delete-form" action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this student account? This will also remove all their course registrations. This action cannot be undone.');">
                                    <input type="hidden" name="student_id" value="<?php echo $student['studentId']; ?>">
                                    <button type="submit" name="delete_student" class="delete-btn">
                                        Delete Account
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>