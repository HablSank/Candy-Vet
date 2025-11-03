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

$stmt = mysqli_prepare($conn, "SELECT * FROM tb_form WHERE id = ?");
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

    header('Content-Type: application/json');
    echo json_encode($data);

} else {
    http_response_code(404); // Not Found
    echo json_encode(['error' => 'Data booking tidak ditemukan']);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>