<?php

// Set PHP timezone (Malaysia)
date_default_timezone_set('Asia/Kuala_Lumpur');

// connection.php
$host = "localhost";
$user = "root";
$pass = ""; // your MySQL password
$db   = "e_kolej"; // your database name

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Set MySQL session timezone (Malaysia)
$conn->query("SET time_zone = '+08:00'");
?>


