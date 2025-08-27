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
        <title>Admin Dashboard</title>
        <link rel="stylesheet" href="../css/dashboard.css">
    </head>
    <body>
        <form class="dashboard_admin" method="POST">
            <div class="dashboard">
                <aside class="sidebar">
                    <h2 class="logo">CourseReg</h2>
                    <ul class="menu">
                        <li><a href="#">👤 Profile</a></li>
                        <li><a href="#">📚 Manage Courses</a></li>
                        <li><a href="#">👥 Manage Students</a></li>
                        <li><a href="#">📝 Registrations</a></li>
                        <li><a href="#">📊 Reports</a></li>
                        <li><a href="#">🔔 Notifications</a></li>
                    </ul>

                    <button type="submit" name="logout-btn" class="logout">Logout</button>
                </aside>

                <div class="main">
                    <header class="header">
                        <h1>Welcome, Admin</h1>
                    </header>

                    <section class="content">
                        <div class="card">
                            <h3>Total Courses</h3>
                            <p>12</p>
                        </div>
                        <div class="card">
                            <h3>Total Students</h3>
                            <p>230</p>
                        </div>
                        <div class="card">
                            <h3>Total Registered Students</h3>
                            <p>300</p>
                        </div>
                    </section>
                </div>
            </div>
        </form>
    </body>
</html>
