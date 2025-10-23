<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); 
$dotenv->load();
$host = $_ENV['DB_HOST']; 
$user = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];
$db   = $_ENV['DB_DATABASE'];

$conn = mysqli_connect($host, $user, $pass, $db);

if(!$conn){
    echo 'Error: ' . mysqli_connect_error();
}
?>