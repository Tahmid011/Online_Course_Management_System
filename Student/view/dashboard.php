<?php
session_start();
if (isset($_POST['logout-btn'])) {
    session_destroy();
    header("Location: ../../User_Authentication/view/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Student Dashboard</title>
        <link rel="stylesheet" href="../css/dashboard.css">
    </head>
    <body>
        <form class="dashboard_student" method="POST">
            <div class="dashboard">
                <aside class="sidebar">
                    <h2 class="logo">CourseReg</h2>
                    <ul class="menu">
                        <li><a href="#">👤 Profile</a></li>
                        <li><a href="#">📚 Available Courses</a></li>
                        <li><a href="#">📝 My Enrollments</a></li>
                        <li><a href="#">🔔 Notifications</a></li>
                    </ul>

                    <button type="submit" name="logout-btn" class="logout">Logout</button>
                </aside>

                <div class="main">
                    <header class="header">
                        <h1>Welcome, Student</h1>
                    </header>

                    <section class="content">
                        <div class="card">
                            <h3>Available Courses</h3>
                            <p>8</p>
                        </div>
                        <div class="card">
                            <h3>My Enrollments</h3>
                            <p>3</p>
                        </div>
                        <div class="card">
                            <h3>Notifications</h3>
                            <p>2 New</p>
                        </div>
                    </section>
                </div>
            </div>
        </form>
    </body>
</html>
