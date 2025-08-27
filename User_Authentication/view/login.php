<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['studentId'] ?? '');
    $password = trim($_POST['password'] ?? '');
 
    /*if (preg_match('/^\d{2}-\d{5}-\d{1}$/', $id)) {
        header('Location: /Online_Course_Management_System/Student/dashboard.php');
        exit();
    }
    elseif (preg_match('/^\d{5}$/', $id)) {
        header('Location: /Online_Course_Management_System/Admin/dashboard.php');
        exit();
    }
    else {
        $error_message = "Invalid ID format.";
    } */

    $student_id = '22-48616-3';
    $student_pass = '11';
    $admin_id = '48616';
    $admin_pass = '22';

    if ($id === $student_id && $password === $student_pass) {
        header('Location: /Online_Course_Management_System/Student/view/dashboard.php');
        exit();
    }
    elseif ($id === $admin_id && $password === $admin_pass) {
        header('Location: ../../Admin/view/dashboard.php');
        exit();
    }
    else {
        $error_message = "Invalid ID or password.";
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Login Page</title>
        <link rel="stylesheet" href="/Online_Course_Management_System/User_Authentication/css/login.css">
    </head>
    <body>
        <form class="login" method="POST" >
            <h1>Online Course Registration</h1>
            <div id="container">
                <input type="text" id="studentId" name="studentId" placeholder="Enter your ID" required>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>

                <button type="submit" id="login-btn" name="login-btn">Login</button>
            </div>
            <?php if (!empty($error_message)): ?>
                <p style="color:red;"><?= $error_message ?></p>
            <?php endif; ?>
            <div class="signup">
            <p>Don't have an account </p>
            <a href="register.php" class="signup-button">Sign Up</a>
            </div>
        </form>
    </body>
</html>