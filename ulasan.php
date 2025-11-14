<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); 
$dotenv->load();
include 'koneksi.php';

include 'koneksi.php'; 


if (!isset($conn) || $conn->connect_error) {
    
    die("Error KONEKSI DATABASE: " . ($conn->connect_error ?? "Variabel \$conn tidak ditemukan."));
}



if (isset($_POST['submit'])) {
    
    
    $nm_majikan = htmlspecialchars(trim($_POST['nm_majikan']));
    $nm_hewan = htmlspecialchars(trim($_POST['nm_hewan']));
    $ulasan = htmlspecialchars(trim($_POST['ulasan']));
    
   
    $tgl_ulasan = date("Y-m-d H:i:s"); 
    $status_pending = 'Pending'; 

    
    if (empty($nm_majikan) || empty($nm_hewan) || empty($ulasan)) {
        
    } else {
        
       
        $sql = "INSERT INTO tb_ulasan (nm_majikan, nm_hewan, ulasan, status, tgl_ulasan) VALUES (?, ?, ?, ?, ?)";
        
      
        $stmt = $conn->prepare($sql);
        
        
        if ($stmt === false) {
            die("Error PREPARE SQL: " . $conn->error); 
        }

       
        $stmt->bind_param("sssss", $nm_majikan, $nm_hewan, $ulasan, $status_pending, $tgl_ulasan);

        
        if ($stmt->execute()) {
           
            session_start();
            $_SESSION['ulasan_sukses'] = true;
            header("Location: ulasan"); 
            exit();

        } else {
           
            die("Error EKSEKUSI SQL: " . $stmt->error); 
        }

       
    }
}


session_start();
$script_sukses = false;
if (isset($_SESSION['ulasan_sukses']) && $_SESSION['ulasan_sukses'] === true) {
    unset($_SESSION['ulasan_sukses']);
    $script_sukses = true;
}


if (isset($conn)) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Ulasan - CandyVet</title>
    <link href="/dist/output.css" rel="stylesheet"> 
    <script src="https://cdn.tailwindcss.com"></script> 
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-[#FEF3E2]" style="font-family: 'Poppins', sans-serif;">
    
        <nav class="fixed top-0 left-0 right-0 z-50 bg-[#FEF3E2]/80 backdrop-blur-md border- md:border-gray-200/50">
        <div class="container mx-auto flex items-center justify-between h-20 px-4">
            <a href="index" class="flex items-center space-x-2">
                <img src="assets/logo.png" alt="CandyVet Logo" class="h-14 w-auto xl:h-24 xl:w-24">
                <span class="font-bold text-xl lg:text-2xl text-gray-800"><span class="text-[#FAB12F]">Candy</span><span class="text-[#F4631E]">Vet</span></span>
            </a>

            <ul class="hidden md:flex items-center space-x-4 lg:space-x-6 xl:space-x-8">
                <li><a href="index#beranda" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Beranda</a></li>
                <li><a href="index#tentang" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Tentang</a></li>
                <li><a href="index#layanan" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Layanan</a></li>
                <li><a href="index#fasilitas" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Fasilitas</a></li>
                <li><a href="index#footer" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Lokasi</a></li>
            </ul>

            <div class="md:hidden">
                <button id="hamburger-btn">
                    <svg id="hamburger-icon" class="h-6 w-6 text-[#9E00BA]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                    <svg id="close-icon" class="h-6 w-6 text-[#9E00BA] hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="md:hidden absolute top-20 w-full bg-[#FEF3E2]/90 backdrop-blur-lg overflow-hidden transition-all duration-300 ease-in-out max-h-0">
            <ul class="flex flex-col items-center space-y-4 pt-8 pb-8">
                <li><a href="index#beranda" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Beranda</a></li>
                <li><a href="index#tentang" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Tentang</a></li>
                <li><a href="index#layanan" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Layanan</a></li>
                <li><a href="index#fasilitas" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Fasilitas</a></li>
                <li><a href="index#footer" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Lokasi</a></li>
            </ul>
        </div>
    </nav>


    <section class="max-w-xl mx-auto pt-24 my-12 px-4">
        <h2 class="text-center text-2xl sm:text-3xl font-bold text-gray-800 mb-10">
            Formulir Ulasan CandyVet
        </h2>

        <hr class="w-1/2 mx-auto border-t-2 border-[#FA812F] mb-10">

        <form action="ulasan" method="POST" class="space-y-6">

            <div>
                <label for="nm_majikan" class="block text-lg font-semibold text-gray-700 mb-2">Nama Majikan</label>
                <input type="text" name="nm_majikan" id="nm_majikan" placeholder="Masukkan Nama Lengkap Anda" required class="w-full px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition">
            </div>

            <div>
                <label for="nm_hewan" class="block text-lg font-semibold text-gray-700 mb-2">Nama Hewan</label>
                <input type="text" name="nm_hewan" id="nm_hewan" placeholder="Masukkan Nama Hewan Anda" required class="w-full px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition">
            </div>

            
            <div>
                <label for="ulasan" class="block text-lg font-semibold text-gray-700 mb-2">Ulasan</label>
                <textarea name="ulasan" id="ulasan" placeholder="Masukkan Ulasan Anda" rows="4" required class="w-full px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition resize-y"></textarea>
            </div>

            <div class="pt-4 space-y-3">
                <button type="submit" name="submit" class="w-full py-3 px-6 text-lg font-semibold text-white bg-[#FA812F] rounded-xl hover:bg-[#E37129] hover:-translate-y-0.5 transform transition shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Kirim Ulasan
                </button>
                <button type="button" onclick="confirmreset()" class="w-full py-3 px-6 text-lg font-semibold text-[#FA812F] bg-transparent border-2 border-[#FA812F] rounded-xl hover:-translate-y-0.5 transform transition shadow-lg hover:shadow-xl">
                    Reset Form
                </button>
            </div>
            
        </form>
    </section>

    <script src="ulasan.js"></script>
    
   
    <?php if ($script_sukses): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Ulasan Terkirim!',
            text: 'Terima kasih atas ulasan Anda. Ulasan akan ditampilkan setelah diverifikasi oleh admin.',
            confirmButtonColor: '#FA812F'
        });
    });
        </script>
    <?php endif; ?>
</body>
</html>