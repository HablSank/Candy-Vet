    <?php
    session_start();
    include "koneksi.php";
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - CandyVet</title>
        <script src="https://cdn.tailwindcss.com"></script> 
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    </head>
    <body class="bg-[#FEF3E2] min-h-screen flex items-center justify-center p-4" style="font-family: 'Poppins', sans-serif;">
        <?php 
        if(isset($_POST['username'])){
            $username = $_POST['username'];
            $password = md5($_POST['password']);

        $stmt = mysqli_prepare($conn, "SELECT * FROM login_admin WHERE username=? AND password=?");
        mysqli_stmt_bind_param($stmt, "ss", $username, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
            
            if(mysqli_num_rows($result) > 0){
                $data = mysqli_fetch_array($result);
                $_SESSION['user'] = $data;
                echo '<script>alert("Selamat datang, '.$data['nama'].'"); location.href="admin.php"</script>';
            }else{
                echo '<script>alert("Username/password tidak sesuai");</script>';
            }
        }
        
        ?>

    <div class="bg-transparent rounded-3xl overflow-hidden max-w-4xl w-full md:flex">
        
        <div class="hidden md:flex flex-col items-center justify-center md:w-1/2 ">
            <a class="flex flex-col items-center space-y-2 relative top-10">
                <img src="assets/logo.png" alt="CandyVet Logo" class="h-[180px] w-auto">
            </a>
            <p class="text-xl font-semibold italic text-center">
                <span class="text-[#FAB12F]">Best </span><span class="text-[#9E00BA]">Vet </span><span class="text-[#F4631E]">For Your </span><span class="text-[#FAB12F]">Best </span><span class="text-[#9E00BA]">Pet</span>
            </p>
            <img src="assets/hero_image.png" alt="Ilustrasi hewan" class="w-auto h-[450px]">
        </div>
        
        <div class="w-full md:w-1/2 p-8 sm:p-12 bg-gradient-to-br from-[#FAB12F] to-[#F5A623] flex flex-col justify-center rounded-3xl shadow-2xl my-24">
            <h2 class="text-4xl font-extrabold text-gray-900 mb-12 text-center">Masuk</h2>
            
            <form action="login.php" method="post" class="space-y-6">
                <div>
                    <label for="username" class="block text-base font-bold text-gray-900 mb-3">Username</label>
                    <div class="relative">
                        <input type="text" id="username" name="username" placeholder="Username" required class="w-full px-4 py-4 pr-12 text-base border-0 rounded-2xl bg-white text-gray-900 placeholder-gray-400 outline-none focus:shadow-md focus:ring-0 transition shadow-sm">
                        <span class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-900">
                        <i class="fas fa-user text-xl"></i>
                        </span>
                    </div>
                </div>
                <div>
                    <label for="password" class="block text-base font-bold text-gray-900 mb-3">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" placeholder="Password" required class="w-full px-4 py-4 pr-12 text-base border-0 rounded-2xl bg-white text-gray-900 placeholder-gray-400 outline-none focus:shadow-md focus:ring-0 transition shadow-sm">
                        <span id="togglepassword" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-900 cursor-pointer">
                        <i class="fas fa-eye-slash text-xl"></i>
                        </span>
                    </div>
                </div>
                
                <button type="submit" name="masuk" class="w-full py-4 px-6 text-xl font-bold text-white bg-[#F4631E] rounded-2xl hover:bg-opacity-90 transition transform hover:scale-105 shadow-lg">
                    Login
                </button>
            </form>
        </div>
    </div>

    <script src="login.js"></script>
    </body>
    </html>