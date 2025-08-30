<?php
session_start();

if (!isset($_SESSION['courses'])) {
    $_SESSION['courses'] = [];
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_course'])) {
    $course_name = trim($_POST["course_name"] ?? "");
    $course_code = trim($_POST["course_code"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $seats_available = trim($_POST["seats_available"] ?? "");

    if (empty($course_name)) $errors[] = "Course Name is required.";
    if (empty($course_code)) $errors[] = "Course Code is required.";
    if (!empty($seats_available) && (!is_numeric($seats_available) || $seats_available < 0)) {
        $errors[] = "Seats Available must be a positive number.";
    }

    if (empty($errors)) {
        $_SESSION['courses'][] = [
            "id" => uniqid(),
            "name" => htmlspecialchars($course_name),
            "code" => htmlspecialchars($course_code),
            "description" => htmlspecialchars($description),
            "seats" => htmlspecialchars($seats_available)
        ];
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_course'])) {
    foreach ($_SESSION['courses'] as &$course) {
        if ($course['id'] === $_POST['course_id']) {
            $course['name'] = htmlspecialchars($_POST['course_name']);
            $course['code'] = htmlspecialchars($_POST['course_code']);
            $course['description'] = htmlspecialchars($_POST['description']);
            $course['seats'] = htmlspecialchars($_POST['seats_available']);
        }
    }
    unset($course);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_course'])) {
    $_SESSION['courses'] = array_filter($_SESSION['courses'], function($c) {
        return $c['id'] !== $_POST['course_id'];
    });
}
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
            <li><a href="#">👥 Manage Students</a></li>
            <li><a href="#">📝 Registrations</a></li>
            <li><a href="#">📊 Reports</a></li>
            <li><a href="#">🔔 Notifications</a></li>
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

    <?php if (!empty($_SESSION['courses'])) : ?>
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
            <?php foreach ($_SESSION['courses'] as $c) : ?>
            <tr>
                <td><?php echo $c["id"]; ?></td>
                <td><?php echo $c["name"]; ?></td>
                <td><?php echo $c["code"]; ?></td>
                <td><?php echo $c["description"]; ?></td>
                <td><?php echo $c["seats"]; ?></td>
                <td>
                    <form action="" method="POST" class="action-form">
                        <input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
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
