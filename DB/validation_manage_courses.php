<?php
include 'db_connection.php'; 

function validateCourse($course_name, $course_code, $seats_available) {
    $errors = [];

    if (empty($course_name)) {
        $errors[] = "Course name is required.";
    }
    if (empty($course_code)) {
        $errors[] = "Course code is required.";
    }
    if (!empty($seats_available) && (!is_numeric($seats_available) || $seats_available < 0)) {
        $errors[] = "Seats must be a positive number.";
    }
    return $errors;
}

if (isset($_POST['logout-btn'])) {
    session_destroy();
    header("Location: ../../User_Authentication/view/login.php");
    exit();
}

//Add Course
if (isset($_POST['add_course'])) {
    $course_name = $_POST['course_name'];
    $course_code = $_POST['course_code'];
    $description = $_POST['description'];
    $seats_available = $_POST['seats_available'];

    $errors = validateCourse($course_name, $course_code, $seats_available);

    if (empty($errors)) {
        $sql = "INSERT INTO courses (course_name, course_code, description, seats_available) 
                VALUES ('$course_name', '$course_code', '$description', '$seats_available')";
        $conn->query($sql);
    }
}

//Update Course
if (isset($_POST['update_course'])) {
    $course_id = $_POST['course_id'];
    $course_name = $_POST['course_name'];
    $course_code = $_POST['course_code'];
    $description = $_POST['description'];
    $seats_available = $_POST['seats_available'];

    $errors = validateCourse($course_name, $course_code, $seats_available);

    if (empty($errors)) {
        $sql = "UPDATE courses SET 
                course_name='$course_name',
                course_code='$course_code',
                description='$description',
                seats_available='$seats_available'
                WHERE course_id=$course_id";
        $conn->query($sql);
    }
}

//Delete Course
if (isset($_POST['delete_course'])) {
    $course_id = $_POST['course_id'];
    $sql = "DELETE FROM courses WHERE course_id=$course_id";
    $conn->query($sql);
}

// Fetch courses for display
$result = $conn->query("SELECT * FROM courses ORDER BY course_id DESC");
$courses = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
