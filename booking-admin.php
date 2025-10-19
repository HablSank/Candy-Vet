<?php
session_start();
if(!isset($_SESSION['user'])){
    header('location:login.php');
}
// Panggil library PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Sesuaikan path ini jika kamu tidak menggunakan Composer
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); 
$dotenv->load();
include 'koneksi.php';

if(isset($_POST['submit'])) {
    // --- Bagian 1: Simpan data ke database (kode temanmu, tidak diubah) ---
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
        // --- Bagian 2: Kirim email konfirmasi ke user ---
        $mail = new PHPMailer(true);

        try {
            // --- PENGATURAN YANG PERLU KAMU UBAH ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';                     // Server SMTP Gmail
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['GMAIL_USERNAME'];                // GANTI DENGAN EMAIL GMAIL-MU
            $mail->Password   = $_ENV['GMAIL_APP_PASSWORD'];                   // GANTI DENGAN 16 KARAKTER APP PASSWORD-MU
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            // -----------------------------------------

            // Pengirim & Penerima
            $mail->setFrom('no-reply@candyvet.com', 'Admin CandyVet'); // Email "Dari" (bisa fiktif) dan Nama Pengirim
            $mail->addAddress($_POST['email_majikan'], $_POST['nm_majikan']); // Kirim ke email yang diisi di form

            // Konten Email
            $mail->isHTML(true);
            $mail->Subject = 'Bukti Booking Anda di CandyVet';
            $mail->Body    = "
                <h2>Terima Kasih, " . htmlspecialchars($_POST['nm_majikan']) . "!</h2>
                <p>Booking Anda telah kami terima. Berikut adalah detailnya:</p>
                <table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>
                    <tr><td style='background-color: #f2f2f2; width: 30%;'><strong>Nama Hewan</strong></td><td>" . htmlspecialchars($_POST['nm_hewan']) . "</td></tr>
                    <tr><td style='background-color: #f2f2f2;'><strong>Jenis Hewan</strong></td><td>" . htmlspecialchars($_POST['jenis_hewan']) . "</td></tr>
                    <tr><td style='background-color: #f2f2f2;'><strong>Keluhan</strong></td><td>" . htmlspecialchars($_POST['keluhan']) . "</td></tr>
                </table>
                <p>Mohon tunggu konfirmasi jadwal dari admin kami via WhatsApp. Jangan balas email ini.</p>
            ";

            $mail->send(); // Kirim email

        } catch (Exception $e) {
            // Jika email gagal, proses tetap lanjut, tidak perlu hentikan user
        }

        // --- Bagian 3: Siapkan & Redirect ke WhatsApp ---
        
        // --- PENGATURAN YANG PERLU KAMU UBAH ---
        $nomorAdminWA = $_ENV['ADMIN_WHATSAPP'];
        // -----------------------------------------

        $pesanWA = "Halo Admin CandyVet, saya ingin booking jadwal atas nama:\n\n" .
                   "Nama Majikan: " . $_POST['nm_majikan'] . "\n" .
                   "No. Telp: " . $_POST['no_tlp_majikan'] . "\n" .
                   "Nama Hewan: " . $_POST['nm_hewan'] . "\n" .
                   "Jenis Hewan: " . $_POST['jenis_hewan'] . "\n" .
                   "Keluhan: " . $_POST['keluhan'] . "\n\n" .
                   "Mohon konfirmasi jadwalnya. Terima kasih.";
        
        $pesanWAEncoded = urlencode($pesanWA);
        $whatsappURL = "https://api.whatsapp.com/send?phone=" . $nomorAdminWA . "&text=" . $pesanWAEncoded;

        // Beri notifikasi ke user lalu redirect
        echo "<script>
                alert('Booking Terkirim! Anda akan dialihkan ke WhatsApp. Bukti booking juga telah dikirim ke email Anda.');
                window.location.href = '$whatsappURL';
              </script>";

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

        <header class="bg-[#FEF3E2]">
        <div class="container mx-auto flex items-center justify-between h-20 px-4">
            <div class="flex items-center space-x-2">
                <img src="assets/logo.png" alt="CandyVet Logo" class="h-14 w-auto">
                <span class="font-bold text-xl lg:text-2xl text-gray-800"><span class="text-[#FAB12F]">Candy</span><span class="text-[#F4631E]">Vet</span></span>
            </div>

            <a href="dashboard.php" class="flex items-center gap-2 bg-[#FEF3E2] border-[#9E00BA] border-2 rounded-lg py-2 px-4">
                <span class="hidden sm:inline text-[#9E00BA] text-xl font-bold">Kembali</span>
                <img src="assets/kembali.png" alt="logo kembali" class="w-auto h-8">
            </a>
        </div>
        </header>


    <section class="max-w-xl mx-auto pt-8 my-12 px-4">
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
                    Tambah Booking
                </button>
                <button type="reset" name="reset" class="w-full py-3 px-6 text-lg font-semibold text-[#FA812F] bg-transparent border-2 border-[#FA812F] rounded-xl hover:-translate-y-0.5 transform transition shadow-lg hover:shadow-xl">
                    Reset Form
                </button>
            </div>
            
        </form>
    </section>

</body>
</html>