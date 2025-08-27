<!DOCTUPE html>
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
                <input type="email" id="email" name="email" placeholder="Enter your Email" required>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>

                <button type="submit" id="login-btn" name="login-btn">Login</button>
            </div>
            <div class="signup">
            <p>Don't have an account </p>
            <a href="register.php" class="signup-button">Sign Up</a>
            </div>
        </form>
    </body>
</html>