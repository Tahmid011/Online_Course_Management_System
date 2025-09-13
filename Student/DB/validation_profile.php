<?php
session_start();

include '../../DB/db_connection.php';

$errors = [];
$success = "";
$user_data = [];

if (!isset($_SESSION['studentId'])) {
    header("Location: ../../User_Authentication/view/login.php");
    exit();
}

$user_id = $_SESSION['studentId'];

$result = $conn->query("SELECT * FROM register WHERE studentId = '$user_id'");
if ($result && $result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
} else {
    $errors[] = "User not found!";
}

if (isset($_POST['logout-btn'])) {
    session_destroy();
    header("Location: ../../User_Authentication/view/login.php");
    exit();
}

if (isset($_POST['change-name'])) {
    $name = trim($_POST['name']);
    if (empty($name)) {
        $errors[] = "Name cannot be empty.";
    } else {
        $name = $conn->real_escape_string($name);
        
        if ($conn->query("UPDATE register SET name='$name' WHERE studentId='$user_id'")) {
            $success = "Name updated successfully!";
            $user_data['name'] = $name;
        } else {
            $errors[] = "Error updating name: " . $conn->error;
        }
    }
}

if (isset($_POST['change-phone'])) {
    $phone = trim($_POST['phone']);
    if (empty($phone)) {
        $errors[] = "Phone cannot be empty.";
    } else {
        $phone = $conn->real_escape_string($phone);
        
        if ($conn->query("UPDATE register SET phone='$phone' WHERE studentId='$user_id'")) {
            $success = "Phone updated successfully!";
            $user_data['phone'] = $phone;
        } else {
            $errors[] = "Error updating phone: " . $conn->error;
        }
    }
}

if (isset($_POST['change-email'])) {
    $email = trim($_POST['email']);
    if (empty($email)) {
        $errors[] = "Email cannot be empty.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } else {
        $email = $conn->real_escape_string($email);
        
        if ($conn->query("UPDATE register SET email='$email' WHERE studentId='$user_id'")) {
            $success = "Email updated successfully!";
            $user_data['email'] = $email;
        } else {
            $errors[] = "Error updating email: " . $conn->error;
        }
    }
}

if (isset($_POST['change-password'])) {
    $current = $_POST['current-pass'];
    $new = $_POST['new-pass'];
    $confirm = $_POST['confirm-pass'];

    if (empty($current) || empty($new) || empty($confirm)) {
        $errors[] = "All password fields are required.";
    } else {
        $res = $conn->query("SELECT password FROM register WHERE studentId='$user_id'");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            if ($current !== $row['password']) {
                $errors[] = "Current password incorrect.";
            } elseif ($new !== $confirm) {
                $errors[] = "New passwords do not match.";
            } else {
                $new = $conn->real_escape_string($new);
                if ($conn->query("UPDATE register SET password='$new', confirmPassword='$new' WHERE studentId='$user_id'")) {
                    $success = "Password updated successfully!";
                } else {
                    $errors[] = "Error updating password: " . $conn->error;
                }
            }
        } else {
            $errors[] = "Error verifying current password.";
        }
    }
}

$conn->close();
?>