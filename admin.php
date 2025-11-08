<?php
session_start();
if(!isset($_SESSION['user'])){
    header('location:login');
    exit;
}
include 'koneksi.php'; 

if (!isset($conn)) {
    die("Koneksi database gagal dimuat. Pastikan 'koneksi.php' mendefinisikan \$conn.");
}

// --- Kumpulkan Parameter URL untuk Redirect ---
// Ini penting agar filter & halaman tidak reset setelah aksi
$redirect_params = '';
// Jika ada filter status, simpan
if(isset($_GET['status'])) $redirect_params .= '&status=' . urlencode($_GET['status']); 
// Jika ada info halaman, simpan
if(isset($_GET['page'])) $redirect_params .= '&page=' . (int)$_GET['page'];


// --- Logic untuk Batalkan, Selesai, Aktifkan ---

if(isset($_GET['batalkan'])){
    $id = $_GET['batalkan'];
    $stmt = mysqli_prepare($conn, "UPDATE tb_form SET status = 'Dibatalkan' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if(mysqli_stmt_execute($stmt)){
        header("Location: admin?pesan=dibatalkan" . $redirect_params);
        exit;
    } else {
        echo "Error batalkan: " . mysqli_error($conn);
    }
}

if(isset($_GET['selesai'])){
    $id = $_GET['selesai'];
    $stmt = mysqli_prepare($conn, "UPDATE tb_form SET status = 'Selesai' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if(mysqli_stmt_execute($stmt)){
        header("Location: admin?pesan=selesai" . $redirect_params);
        exit;
    } else {
        echo "Error selesai: " . mysqli_error($conn);
    }
}

if(isset($_GET['aktifkan'])){
    $id = $_GET['aktifkan'];
    $stmt = mysqli_prepare($conn, "UPDATE tb_form SET status = 'Aktif' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if(mysqli_stmt_execute($stmt)){
        header("Location: admin?pesan=aktifkan" . $redirect_params);
        exit;
    } else {
        echo "Error aktifkan: " . mysqli_error($conn);
    }
}

// --- LOGIKA PAGINATION (HALAMAN) BARU---
$data_per_halaman = 5; // Tentukan 5 data per halaman
// Cek URL, user ada di halaman berapa? Default-nya halaman 1
$halaman_sekarang = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
if ($halaman_sekarang < 1) $halaman_sekarang = 1;
// Hitung data yang harus di-skip di SQL
$offset = ($halaman_sekarang - 1) * $data_per_halaman; 

// --- LOGIKA FILTER STATUS (Dimodifikasi untuk Pagination) ---
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'semua';
$status_param_url = ''; // Ini untuk disimpan di link tombol pagination nanti
$where_clause = ''; // Ini adalah klausa 'WHERE' untuk query SQL

if($status_filter != 'semua'){
    $status_filter_safe = mysqli_real_escape_string($conn, $status_filter);
    $where_clause = "WHERE status = '$status_filter_safe'";
    $status_param_url = "&status=" . urlencode($status_filter); // Simpan status untuk link
}

// --- DUA QUERY BARU (PENTING UNTUK PAGINATION) ---

// 1. Query untuk MENGHITUNG TOTAL DATA (sesuai filter)
// Kita perlu tahu total data untuk menghitung total halaman
$query_total = "SELECT COUNT(*) as total FROM tb_form $where_clause";
$result_total = mysqli_query($conn, $query_total);
$data_total = mysqli_fetch_assoc($result_total);
$total_data = (int)$data_total['total'];

// Hitung total halaman yang akan ada
$total_halaman = ceil($total_data / $data_per_halaman); // ceil() = bulatkan ke atas

// 2. Query untuk MENGAMBIL DATA (sesuai filter DAN halaman)
// Kita tambahkan LIMIT (5 data) dan OFFSET (skip berapa data)
$query_data = "SELECT * FROM tb_form $where_clause ORDER BY id ASC LIMIT $data_per_halaman OFFSET $offset";
$result = mysqli_query($conn, $query_data);

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}

