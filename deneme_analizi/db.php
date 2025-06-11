<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$database = "proje2";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Veritabanına bağlanılamadı: " . $conn->connect_error);
}
?>
