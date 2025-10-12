<?php
$host = "localhost";
$user = 'root';
$pass = '';
$db = 'db_candyvet01';

$conn = mysqli_connect($host, $user, $pass, $db);

if(!$conn){
    echo 'Error: ' . mysqli_connect_error($conn);
}
?>