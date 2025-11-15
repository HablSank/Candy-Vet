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

$id = (int)$_GET['id'];

$stmt = $conn->prepare("
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
        f.tanggal_booking,
        f.keluhan,
        f.status
    FROM tb_form f
    LEFT JOIN tb_jenis_hewan jh ON f.id_jenis_hewan = jh.id_jenis_hewan
    LEFT JOIN tb_jenis_kelamin jk ON f.id_jenis_kelamin = jk.id_jenis_kelamin
    WHERE f.id = ?
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if ($data) {
    if (!empty($data['tanggal_booking']) && $data['tanggal_booking'] != '0000-00-00') {
        $data['tanggal_booking_formatted'] = date('d F Y', strtotime($data['tanggal_booking']));
    } else {
        $data['tanggal_booking_formatted'] = '-';
    }

    if ((int)$data['id_jenis_hewan'] == 4) {
        $data['jenis_hewan'] = $data['jenis_hewan_custom'] ?? 'Lainnya (Data Kosong)';
    } else {
        $data['jenis_hewan'] = $data['nama_jenis_hewan'] ?? 'Data Salah';
    }

    $data['jenis_kelamin_hewan'] = $data['nama_jenis_kelamin'] ?? 'Data Salah';

    if (!empty($data['usia_hewan'])) {
        $data['usia_hewan_formatted'] = (int)$data['usia_hewan'] . ' Tahun';
    } else {
        $data['usia_hewan_formatted'] = '-';
    }

    header('Content-Type: application/json');
    echo json_encode($data);

} else {
    http_response_code(404);
    echo json_encode(['error' => 'Data booking tidak ditemukan']);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>