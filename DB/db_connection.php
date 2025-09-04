<?php
$host = "localhost";  // database r server k store kore
$user = "root"; // username
$pass = "";  // password
$dbname = "web_project"; // database name

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>