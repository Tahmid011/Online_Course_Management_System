<?php
session_start();
include '../../DB/db_connection.php';

if (!isset($_SESSION['studentId']) || preg_match('/^\d{5}$/', $_SESSION['studentId'])) {
    header("Location: /Online_Course_Management_System/User_Authentication/view/login.php");
    exit();
}

$student_id = $_SESSION['studentId'];

if (isset($_POST['logout-btn'])) {
    session_destroy();
    header("Location: ../../User_Authentication/view/login.php");
    exit();
}

$search_query = "";
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
}

$sql = "SELECT n.*, r.name as admin_name 
        FROM notifications n 
        LEFT JOIN register r ON n.admin_id = r.studentId 
        WHERE 1=1";

if (!empty($search_query)) {
    $sql .= " AND (n.title LIKE '%$search_query%' 
                  OR n.message LIKE '%$search_query%' 
                  OR r.name LIKE '%$search_query%')";
}
$sql .= " ORDER BY n.is_important DESC, n.created_at DESC";

$notifications = [];
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $notifications = $result->fetch_all(MYSQLI_ASSOC);
}

$important_count = 0;
foreach ($notifications as $notification) {
    if ($notification['is_important']) {
        $important_count++;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
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

.notifications-stats {
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

.stat-important {
	border-left: 4px solid #e74c3c;
}

.stat-total {
	border-left: 4px solid #3498db;
}

.notifications-container {
	display: grid;
	gap: 20px;
	max-width: 1000px;
	margin: 0 auto;
}

.notification-card {
	background-color: white;
	border-radius: 10px;
	padding: 25px;
	box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
	border-left: 4px solid #3498db;
	transition: transform 0.3s, box-shadow 0.3s;
}

.notification-card:hover {
	transform: translateY(-2px);
	box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.notification-card.important {
	border-left-color: #e74c3c;
	background: linear-gradient(135deg, #fff, #fff5f5);
}

.notification-card.important::before {
	content: "❗ Important";
	display: block;
	background: #e74c3c;
	color: white;
	padding: 5px 10px;
	border-radius: 15px;
	font-size: 12px;
	font-weight: bold;
	margin-bottom: 15px;
	width: fit-content;
}

.notification-header {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	margin-bottom: 15px;
}

.notification-title {
	font-size: 20px;
	color: #2c3e50;
	margin-bottom: 5px;
	font-weight: 600;
}

.notification-meta {
	color: #7f8c8d;
	font-size: 14px;
}

.notification-message {
	color: #2c3e50;
	line-height: 1.6;
	margin-bottom: 15px;
	font-size: 16px;
}

.file-attachment {
	margin-top: 20px;
	padding: 12px;
	background-color: #f8f9fa;
	border-radius: 6px;
	border: 1px solid #e9ecef;
}

.file-attachment a {
	color: #3498db;
	text-decoration: none;
	display: flex;
	align-items: center;
	gap: 8px;
	font-weight: 500;
	transition: color 0.3s;
}

.file-attachment a:hover {
	color: #2980b9;
	text-decoration: underline;
}

.file-icon {
	font-size: 18px;
}

.file-size {
	font-size: 12px;
	color: #7f8c8d;
	margin-left: auto;
}

.no-notifications {
	text-align: center;
	padding: 60px 40px;
	color: #7f8c8d;
	font-size: 18px;
	background-color: white;
	border-radius: 10px;
	box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.no-notifications a {
	color: #3498db;
	text-decoration: none;
	font-weight: bold;
}

.no-notifications a:hover {
	text-decoration: underline;
}

.notification-time {
	font-size: 12px;
	color: #95a5a6;
	margin-top: 5px;
}

@media (max-width: 768px) {
	.notifications-container {
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
	
	.notifications-stats {
		flex-direction: column;
		align-items: center;
	}
	
	.stat-card {
		width: 100%;
		max-width: 250px;
	}
	
	.notification-header {
		flex-direction: column;
		gap: 10px;
	}
}
    </style>
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
                <h1>Notifications</h1>
                <a href="/Online_Course_Management_System/Student/view/dashboard.php" class="back-btn">Back to Dashboard</a>
            </div>

            <div class="notifications-stats">
                <div class="stat-card stat-total">
                    <div class="stat-number"><?php echo count($notifications); ?></div>
                    <div class="stat-label">Total Notifications</div>
                </div>
                <div class="stat-card stat-important">
                    <div class="stat-number"><?php echo $important_count; ?></div>
                    <div class="stat-label">Important</div>
                </div>
            </div>

            <form method="GET" action="" class="search-container">
                <input type="text" name="search" class="search-input" placeholder="Search notifications..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="search-btn">Search</button>
                <?php if (!empty($search_query)) : ?>
                    <a href="student_notifications.php" class="clear-search">Clear</a>
                <?php endif; ?>
            </form>

            <?php if (!empty($search_query)) : ?>
                <div class="search-results-info">
                    Showing results for: "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                    <?php if (count($notifications) > 0) : ?>
                        - <?php echo count($notifications); ?> notification(s) found
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($notifications)) : ?>
                <div class="no-notifications">
                    <h2>No Notifications</h2>
                    <p>You don't have any notifications at the moment.</p>
                    <p>Check back later for updates from your administrators.</p>
                </div>
            <?php else : ?>
                <div class="notifications-container">
                    <?php foreach ($notifications as $notification) : ?>
                        <div class="notification-card <?php echo $notification['is_important'] ? 'important' : ''; ?>">
                            <div class="notification-header">
                                <div>
                                    <h3 class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></h3>
                                    <div class="notification-meta">
                                        By <?php echo htmlspecialchars($notification['admin_name'] ?? 'Administrator'); ?>
                                    </div>
                                    <div class="notification-time">
                                        <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                                        <?php if ($notification['updated_at'] != $notification['created_at']) : ?>
                                            • Updated <?php echo date('M j, Y g:i A', strtotime($notification['updated_at'])); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="notification-message">
                                <?php echo nl2br(htmlspecialchars($notification['message'])); ?>
                            </div>
                            
                            <?php if ($notification['file_path']) : 
                                $file_path = "../../uploads/notifications/" . $notification['file_path'];
                                $file_exists = file_exists($file_path);
                                $file_size = $file_exists ? filesize($file_path) : 0;
                                $file_size_formatted = $file_size > 0 ? formatFileSize($file_size) : 'Unknown size';
                            ?>
                                <div class="file-attachment">
                                    <a href="../../uploads/notifications/<?php echo $notification['file_path']; ?>" target="_blank" <?php echo $file_exists ? '' : 'style="color: #95a5a6; cursor: not-allowed;"'; ?>>
                                        <span class="file-icon">📎</span>
                                        <?php echo $file_exists ? 'Download Attachment' : 'File not available'; ?>
                                        <?php if ($file_exists) : ?>
                                            <span class="file-size">(<?php echo $file_size_formatted; ?>)</span>
                                        <?php endif; ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    function formatFileSize($bytes) {
        if ($bytes == 0) return '0 Bytes';
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
    ?>
</body>
</html>