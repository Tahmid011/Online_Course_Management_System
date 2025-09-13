<?php
session_start();

$upload_dir = "../../uploads/notifications/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
    file_put_contents($upload_dir . "index.html", "<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><p>Directory access is forbidden.</p></body></html>");
}

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

function handleFileUpload($file, $notification_id) {
    $target_dir = "../../uploads/notifications/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $file_name = "notification_" . $notification_id . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $file_name;
    
    if ($file["size"] > 5000000) {
        return ["error" => "File is too large. Maximum size is 5MB."];
    }
    
    $allowed_extensions = ["jpg", "jpeg", "png", "gif", "pdf", "doc", "docx"];
    if (!in_array($file_extension, $allowed_extensions)) {
        return ["error" => "Only JPG, JPEG, PNG, GIF, PDF, DOC & DOCX files are allowed."];
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ["success" => $file_name];
    } else {
        return ["error" => "Sorry, there was an error uploading your file."];
    }
}

if (isset($_POST['create_notification'])) {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $is_important = isset($_POST['is_important']) ? 1 : 0;
    
    if (empty($title) || empty($message)) {
        $errors[] = "Title and message are required.";
    } else {
        $sql = "INSERT INTO notifications (admin_id, title, message, is_important) 
                VALUES ('$admin_id', '$title', '$message', $is_important)";
        
        if ($conn->query($sql)) {
            $notification_id = $conn->insert_id;
            $file_path = null;
            
            if (!empty($_FILES['attachment']['name'])) {
                $upload_result = handleFileUpload($_FILES['attachment'], $notification_id);
                
                if (isset($upload_result['error'])) {
                    $errors[] = $upload_result['error'];
                } else {
                    $file_path = $upload_result['success'];
                    $conn->query("UPDATE notifications SET file_path = '$file_path' WHERE id = $notification_id");
                }
            }
            
            $success = "Notification created successfully!";
        } else {
            $errors[] = "Error creating notification: " . $conn->error;
        }
    }
}

if (isset($_POST['update_notification'])) {
    $notification_id = $_POST['notification_id'];
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $is_important = isset($_POST['is_important']) ? 1 : 0;
    
    if (empty($title) || empty($message)) {
        $errors[] = "Title and message are required.";
    } else {
        $sql = "UPDATE notifications SET title = '$title', message = '$message', 
                is_important = $is_important, updated_at = NOW() 
                WHERE id = $notification_id";
        
        if ($conn->query($sql)) {
            if (!empty($_FILES['attachment']['name'])) {
                $upload_result = handleFileUpload($_FILES['attachment'], $notification_id);
                
                if (isset($upload_result['error'])) {
                    $errors[] = $upload_result['error'];
                } else {
                    $file_path = $upload_result['success'];
                    $conn->query("UPDATE notifications SET file_path = '$file_path' WHERE id = $notification_id");
                }
            }
            
            $success = "Notification updated successfully!";
        } else {
            $errors[] = "Error updating notification: " . $conn->error;
        }
    }
}

if (isset($_POST['delete_notification'])) {
    $notification_id = $_POST['notification_id'];
    
    $result = $conn->query("SELECT file_path FROM notifications WHERE id = $notification_id");
    if ($result && $result->num_rows > 0) {
        $notification = $result->fetch_assoc();
        if ($notification['file_path']) {
            $file_path = "../../uploads/notifications/" . $notification['file_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
    
    $sql = "DELETE FROM notifications WHERE id = $notification_id";
    if ($conn->query($sql)) {
        $success = "Notification deleted successfully!";
    } else {
        $errors[] = "Error deleting notification: " . $conn->error;
    }
}

$notifications = [];
$result = $conn->query("SELECT n.*, r.name as admin_name 
                       FROM notifications n 
                       LEFT JOIN register r ON n.admin_id = r.studentId 
                       ORDER BY n.created_at DESC");
if ($result && $result->num_rows > 0) {
    $notifications = $result->fetch_all(MYSQLI_ASSOC);
} else {
    if ($conn->error) {
        $errors[] = "Database error: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notification Management</title>
    <link rel="stylesheet" href="../css/admin_notifications.css">
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
                <li><a href="registrations.php">📝 Registrations</a></li>
                <li><a href="admin_notifications.php" class="active">🔔 Notifications</a></li>
            </ul>
            <form action="" method="POST">
                <button type="submit" name="logout-btn" class="logout">Logout</button>
            </form>
        </aside>

        <div class="main">
            <div class="page-header">
                <h1>Notification Management</h1>
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

            <div class="notification-form">
                <h2>Create New Notification</h2>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="attachment">Attachment (Optional)</label>
                        <input type="file" id="attachment" name="attachment" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx">
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_important" name="is_important">
                            <label for="is_important">Mark as Important</label>
                        </div>
                    </div>
                    
                    <button type="submit" name="create_notification" class="submit-btn">Create Notification</button>
                </form>
            </div>

            <h2>Existing Notifications</h2>
            
            <?php if (empty($notifications)) : ?>
                <div class="no-notifications">
                    <p>No notifications created yet.</p>
                </div>
            <?php else : ?>
                <div class="notifications-container">
                    <?php foreach ($notifications as $notification) : ?>
                        <div class="notification-card <?php echo $notification['is_important'] ? 'important' : ''; ?>">
                            <div class="notification-header">
                                <div>
                                    <h3 class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></h3>
                                    <div class="notification-meta">
                                        By <?php echo htmlspecialchars($notification['admin_name'] ?? 'Admin'); ?> • 
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
                            
                            <?php if ($notification['file_path']) : ?>
                                <div class="file-attachment">
                                    <a href="../../uploads/notifications/<?php echo $notification['file_path']; ?>" target="_blank">
                                        <span class="file-icon">📎</span>
                                        Download Attachment
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="notification-actions">
                                <form action="" method="POST" enctype="multipart/form-data" class="edit-form">
                                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                    
                                    <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" name="title" value="<?php echo htmlspecialchars($notification['title']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Message</label>
                                        <textarea name="message" required><?php echo htmlspecialchars($notification['message']); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>New Attachment (Optional)</label>
                                        <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx">
                                    </div>
                                    
                                    <div class="form-group">
                                        <div class="checkbox-group">
                                            <input type="checkbox" name="is_important" <?php echo $notification['is_important'] ? 'checked' : ''; ?>>
                                            <label>Mark as Important</label>
                                        </div>
                                    </div>
                                    
                                    <div style="display: flex; gap: 10px;">
                                        <button type="submit" name="update_notification" class="submit-btn">Update</button>
                                        <button type="submit" name="delete_notification" class="delete-btn" 
                                                onclick="return confirm('Are you sure you want to delete this notification?')">
                                            Delete
                                        </button>
                                    </div>
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