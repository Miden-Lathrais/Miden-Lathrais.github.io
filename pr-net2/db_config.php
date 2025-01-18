<?php
// Database configuration
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = "ChaosChaosl3titr4in"; // Replace with your MySQL password
$dbname = "websitePr"; // Name of your database

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
