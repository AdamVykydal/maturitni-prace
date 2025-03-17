<?php
$host = "localhost";
$user = "root"; 
$password = ""; 
$database = "autoskola"; 

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Chyba připojení k DB: " . $conn->connect_error);
}
?>