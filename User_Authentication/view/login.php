<?php
include "../../DB/db_connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = trim($_POST['studentId']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM register WHERE studentId='$studentId' AND password='$password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        session_start();
        $_SESSION['studentId'] = $studentId;

        if (preg_match('/^\d{2}-\d{5}-\d{1}$/', $studentId)) {
            header("Location: /Online_Course_Management_System/Student/view/dashboard.php");
            exit();
        } elseif (preg_match('/^\d{5}$/', $studentId)) {
            header("Location: /Online_Course_Management_System/Admin/view/dashboard.php");
            exit();
        } else {
            echo "Invalid ID format.";
        }
    } else {
        echo "Invalid student ID or password.";
    }

    $conn->close();

}
?>
<!DOCTYPE html>
<html lang="en">
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