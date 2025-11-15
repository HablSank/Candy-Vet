<?php
session_start();
if(!isset($_SESSION['user'])){
    header('location:login');
    exit;
}
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
    
    $jenis_hewan_teks = $_POST['jenis_hewan'] ?? '';
    $id_jenis_hewan = $map_hewan[$jenis_hewan_teks] ?? 4;
    
    $jenis_hewan_custom = NULL;
    if ($id_jenis_hewan == 4 && !empty($_POST['hewan_lainnya'])) {
        $jenis_hewan_custom = $_POST['hewan_lainnya'];
    }

    $jenis_kelamin_teks = $_POST['jenis_kelamin_hewan'] ?? '';
    $id_jenis_kelamin = $map_kelamin[$jenis_kelamin_teks] ?? 0;
    
    if(isset($_POST['id']) && !empty($_POST['id'])) {
        $id_to_update = (int)$_POST['id'];
        
$stmt_update = $conn->prepare("UPDATE tb_form SET 
    nm_majikan = ?, 
    email_majikan = ?, 
    no_tlp_majikan = ?, 
    nm_hewan = ?, 
    id_jenis_hewan = ?, 
    jenis_hewan_custom = ?, 
    usia_hewan = ?, 
    id_jenis_kelamin = ?, 
    keluhan = ? 
    WHERE id = ?");

if(!$stmt_update) {
    die("Prepare failed: " . $conn->error);
}

$result = $stmt_update->bind_param(
    "ssssissisi",  
    $_POST['nm_majikan'],
    $_POST['email_majikan'],
    $_POST['no_tlp_majikan'],
    $_POST['nm_hewan'],
    $id_jenis_hewan,
    $jenis_hewan_custom,
    $_POST['usia_hewan'],
    $id_jenis_kelamin,
    $_POST['keluhan'],
    $id_to_update
);

if(!$result) {
    die("Bind param failed: " . $stmt_update->error);
}

if($stmt_update->execute()) {
    echo "<script>alert('Data booking berhasil diupdate!'); window.location.href='admin';</script>";
} else {
    echo "<script>alert('Data GAGAL diupdate: " . $stmt_update->error . "');</script>";
}
$stmt_update->close();

    } else {
        $stmt_insert = $conn->prepare("INSERT INTO tb_form (
            nm_majikan, email_majikan, no_tlp_majikan, 
            nm_hewan, id_jenis_hewan, jenis_hewan_custom, 
            usia_hewan, id_jenis_kelamin, keluhan, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"); 
        
        if(!$stmt_insert) {
            die("Prepare failed: " . $conn->error);
        }
        
        $status_aktif = 'Aktif';
        $result = $stmt_insert->bind_param(
            "ssssisisss",
            $_POST['nm_majikan'],
            $_POST['email_majikan'],
            $_POST['no_tlp_majikan'],
            $_POST['nm_hewan'],
            $id_jenis_hewan,
            $jenis_hewan_custom,
            $_POST['usia_hewan'],
            $id_jenis_kelamin,
            $_POST['keluhan'],
            $status_aktif
        );
        
        if(!$result) {
            die("Bind param failed: " . $stmt_insert->error);
        }

        if($stmt_insert->execute()) {
             echo "<script>alert('Booking baru berhasil ditambahkan!'); window.location.href='admin';</script>";
        } else {
            echo "<script>alert('Data GAGAL disimpan: " . $stmt_insert->error . "');</script>";
        }
        $stmt_insert->close();
    }
    exit; 
}

$booking_data = []; 
$is_edit_mode = false; 
$page_title = "Tambah Booking Baru"; 

if(isset($_GET['id']) && is_numeric($_GET['id'])) {
    $is_edit_mode = true;
    $page_title = "Edit Booking";
    $id = (int)$_GET['id'];
    
    $stmt_get = $conn->prepare("
        SELECT 
            f.id,
            f.nm_majikan,
            f.email_majikan,
            f.no_tlp_majikan,
            f.nm_hewan,
            f.id_jenis_hewan,
            jh.nama_jenis_hewan,
            f.jenis_hewan_custom,
            f.usia_hewan,
            f.id_jenis_kelamin,
            jk.nama_jenis_kelamin,
            f.keluhan
        FROM tb_form f
        LEFT JOIN tb_jenis_hewan jh ON f.id_jenis_hewan = jh.id_jenis_hewan
        LEFT JOIN tb_jenis_kelamin jk ON f.id_jenis_kelamin = jk.id_jenis_kelamin
        WHERE f.id = ?
    ");
    
    if(!$stmt_get) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt_get->bind_param("i", $id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    $booking_data = $result->fetch_assoc();
    $stmt_get->close();

    if(!$booking_data) {
        echo "<script>alert('Error: Booking ID tidak ditemukan.'); window.location.href='admin';</script>";
        exit;
    }
}

function getData($field) {
    global $booking_data;
    if ($booking_data && isset($booking_data[$field])) {
        $value = $booking_data[$field];
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
    return ''; 
}

$id_jenis_hewan_db = (int)($booking_data['id_jenis_hewan'] ?? 0);
$jenis_hewan_custom_db = $booking_data['jenis_hewan_custom'] ?? '';
$is_jenis_lainnya = ($id_jenis_hewan_db == 4);

$id_jenis_kelamin_db = (int)($booking_data['id_jenis_kelamin'] ?? 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - CandyVet</title>
    <script src="https://cdn.tailwindcss.com"></script> 
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-[#FEF3E2]" style="font-family: 'Poppins', sans-serif;">

    <header class="bg-[#FEF3E2]">
        <div class="container mx-auto flex items-center justify-between h-20 px-4">
            <div class="flex items-center space-x-2">
                <img src="./assets/logo.png" alt="CandyVet Logo" class="h-14 w-auto">
                <span class="font-bold text-xl lg:text-2xl text-gray-800"><span class="text-[#FAB12F]">Candy</span><span class="text-[#F4631E]">Vet</span></span>
            </div>
            <a href="admin" class="flex items-center gap-2 bg-[#FEF3E2] border-[#9E00BA] border-2 rounded-lg py-2 px-4">
                <span class="hidden sm:inline text-[#9E00BA] text-xl font-bold">Kembali</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#9E00BA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            </a>
        </div>
    </header>

    <section class="max-w-xl mx-auto pt-24 my-12 px-4">
        <h2 class="text-center text-2xl sm:text-3xl font-bold text-gray-800 mb-10">
            <?php echo $page_title; ?>
        </h2>

        <hr class="w-1/2 mx-auto border-t-2 border-[#FA812F] mb-10">

        <form action="booking-admin.php" method="POST" class="space-y-6">
            
            <?php if($is_edit_mode): ?>
                <input type="hidden" name="id" value="<?php echo (int)$booking_data['id']; ?>">
            <?php endif; ?>

            <div>
                <label for="nm_majikan" class="block text-lg font-semibold text-gray-700 mb-2">Nama Majikan</label>
                <input type="text" name="nm_majikan" id="nm_majikan" value="<?php echo getData('nm_majikan'); ?>" placeholder="Masukkan Nama Lengkap Anda" required class="w-full px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition">
            </div>

            <div>
                <label for="email_majikan" class="block text-lg font-semibold text-gray-700 mb-2">Email Majikan</label>
                <input type="email" name="email_majikan" id="email_majikan" value="<?php echo getData('email_majikan'); ?>" placeholder="Masukkan Email Anda" required class="w-full px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition">
            </div>

            <div>
                <label for="no_tlp_majikan" class="block text-lg font-semibold text-gray-700 mb-2">No. Telepon</label>
                <input type="text" name="no_tlp_majikan" id="no_tlp_majikan" value="<?php echo getData('no_tlp_majikan'); ?>" placeholder="Masukkan No. Telepon Anda" required class="w-full px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition">
            </div>

            <div>
                <label for="nm_hewan" class="block text-lg font-semibold text-gray-700 mb-2">Nama Hewan</label>
                <input type="text" name="nm_hewan" id="nm_hewan" value="<?php echo getData('nm_hewan'); ?>" placeholder="Masukkan Nama Hewan" required class="w-full px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition">
            </div>

            <div class="relative">
                <label for="jenis_hewan" class="block text-lg font-semibold text-gray-700 mb-2">Jenis Hewan</label>
                <select id="jenis_hewan" name="jenis_hewan" onchange="toggleInput(this)" required class="w-full appearance-none px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition pr-10">
                    <option value="" <?php if(empty($booking_data)) echo 'selected'; ?> disabled>Pilih jenis hewan</option>
                    <option value="Kucing" <?php if($id_jenis_hewan_db == 0) echo 'selected'; ?>>Kucing</option>
                    <option value="Anjing" <?php if($id_jenis_hewan_db == 1) echo 'selected'; ?>>Anjing</option>
                    <option value="Kelinci" <?php if($id_jenis_hewan_db == 2) echo 'selected'; ?>>Kelinci</option>
                    <option value="Burung" <?php if($id_jenis_hewan_db == 3) echo 'selected'; ?>>Burung</option>
                    <option value="Lainnya" <?php if($is_jenis_lainnya) echo 'selected'; ?>>Lainnya</option>
                </select>
                <svg class="absolute right-4 top-[54px] w-5 h-5 text-gray-500 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 30 24"><path d="M29.0561 3.14713e-05L29.0561 9.21603L14.5281 23.936L7.24792e-05 9.21603V3.14713e-05L14.5281 14.784L29.0561 3.14713e-05Z" fill="#DD0303"/></svg>

                <input type="text" id="hewan_lainnya" name="hewan_lainnya"
                       value="<?php if($is_jenis_lainnya) echo htmlspecialchars($jenis_hewan_custom_db, ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="Tulis jenis hewan"
                       class="<?php if(!$is_jenis_lainnya) echo 'hidden'; ?> mt-3 w-full px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition">
            </div>

            <div>
                <label for="usia_hewan" class="block text-lg font-semibold text-gray-700 mb-2">Usia Hewan (Tahun)</label>
                <input type="number" name="usia_hewan" id="usia_hewan" value="<?php echo getData('usia_hewan'); ?>" placeholder="Masukkan Usia Hewan" min="1" max="99" required class="w-full px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition">
            </div>

            <div>
                <label class="block text-lg font-semibold text-gray-700 mb-2">Jenis Kelamin Hewan</label>
                <div class="flex items-center space-x-6 p-4 border-2 border-[#FA812F] rounded-xl bg-white">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="jenis_kelamin_hewan" value="Jantan" required class="h-5 w-5 text-[#FA812F] focus:ring-orange-200" <?php if($id_jenis_kelamin_db == 0 || ($is_edit_mode && $id_jenis_kelamin_db == 0)) echo 'checked'; ?>>
                        <span class="text-base text-gray-800">Jantan</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="jenis_kelamin_hewan" value="Betina" required class="h-5 w-5 text-[#FA812F] focus:ring-orange-200" <?php if($id_jenis_kelamin_db == 1) echo 'checked'; ?>>
                        <span class="text-base text-gray-800">Betina</span>
                    </label>
                </div>
            </div>

            <div>
                <label for="keluhan" class="block text-lg font-semibold text-gray-700 mb-2">Keluhan</label>
                <textarea name="keluhan" id="keluhan" placeholder="Masukkan Keluhan Hewan Anda" rows="4" required class="w-full px-5 py-3 text-base border-2 border-[#FA812F] rounded-xl bg-white text-gray-800 outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition resize-y"><?php echo getData('keluhan'); ?></textarea>
            </div>

            <div class="pt-4 space-y-3">
                <button type="submit" name="submit" class="w-full py-3 px-6 text-lg font-semibold text-white bg-[#FA812F] rounded-xl hover:bg-[#E37129] hover:-translate-y-0.5 transform transition shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    <?php echo $is_edit_mode ? 'Simpan Perubahan' : 'Kirim Booking'; ?>
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