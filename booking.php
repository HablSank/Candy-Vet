<?php
include 'koneksi.php';

if(isset($_POST['submit'])) {
    $stmt = $conn->prepare("INSERT INTO tb_form (nm_majikan, email_majikan, no_tlp_majikan, nm_hewan, jenis_hewan, usia_hewan, jenis_kelamin_hewan, keluhan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssssssss", 
        $_POST['nm_majikan'],
        $_POST['email_majikan'],
        $_POST['no_tlp_majikan'],
        $_POST['nm_hewan'],
        $_POST['jenis_hewan'],
        $_POST['usia_hewan'],
        $_POST['jenis_kelamin_hewan'],
        $_POST['keluhan']
    );
    
    if($stmt->execute()){
        echo '<script>alert("Data Berhasil Disimpan! Terima kasih sudah melakukan booking, mohon tunggu konfirmasi dari admin via WhatsApp."); window.location.href="booking.php";</script>';
    } else {
        echo '<script>alert("Data Gagal Disimpan, silakan coba lagi.");</script>';
    }
    
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Booking - CandyVet</title>
    <link href="/dist/output.css" rel="stylesheet"> 
    <script src="https://cdn.tailwindcss.com"></script> 
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-[#FEF3E2]" style="font-family: 'Poppins', sans-serif;">
    
        <nav class="fixed top-0 left-0 right-0 z-50 bg-[#FEF3E2]/80 backdrop-blur-md border- md:border-gray-200/50">
        <div class="container mx-auto flex items-center justify-between h-20 px-4">
            <a href="index.html" class="flex items-center space-x-2">
                <img src="assets/logo.png" alt="CandyVet Logo" class="h-14 w-auto xl:h-24 xl:w-24">
                <span class="font-bold text-xl lg:text-2xl text-gray-800"><span class="text-[#FAB12F]">Candy</span><span class="text-[#F4631E]">Vet</span></span>
            </a>

            <ul class="hidden md:flex items-center space-x-4 lg:space-x-6 xl:space-x-8">
                <li><a href="index.html#beranda" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Beranda</a></li>
                <li><a href="index.html#tentang" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Tentang</a></li>
                <li><a href="index.html#layanan" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Layanan</a></li>
                <li><a href="index.html#fasilitas" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Fasilitas</a></li>
                <li><a href="index.html#footer" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Lokasi</a></li>
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
                <li><a href="index.html#beranda" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Beranda</a></li>
                <li><a href="index.html#tentang" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Tentang</a></li>
                <li><a href="index.html#layanan" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Layanan</a></li>
                <li><a href="index.html#fasilitas" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Fasilitas</a></li>
                <li><a href="index.html#footer" class="font-semibold text-gray-600 hover:text-[#9E00BA]">Lokasi</a></li>
            </ul>
        </div>
    </nav>


    <section class="max-w-xl mx-auto pt-24 my-12 px-4">
        <h2 class="text-center text-2xl sm:text-3xl font-bold text-gray-800 mb-10">
            Formulir Booking CandyVet
        </h2>

        <hr class="w-1/2 mx-auto border-t-2 border-[#FA812F] mb-10">

        <form action="booking.php" method="POST" class="space-y-6">

            <div>
                <label for="nm_majikan" class="block text-lg font-semibold text-gray-700 mb-2">Nama Majikan</label>
                <input type="text" name="nm_majikan" id="nm_majikan" placeholder="Masukkan Nama Lengkap Anda" required class="w-full px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition">
            </div>

            <div>
                <label for="email_majikan" class="block text-lg font-semibold text-gray-700 mb-2">Email Majikan</label>
                <input type="email" name="email_majikan" id="email_majikan" placeholder="Masukkan Email Anda" required class="w-full px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition">
            </div>

            <div>
                <label for="no_tlp_majikan" class="block text-lg font-semibold text-gray-700 mb-2">No. Telepon</label>
                <input type="text" name="no_tlp_majikan" id="no_tlp_majikan" placeholder="Masukkan No. Telepon Anda" required class="w-full px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition">
            </div>

            <div>
                <label for="nm_hewan" class="block text-lg font-semibold text-gray-700 mb-2">Nama Hewan</label>
                <input type="text" name="nm_hewan" id="nm_hewan" placeholder="Masukkan Nama Hewan" required class="w-full px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition">
            </div>

            <div>
                <label for="jenis_hewan" class="block text-lg font-semibold text-gray-700 mb-2">Jenis Hewan</label>
                <input type="text" name="jenis_hewan" id="jenis_hewan" placeholder="Contoh: Kucing, Anjing, Kelinci" required class="w-full px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition">
            </div>

            <div>
                <label for="usia_hewan" class="block text-lg font-semibold text-gray-700 mb-2">Usia Hewan (Tahun)</label>
                <input type="number" name="usia_hewan" id="usia_hewan" placeholder="Masukkan Usia Hewan" required class="w-full px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition">
            </div>

            <div>
                <label class="block text-lg font-semibold text-gray-700 mb-2">Jenis Kelamin Hewan</label>
                <div class="flex items-center space-x-6 p-4 border-2 border-[#FA812F] rounded-xl bg-white">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="jenis_kelamin_hewan" value="Jantan" required class="h-5 w-5 text-[#FA812F] focus:ring-orange-200">
                        <span class="text-base text-gray-800">Jantan</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="jenis_kelamin_hewan" value="Betina" required class="h-5 w-5 text-[#FA812F] focus:ring-orange-200">
                        <span class="text-base text-gray-800">Betina</span>
                    </label>
                </div>
            </div>

            <div>
                <label for="keluhan" class="block text-lg font-semibold text-gray-700 mb-2">Keluhan</label>
                <textarea name="keluhan" id="keluhan" placeholder="Masukkan Keluhan Hewan Anda" rows="4" required class="w-full px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition resize-y"></textarea>
            </div>

            <div class="pt-4 space-y-3">
                <button type="submit" name="submit" class="w-full py-3 px-6 text-lg font-semibold text-white bg-[#FA812F] rounded-xl hover:bg-[#E37129] hover:-translate-y-0.5 transform transition shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Kirim Booking
                </button>
                <button type="reset" name="reset" class="w-full py-3 px-6 text-lg font-semibold text-[#FA812F] bg-transparent border-2 border-[#FA812F] rounded-xl hover:-translate-y-0.5 transform transition shadow-lg hover:shadow-xl">
                    Reset Form
                </button>
            </div>
            
        </form>
    </section>

    <script src="script.js"></script>
</body>
</html>