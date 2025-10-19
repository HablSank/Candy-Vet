<?php
$host = "localhost";
$user = '***REMOVED***';
$pass = '';
$db = '***REMOVED***01';

$conn = mysqli_connect($host, $user, $pass, $db);

if(!$conn){
    echo 'Error: ' . mysqli_connect_error($conn);
}
?>