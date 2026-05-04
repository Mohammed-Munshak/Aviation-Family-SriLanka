<?php
$servername = "localhost:3307"; // Added the port number here
$username = "root";
$password = ""; 
$dbname = "aviation_family_db"; 

// The rest of your logic remains the same
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>