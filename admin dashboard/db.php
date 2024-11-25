<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_db";  // Ensure this matches the name of the database you created

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>