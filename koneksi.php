<?php
$host = "localhost";
$user = '***REMOVED***';
$pass = '';
$db = '***REMOVED***';

$conn = mysqli_connect($host, $user, $pass, $db);

if(!$conn){
    echo 'Error: ' . mysqli_connect_error($conn);
}
?>