// Variabel untuk parameter JS (ini untuk langkah 3 & 4 nanti)
// Ini akan menghasilkan string seperti "status=Aktif&page=2"
$js_params = ltrim($status_param_url . '&page=' . $halaman_sekarang, '&');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - CandyVet</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // WARNA REVISI
                        HitamTeks: '#1F2937', // Hitam gelap untuk semua teks
                        OrenTua: '#FA812F', // Jingga Tua
                        OrenMuda: '#FAB12F', // Jingga Muda (digunakan untuk latar belakang body)
                        PutihCard: '#FFFFFF', // Putih untuk card konten dan sidebar
                        UnguAksen: '#9E00BA', // Aksen Ungu
                    },
                    boxShadow: {
                        'soft': '0 4px 15px rgba(0,0,0,0.08)',
                    }
                }
            }
        }
    </script>

    <style>
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-slideDown {
            animation: slideDown 0.3s ease;
        }
        .table-spacing {
            border-spacing: 0; 
            border-collapse: separate; 
        }
        .table-spacing td, 
        .table-spacing th {
            border-right: none !important; 
        }
        .table-spacing th.rounded-r-xl,
        .table-spacing td.rounded-r-xl {
            border-right: none !important;
        }

        /* SweetAlert Styling - Menggunakan OrenTua */
        /* Pastikan custom class di JS terdefinisi dengan benar jika ingin override */
        .swal2-styled.swal2-confirm {
            background-color: #FA812F !important; 
            color: white !important;
            border: none !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .swal2-styled.swal2-cancel {
            /* Menggunakan OrenMuda untuk tombol Batal */
            background-color: #FAB12F !important; 
            color: #495057 !important;
            border: 1px solid #ced4da !important;
            font-weight: 600 !important;
        }
        /* Tambahkan style untuk konfirmasi Batalkan yang menggunakan warna merah */
        .swal2-styled.swal2-confirm-red {
            background-color: rgba(220, 53, 69, 1) !important;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #detailModal, #detailModal * {
                visibility: visible;
            }

            #detailModal {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: auto;
                box-shadow: none;
                border: none;
                overflow: visible;
            }
            
            #modalOverlay, .print-hide {
                display: none !important;
            }

            .rounded-lg.bg-gray-100 {
                background-color: #f3f4f6 !important;
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
            }
        }
        body.modal-open {
        overflow: hidden;
        }
    </style>
