<?php
$servername = "localhost";
$username = "root"; // or your DB user
$password = ""; // or your DB password
$database = "budget_tracker";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
