<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "be22_exam5_animal_adoption_juliustimgum"; // Update this line with your new database name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
