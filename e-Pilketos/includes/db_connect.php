<?php

$servername = getenv('DB_HOST') ?: "db"; 
$username = getenv('DB_USER') ?: "syaif";
$password = getenv('DB_PASSWORD') ?: "123";
$dbname = getenv('DB_NAME') ?: "e_pilketos";


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("<div class='container message error'>Koneksi database gagal: " . $conn->connect_error . "</div>");
}

