<?php
session_start();
include "connect.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php 
    if(isset($_POST['username'])){
        $username = $_POST['username'];
        $password = md5($_POST['password']);

        $query = mysqli_query($connect, "SELECT*FROM login_admin where username='$username' and password='$password'");
        
        if(mysqli_num_rows($query) > 0){
            $data = mysqli_fetch_array($query);
            $_SESSION['user'] = $data;
            echo '<script>alert("Selamat datang, '.$data['nama'].'"); location.href="admin.php"</script>';
        }else{
            echo '<script>alert("Username/password tidak sesuai");</script>';
        }
    }
    
    ?>
    <div class="container">
        <div class="form-box" id="login-form">
            <form action="" method="post">
                <h2>Masuk</h2>
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="masuk">Masuk</button>
            </form>
        </div>
    </div>
</body>
</html>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background-color: #fef3e2;
        color:black;
    }

    .container{
        margin: 0 15px;
    }

    .form-box{
        width: 100%;
        max-width: 450px;
        padding: 30px;
        background-color: #fab12f;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h2{
        font-size: 34px;
        text-align: center;
        margin-bottom: 20px;
    }

    input{
        width: 100%;
        padding: 12px;
        background-color: #eeee;
        border-radius: 6px;
        border: none;
        outline: none;
        font-size: 16px;
        color:black;
        margin-bottom: 20px;
    }

    button{
        width: 100%;
        padding: 12px;
        background-color: #f4631e;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        font-size: 16px;
        color:#fef3e2;
        font-weight: 500;
        margin-bottom: 20px;
        transition: 0.5s;
    }
</style>