<?php
// Database connection information
$host = "localhost";    // Database host
$username = "root";     // Database username
$password = "";         // Database password 
$database = "plantdb";   // Database name

// Create database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8");
?>