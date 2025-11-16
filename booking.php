<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); 
$dotenv->load();
include 'koneksi.php';

$map_hewan = [
    'Kucing' => 0,      
    'Anjing' => 1,
    'Kelinci' => 2,
    'Burung' => 3,
    'Lainnya' => 4
];
$map_kelamin = [
    'Jantan' => 0,    
    'Betina' => 1
];

if(isset($_POST['submit'])) {
    $jenis_hewan_teks = $_POST['jenis_hewan']; 
    
    $id_jenis_hewan = $map_hewan[$jenis_hewan_teks] ?? 4;
    
    $jenis_hewan_custom = NULL;
    if ($id_jenis_hewan == 4 && !empty($_POST['hewan_lainnya'])) {
        $jenis_hewan_custom = $_POST['hewan_lainnya'];
    }

    $id_jenis_kelamin = $map_kelamin[$_POST['jenis_kelamin_hewan']] ?? 0;

    $stmt = $conn->prepare("INSERT INTO tb_form (
        nm_majikan, email_majikan, no_tlp_majikan, 
        nm_hewan, id_jenis_hewan, jenis_hewan_custom, 
        usia_hewan, id_jenis_kelamin, keluhan
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssssisiis",
        $_POST['nm_majikan'],
        $_POST['email_majikan'],
        $_POST['no_tlp_majikan'],
        $_POST['nm_hewan'],
        $id_jenis_hewan,       
        $jenis_hewan_custom,    
        $_POST['usia_hewan'],   
        $id_jenis_kelamin,      
        $_POST['keluhan']
    );
    
    if($stmt->execute()){
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['GMAIL_USERNAME'];
            $mail->Password   = $_ENV['GMAIL_APP_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('no-reply@candyvet.com', 'Admin CandyVet');
            $mail->addAddress($_POST['email_majikan'], $_POST['nm_majikan']);

            $display_hewan_email = $_POST['jenis_hewan'];
            if ($display_hewan_email == 'Lainnya' && !empty($_POST['hewan_lainnya'])) {
                $display_hewan_email = $_POST['hewan_lainnya'];
            }

            $mail->isHTML(true);
            $mail->Subject = 'Bukti Booking Anda di CandyVet';
            $mail->Body    = "
                <h2>Terima Kasih, " . htmlspecialchars($_POST['nm_majikan']) . "!</h2>
                <p>Booking Anda telah kami terima. Berikut adalah detailnya:</p>
                <table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>
                    <tr><td style='background-color: #f2f2f2; width: 30%;'><strong>Nama Hewan</strong></td><td>" . htmlspecialchars($_POST['nm_hewan']) . "</td></tr>
                    <tr><td style='background-color: #f2f2f2;'><strong>Jenis Hewan</strong></td><td>" . htmlspecialchars($display_hewan_email) . "</td></tr>
                    <tr><td style='background-color: #f2f2f2;'><strong>Keluhan</strong></td><td>" . htmlspecialchars($_POST['keluhan']) . "</td></tr>
                </table>
                <p>Mohon tunggu konfirmasi jadwal dari admin kami via WhatsApp. Jangan balas email ini.</p>
            ";

            $mail->send();

        } catch (Exception $e) {
        }

        $nomorAdminWA = $_ENV['ADMIN_WHATSAPP'];
        
        $display_hewan_wa = $_POST['jenis_hewan'];
        if ($display_hewan_wa == 'Lainnya' && !empty($_POST['hewan_lainnya'])) {
            $display_hewan_wa = $_POST['hewan_lainnya'];
        }

        $pesanWA = "Halo Admin CandyVet, saya ingin booking jadwal atas nama:\n\n" .
                   "Nama Majikan: " . $_POST['nm_majikan'] . "\n" .
                   "No. Telp: " . $_POST['no_tlp_majikan'] . "\n" .
                   "Nama Hewan: " . $_POST['nm_hewan'] . "\n" .
                   "Jenis Hewan: " . $display_hewan_wa . "\n" .
                   "Keluhan: " . $_POST['keluhan'] . "\n\n" .
                   "Mohon konfirmasi jadwalnya. Terima kasih.";
        
        $pesanWAEncoded = urlencode($pesanWA);
        $whatsappURL = "https://api.whatsapp.com/send?phone=" . $nomorAdminWA . "&text=" . $pesanWAEncoded;

        echo "<script>
                alert('Booking Terkirim! Anda akan dialihkan ke WhatsApp. Bukti booking juga telah dikirim ke email Anda.');
                window.location.href = '$whatsappURL';
              </script>";

    } else {
        echo '<script>alert("Data Gagal Disimpan, silakan coba lagi: ' . $conn->error . '");</script>';
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
            Formulir Booking CandyVet
        </h2>

        <hr class="w-1/2 mx-auto border-t-2 border-[#FA812F] mb-10">

        <form action="booking" method="POST" class="space-y-6">

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

            <div class="relative">
                <label for="jenis_hewan" class="block text-lg font-semibold text-gray-700 mb-2">Jenis Hewan</label>

                <select id="jenis_hewan" name="jenis_hewan"
                    onchange="toggleInput(this)" required
                    class="w-full appearance-none px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition pr-10">
                    <option value="" disabled selected>Pilih jenis hewan</option>
                    <option value="Kucing">Kucing</option>
                    <option value="Anjing">Anjing</option>
                    <option value="Kelinci">Kelinci</option>
                    <option value="Burung">Burung</option>
                    <option value="Lainnya">Lainnya</option>
                </select>

                <svg class="absolute right-4 top-[54px] w-5 h-5 text-gray-500 pointer-events-none"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 30 24">
                    <path d="M29.0561 3.14713e-05L29.0561 9.21603L14.5281 23.936L7.24792e-05 9.21603V3.14713e-05L14.5281 14.784L29.0561 3.14713e-05Z" fill="#DD0303"/>
                </svg>

                <input type="text" id="hewan_lainnya" name="hewan_lainnya"
                    placeholder="Tulis jenis hewan" required
                    class="hidden mt-3 w-full px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition">
            </div>

            <div>
                <label for="usia_hewan" class="block text-lg font-semibold text-gray-700 mb-2">Usia Hewan (Tahun)</label>
                <input type="number" name="usia_hewan" id="usia_hewan" placeholder="Masukkan Usia Hewan" min="1" max="99" required class="w-full px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition">
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
                <button type="button" onclick="confirmreset()" class="w-full py-3 px-6 text-lg font-semibold text-[#FA812F] bg-transparent border-2 border-[#FA812F] rounded-xl hover:-translate-y-0.5 transform transition shadow-lg hover:shadow-xl">
                    Reset Form
                </button>
            </div>
            
        </form>
    </section>

    <script src="booking.js"></script>
</body>
</html>