<?php
include '../../DB/validation_manage_courses.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Courses</title>
    <link rel="stylesheet" href="../css/manage_courses.css">
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
                <li><a href="admin_notifications.php">🔔 Notifications</a></li>
            </ul>
            <form action="" method="POST">
                <button type="submit" name="logout-btn" class="logout">Logout</button>
            </form>
        </aside>

        <div class="main">
            <h1>Manage Courses</h1>

            <?php if (!empty($errors)) : ?>
            <div class="error-box">
                <ul>
                <?php foreach ($errors as $error) : ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <section class="course-section">
                <h2>Add New Course</h2>
                <form action="" method="POST" class="inline-form">
                    <input type="text" name="course_name" placeholder="Course Name" required>
                    <input type="text" name="course_code" placeholder="Course Code" required>
                    <input type="text" name="description" placeholder="Description">
                    <input type="number" name="seats_available" placeholder="Seats Available">
                    <button type="submit" name="add_course">Add Course</button>
                </form>
            </section>

            <?php if (!empty($courses)) : ?>
            <section class="course-section">
                <h2>Preview Added Courses</h2>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Course Name</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Seats</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($courses as $c) : ?>
                    <tr>
                        <td><?php echo $c["course_id"]; ?></td>
                        <td><?php echo $c["course_name"]; ?></td>
                        <td><?php echo $c["course_code"]; ?></td>
                        <td><?php echo $c["description"]; ?></td>
                        <td><?php echo $c["seats_available"]; ?></td>
                        <td>
                            <form action="" method="POST" class="action-form">
                                <input type="hidden" name="course_id" value="<?php echo $c['course_id']; ?>">
                                <input type="text" name="course_name" placeholder="New Name">
                                <input type="text" name="course_code" placeholder="New Code">
                                <input type="text" name="description" placeholder="New Description">
                                <input type="number" name="seats_available" placeholder="New Seats">
                                <button type="submit" name="update_course">Update</button>
                                <button type="submit" name="delete_course" onclick="return confirm('Delete this course?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </section>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