</head>
<!-- Latar Belakang Body menggunakan Oren Muda -->
<body class="bg-OrenMuda font-sans flex min-h-screen text-HitamTeks">

    <!-- SIDEBAR: BACKGROUND DIUBAH MENJADI PUTIH (bg-PutihCard) -->
    <aside class="w-72 bg-PutihCard p-8 fixed h-screen rounded-e-3xl flex flex-col justify-between shadow-lg">
        <div>
            <!-- Header Sidebar dengan Garis Oren Tua di bawah -->
            <div class="flex items-center mb-6 pb-4 border-b border-OrenTua">
                <!-- Logo dipertahankan -->
                <img src="./assets/logo.png" alt="CandyVet Logo" class="w-[100px] h-[100px] object-contain">
                <!-- Teks menggunakan warna Hitam (Ukuran diperkecil dari text-3xl menjadi text-2xl) -->
                <h2 class="text-HitamTeks text-2xl font-extrabold ml-2">CandyVet</h2>
            </div>
            <ul class="space-y-3">
                <!-- Teks Hitam, Background Aktif (Oren Tua) - Ditambah HOVER UNGU -->
                <li>
                    <a href="admin" class="flex items-center gap-3 py-3 px-5 bg-OrenTua text-white font-semibold rounded-xl shadow-soft transition-all hover:bg-UnguAksen hover:shadow-lg">
                        <i class='bx bxs-dashboard text-2xl'></i> 
                        Riwayat Booking
                    </a>
                </li>
                <!-- Teks Hitam, Non-Aktif (Ditambahkan hover:text-UnguAksen) -->
                <li>
                    <a href="#" class="flex items-center gap-3 py-3 px-5 text-HitamTeks hover:bg-gray-100 hover:shadow-soft hover:text-UnguAksen font-semibold rounded-xl transition-all">
                        <i class='bx bx-store text-2xl'></i>
                        Layanan
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Tombol Keluar di Bawah dengan Garis Oren Tua di atas (Ditambahkan hover:text-UnguAksen) -->
        <div class="pt-4 border-t border-OrenTua">
            <a href="#" onclick="confirmlogout()" class="flex items-center gap-3 py-3 px-5 text-HitamTeks hover:bg-gray-100 hover:shadow-soft hover:text-UnguAksen font-semibold rounded-xl transition-all">
                <i class='bx bx-log-out text-2xl'></i> Keluar
            </a>

        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 lg:ml-72 p-10">

        <!-- Header dan Search Bar -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <!-- Teks Hitam -->
            <h1 class="text-HitamTeks text-4xl font-extrabold drop-shadow">RIWAYAT BOOKING</h1>
            <!-- Search Bar -->
            <div class="relative w-full md:w-80">
                <input type="text" placeholder="Cari..." id="searchInput"
                    class="w-full py-3 pl-5 pr-12 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-OrenTua text-gray-700">
                <i class='bx bx-search absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 text-xl'></i>
            </div>
        </header>

        <!-- Pesan Alert
        <?php
        if(isset($_GET['pesan'])){
            $alert_text = '';
            if($_GET['pesan'] == 'dibatalkan') $alert_text = '✓ Booking berhasil dibatalkan!';
            elseif($_GET['pesan'] == 'selesai') $alert_text = '✓ Booking berhasil ditandai selesai!';
            elseif($_GET['pesan'] == 'aktifkan') $alert_text = '✓ Status booking berhasil diaktifkan kembali!';

            if($alert_text){
                echo '<div class="bg-green-100 text-green-800 font-semibold p-4 rounded-xl mb-6 shadow-soft animate-slideDown">'.$alert_text.'</div>';
            }
        }
        ?>
        -->
        <!-- Card Konten Utama: Background Putih -->
        <div class="bg-PutihCard p-8 rounded-3xl shadow-soft">

            <!-- Judul "Semua Bookings" dengan Garis Oren Tua di bawah -->
            <div class="text-center mb-8">
                <h2 class="text-HitamTeks text-2xl font-bold relative inline-block pb-1.5">
                    <?php 
                        $title_map = ['semua' => 'Semua Bookings', 'Aktif' => 'Booking Aktif', 'Selesai' => 'Booking Selesai', 'Dibatalkan' => 'Booking Dibatalkan'];
                        echo $title_map[$status_filter] ?? 'Semua Bookings';
                    ?>
                    <!-- Garis Oren Tua -->
                    <span class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full h-0.5 bg-OrenTua rounded-full"></span>
                </h2>
            </div>

            <!-- Filter Tabs Status -->
            <div class="flex gap-3 mb-6 flex-wrap justify-center">
                <?php
                $tabs = ['semua'=>'Semua','Aktif'=>'Aktif','Selesai'=>'Selesai','Dibatalkan'=>'Dibatalkan'];
                foreach ($tabs as $key=>$label) {
                    // Menggunakan OrenTua 
                    $active = ($status_filter == $key) ? 'bg-OrenTua text-white' : 'bg-gray-100 text-HitamTeks';
                    // Hover menggunakan OrenTua 
                    echo "<a href='admin?status=$key' class='px-5 py-2 font-semibold rounded-xl transition hover:scale-[1.02] hover:bg-OrenTua hover:text-white $active'>$label</a>";
                }
                ?>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-separate table-spacing">
                    <thead>
                        <tr>
                            <?php
                            // Header bar menggunakan warna Oren Tua
                            // Menambahkan 'Tanggal Booking'
                            $headers = ['No.', 'Nama Majikan', 'Nama Hewan', 'Jenis Hewan', 'Tanggal Booking', 'Status', 'Aksi'];
                            foreach($headers as $i => $h){
                                $rounded_l = ($i==0) ? 'rounded-l-xl' : '';
                                $rounded_r = ($i==count($headers)-1) ? 'rounded-r-xl' : '';
                                // Garis vertikal dihilangkan
                                echo "<th class='p-4 text-left font-bold text-sm uppercase text-white bg-OrenTua $rounded_l $rounded_r'>$h</th>";
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
    <?php
    if(mysqli_num_rows($result) > 0){
        $no = $offset + 1;
        $total_rows = mysqli_num_rows($result);
        mysqli_data_seek($result, 0); 
        
        // ++ PETA DATA: Angka dari DB ke Teks (untuk Tampilan) ++
        $map_hewan_display = [
            0 => 'Kucing', 
            1 => 'Anjing', 
            2 => 'Kelinci', 
            3 => 'Burung', 
            4 => 'Lainnya' // Sesuai permintaan Anda, dashboard menampilkan "Lainnya"
        ];
        // ++ SELESAI TAMBAHAN
        
        while($row = mysqli_fetch_assoc($result)){
            $badge_class = 'bg-yellow-100 text-yellow-800';
            if($row['status'] == 'Aktif') $badge_class = 'bg-green-100 text-green-700';
            elseif($row['status'] == 'Selesai') $badge_class = 'bg-blue-100 text-blue-700';
            elseif($row['status'] == 'Dibatalkan') $badge_class = 'bg-red-100 text-red-700';
            
            $border_class = ($no < $total_rows) ? 'border-b border-OrenTua' : '';
            
            echo "<tr class='bg-PutihCard hover:bg-orange-50 transition'>"; 
            
            echo "<td class='p-4 font-semibold text-HitamTeks $border_class'>$no</td>";
            echo "<td class='p-4 font-semibold text-HitamTeks $border_class'>".htmlspecialchars($row['nm_majikan'] ?? "")."</td>";
            echo "<td class='p-4 text-HitamTeks $border_class'>".htmlspecialchars($row['nm_hewan'] ?? "")."</td>";
            
            // ================================================================
            // ++ LOGIKA JENIS HEWAN DIPERBARUI (Membaca Angka)
            // ================================================================
            $jenis_hewan_int_db = (int)($row['jenis_hewan'] ?? -1); // Ambil angka (cth: 0 atau 4)
            
            // Terjemahkan angka ke teks menggunakan peta
            $display_jenis_hewan = $map_hewan_display[$jenis_hewan_int_db] ?? 'Data Salah';
            
            echo "<td class='p-4 text-HitamTeks $border_class'>".$display_jenis_hewan."</td>";
            // ================================================================
            // ++ SELESAI PERUBAHAN
            // ================================================================

            // Kolom Tanggal Booking (Tidak diubah)
            $raw_date = $row['tanggal_booking'] ?? "";
            if (!empty($raw_date) && $raw_date != '0000-00-00') {
                $formatted_date = date('d F Y', strtotime($raw_date)); 
            } else {
                $formatted_date = "-";
            }

            echo "<td class='p-4 text-HitamTeks $border_class'>".htmlspecialchars($formatted_date)."</td>";

            // Status cell (Tidak diubah)
            echo "<td class='p-4 $border_class'><span class='px-4 py-1.5 rounded-full text-xs font-bold $badge_class'>".htmlspecialchars($row['status'] ?? "")."</span></td>";
            
            // Cell Aksi (Tidak diubah)
            echo "<td class='p-4 $border_class'>
                    <div class='flex gap-2 items-center'>
                        
                        <button type='button' onclick='showDetailModal({$row['id']})' class='w-8 h-8 flex items-center justify-center bg-gray-100 text-gray-700 rounded-full hover:bg-gray-700 hover:text-white transition' title='Lihat Detail'><i class=\"bx bx-file text-md\"></i></button>

                        <a href='booking-admin?id={$row['id']}' class='w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-700 rounded-full hover:bg-blue-700 hover:text-white transition' title='Edit Booking'><i class=\"bx bx-pencil text-md\"></i></a>
                        ";
                        
            if($row['status']=='Aktif'){
                echo "
                        <a href='#' onclick=\"confirmSelesai({$row['id']}, '<?php echo $js_params; ?>')\" class='w-8 h-8 flex items-center justify-center bg-green-100 text-green-700 rounded-full hover:bg-green-700 hover:text-white transition' title='Tandai Selesai'><i class=\"bx bx-check-circle text-md\"></i></a>
                        <a href='#' onclick=\"confirmBatalkan({$row['id']}, '<?php echo $js_params; ?>')\" class='w-8 h-8 flex items-center justify-center bg-red-100 text-red-700 rounded-full hover:bg-red-700 hover:text-white transition' title='Batalkan Booking'><i class=\"bx bx-x-circle text-md\"></i></a>
                        ";
            } elseif ($row['status'] == 'Selesai' || $row['status'] == 'Dibatalkan') {
                echo "
                        <a href='#' onclick=\"confirmAktifkan({$row['id']}, '<?php echo $js_params; ?>')\" class='w-8 h-8 flex items-center justify-center bg-gray-200 text-HitamTeks rounded-full hover:bg-OrenTua hover:text-white transition' title='Aktifkan Kembali'><i class=\"bx bx-undo text-md\"></i></a>
                        ";
            }

            echo "</div></td></tr>";
            $no++;
        }
    } else {
        echo '<tr><td colspan="7" class="text-center p-12 text-gray-500"><i class="bx bx-folder-open text-3xl mb-2 block"></i>Tidak ada data booking</td></tr>';
    }
    ?>
</tbody>
                </table>
            <?php if ($total_halaman > 1): ?>
            <nav class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mt-8 pt-6 border-t border-OrenMuda">
                
                <div class="text-sm text-gray-500">
                    Halaman <span class="font-bold text-HitamTeks"><?php echo $halaman_sekarang; ?></span> dari <span class="font-bold text-HitamTeks"><?php echo $total_halaman; ?></span>
                    (Total <?php echo $total_data; ?> booking)
                </div>

                <div class="flex gap-2 flex-wrap mx-auto">
                    <?php if($halaman_sekarang > 1): ?>
                        <a href="admin?page=<?php echo $halaman_sekarang - 1; ?><?php echo $status_param_url; ?>" class="px-4 py-2 text-sm font-semibold bg-gray-100 text-HitamTeks rounded-lg hover:bg-OrenTua hover:text-white transition">
                            <span class="relative bottom-0.5">&laquo;</span>
                        </a>
                    <?php else: ?>
                        <span class="px-4 py-2 text-sm font-semibold bg-gray-50 text-gray-400 rounded-lg cursor-not-allowed relative bottom-0.5">&laquo;</span>
                    <?php endif; ?>

                    <?php for($i = 1; $i <= $total_halaman; $i++): ?>
                        <?php
                            $is_active = ($i == $halaman_sekarang);
                            $active_class = $is_active ? 'bg-OrenTua text-white' : 'bg-gray-100 text-HitamTeks hover:bg-OrenMuda';
                        ?>
                        <a href="admin?page=<?php echo $i; ?><?php echo $status_param_url; ?>" class="px-4 py-2 text-sm font-semibold rounded-lg transition <?php echo $active_class; ?> <?php if(!$is_active) echo 'hidden sm:block'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if($halaman_sekarang < $total_halaman): ?>
                        <a href="admin?page=<?php echo $halaman_sekarang + 1; ?><?php echo $status_param_url; ?>" class="px-4 py-2 text-sm font-semibold bg-gray-100 text-HitamTeks rounded-lg hover:bg-OrenTua hover:text-white transition">
                            <span class="relative bottom-0.5">&raquo;</span>
                        </a>
                    <?php else: ?>
                        <span class="px-4 py-2 text-sm font-semibold bg-gray-50 text-gray-400 rounded-lg cursor-not-allowed relative bottom-0.5">&raquo;</span>
                    <?php endif; ?>
                </div>
                    <a href="booking-admin">
                        <button class="flex items-center gap-2 bg-UnguAksen text-white px-6 py-4 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition" title="Buat Booking Baru">
                            <i class='bx bx-plus text-2xl'></i> Tambah Booking Baru
                        </button>
                    </a>
            </nav>
            <?php endif; ?>
            </div>
        </div>
    </main>


<div id="modalOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 hidden" onclick="hideDetailModal()"></div>

    <div id="detailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto hidden">
        <div class="relative z-20 w-full max-w-xl transform overflow-hidden rounded-xl bg-[#FFFFFF] text-[#1F2937] shadow-2xl transition-all">
            <div class="p-8">
                <button onclick="hideDetailModal()" class="absolute top-4 right-6 text-[#1F2937] hover:opacity-75 print-hide">
                    <span class="material-symbols-outlined text-2xl font-bold">X</span>
                </button>

                <div class="flex flex-col mb-6">
                    <p class="text-3xl font-black text-[#FA812F]">Detail Booking</p>
                    <p id="modalBookingId" class="text-sm font-normal text-[#1F2937]/70">Booking ID: ...</p>
                </div>

                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold leading-tight tracking-tight text-[#FA812F] mb-3">Informasi Majikan</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                            <div class="flex flex-col">
                                <p class="text-sm font-medium text-[#1F2937]/80">Nama Majikan</p>
                                <p id="modalNamaMajikan" class="text-base font-semibold">-</p>
                            </div>
                            <div class="flex flex-col">
                                <p class="text-sm font-medium text-[#1F2937]/80">Email</p>
                                <p id="modalEmail" class="text-base font-semibold">-</p>
                            </div>
                            <div class="flex flex-col">
                                <p class="text-sm font-medium text-[#1F2937]/80">No. Telepon</p>
                                <p id="modalTelepon" class="text-base font-semibold">-</p>
                            </div>
                            <div class="flex flex-col">
                                <p class="text-sm font-medium text-[#1F2937]/80">Tanggal Booking</p>
                                <p id="modalTanggalBooking" class="text-base font-semibold">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200"></div>

                    <div>
                        <h3 class="text-lg font-bold leading-tight tracking-tight text-[#FA812F] mb-3">Informasi Hewan</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                            <div class="flex flex-col">
                                <p class="text-sm font-medium text-[#1F2937]/80">Nama Hewan</p>
                                <p id="modalNamaHewan" class="text-base font-semibold">-</p>
                            </div>
                            <div class="flex flex-col">
                                <p class="text-sm font-medium text-[#1F2937]/80">Jenis Hewan</p>
                                <p id="modalJenisHewan" class="text-base font-semibold">-</p>
                            </div>
                            <div class="flex flex-col">
                                <p class="text-sm font-medium text-[#1F2937]/80">Usia</p>
                                <p id="modalUsiaHewan" class="text-base font-semibold">-</p>
                            </div>
                            <div class="flex flex-col">
                                <p class="text-sm font-medium text-[#1F2937]/80">Jenis Kelamin</p>
                                <p id="modalJenisKelamin" class="text-base font-semibold">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200"></div>

                    <div>
                        <h3 class="text-lg font-bold leading-tight tracking-tight text-[#FA812F] mb-2">Keluhan</h3>
                        <div class="rounded-lg bg-gray-100 p-4">
                            <p id="modalKeluhan" class="text-sm font-normal leading-relaxed">-</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex flex-col sm:flex-row-reverse gap-3 print-hide">
                    <a id="modalEditButton" href="booking-admin" class="flex w-full items-center justify-center gap-2 rounded-lg bg-[#FA812F] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-opacity-90">
                        Edit
                    </a>
                    <button onclick="printDetail()" class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-[#1F2937] shadow-sm transition-colors hover:bg-gray-100">
                        Cetak
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script src="admin.js"></script>
</body>
</html>
<?php if(isset($conn)) mysqli_close($conn); ?>
