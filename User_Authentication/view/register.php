<!DOCTUPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Login Page</title>
        <link rel="stylesheet" href="/Online_Course_Management_System/User_Authentication/css/register.css">
    </head>
    <body>
        <form class="register" method="POST">
            <h1>Online Course Registration</h1>
            <div id="container">
                <select name="role" required>
                    <option>Select your role</option>
                    <option>Admin</option>
                    <option>Student</option>
                </select>
                <input type="text" name="name" placeholder="Enter your full name" required>
                <input type="text" name="studentId" pattern="([0-9]{2}-[0-9]{5}-[0-9]{1})|([0-9]{5})" placeholder="Enter your ID" required>
                <select name="department" required>
                    <option>Select your department</option>
                    <option>CSE</option>
                    <option>BBA</option>
                    <option>IPE</option>
                    <option>LLB</option>
                </select>
                <input type="tel" name="phone-no" pattern="01[0-9]{9}" placeholder="Enter your phone no" required>
                <input type="email" name="email" placeholder="Enter your Email" required>
                <input type="password" name="password" placeholder="Enter your password" required>
                <input type="password" name="confirm-password" placeholder="Confirm your password " required>
                <div class="signup">
                    <button type="submit" name="signup-btn">Sign UP</button>
                </div>
            </div>
        </form>
    </body>
</html>