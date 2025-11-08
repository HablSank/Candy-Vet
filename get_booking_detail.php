<?php
session_start();
include 'koneksi.php';

if(!isset($_SESSION['user'])){
    header('location:login');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID booking tidak valid']);
    exit;
}

// --- PETA DATA: Angka dari DB ke Teks (untuk Tampilan) ---
$map_hewan_detail = [
    0 => 'Kucing', 
    1 => 'Anjing', 
    2 => 'Kelinci', 
    3 => 'Burung'
    // '4' tidak ada di sini karena kita akan ambil dari kolom custom
];
$map_kelamin_detail = [
    0 => 'Jantan',
    1 => 'Betina'
];
// --------------------------------------------------

$id = (int)$_GET['id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM tb_form WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if ($data) {
    // --- Ubah Data Angka Menjadi Teks Sesuai Permintaan ---

    // 1. Tanggal Booking
    if (!empty($data['tanggal_booking']) && $data['tanggal_booking'] != '0000-00-00') {
        $data['tanggal_booking_formatted'] = date('d F Y', strtotime($data['tanggal_booking']));
    } else {
        $data['tanggal_booking_formatted'] = '-';
    }

    // 2. Jenis Kelamin (Angka -> Teks)
    $kelamin_int = (int)$data['jenis_kelamin_hewan'];
    $data['jenis_kelamin_hewan'] = $map_kelamin_detail[$kelamin_int] ?? 'Data Salah';

    // 3. Jenis Hewan (Angka -> Teks, sesuai logika detail)
    $hewan_int = (int)$data['jenis_hewan'];
    
    if ($hewan_int == 4) {
        // Jika 'Lainnya', tampilkan teks kustom (cth: "Penyu")
        $data['jenis_hewan'] = $data['jenis_hewan_custom'] ?? 'Lainnya (Data Kosong)';
    } else {
        // Jika standar, tampilkan dari peta (cth: "Kucing")
        $data['jenis_hewan'] = $map_hewan_detail[$hewan_int] ?? 'Data Salah';
    }

    // 4. Ubah usia menjadi teks (tambah "Tahun")
    if (!empty($data['usia_hewan'])) {
         $data['usia_hewan'] = $data['usia_hewan'] . ' Tahun';
    }

    // --- Kirim data yang sudah diubah ---
    header('Content-Type: application/json');
    echo json_encode($data);

} else {
    http_response_code(404); // Not Found
    echo json_encode(['error' => 'Data booking tidak ditemukan']);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>