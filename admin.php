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

$redirect_params = '';
if(isset($_GET['status'])) $redirect_params .= '&status=' . urlencode($_GET['status']); 
if(isset($_GET['page'])) $redirect_params .= '&page=' . (int)$_GET['page'];

if(isset($_GET['batalkan'])){
    $id = $_GET['batalkan'];
    $stmt = mysqli_prepare($conn, "UPDATE tb_form SET status = 'Dibatalkan' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if(mysqli_stmt_execute($stmt)){
        header("Location: admin?pesan=dibatalkan" . $redirect_params);
        exit;
    }
}

if(isset($_GET['selesai'])){
    $id = $_GET['selesai'];
    $stmt = mysqli_prepare($conn, "UPDATE tb_form SET status = 'Selesai' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if(mysqli_stmt_execute($stmt)){
        header("Location: admin?pesan=selesai" . $redirect_params);
        exit;
    }
}

if(isset($_GET['aktifkan'])){
    $id = $_GET['aktifkan'];
    $stmt = mysqli_prepare($conn, "UPDATE tb_form SET status = 'Aktif' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if(mysqli_stmt_execute($stmt)){
        header("Location: admin?pesan=aktifkan" . $redirect_params);
        exit;
    }
}

$data_per_halaman = 5;
$halaman_sekarang = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
if ($halaman_sekarang < 1) $halaman_sekarang = 1;
$offset = ($halaman_sekarang - 1) * $data_per_halaman; 

$status_filter = isset($_GET['status']) ? $_GET['status'] : 'semua';
$status_param_url = ''; 
$where_clause = ''; 

if($status_filter != 'semua'){
    $status_filter_safe = mysqli_real_escape_string($conn, $status_filter);
    $where_clause = "WHERE f.status = '$status_filter_safe'";
    $status_param_url = "&status=" . urlencode($status_filter);
}

$query_total = "SELECT COUNT(*) as total FROM tb_form f $where_clause";
$result_total = mysqli_query($conn, $query_total);
$data_total = mysqli_fetch_assoc($result_total);
$total_data = (int)$data_total['total'];
$total_halaman = ceil($total_data / $data_per_halaman);

$query_data = "
    SELECT 
        f.id,
        f.nm_majikan,
        f.nm_hewan,
        f.id_jenis_hewan,
        jh.nama_jenis_hewan,
        f.jenis_hewan_custom,
        f.tanggal_booking,
        f.status
    FROM tb_form f
    LEFT JOIN tb_jenis_hewan jh ON f.id_jenis_hewan = jh.id_jenis_hewan
    $where_clause 
    ORDER BY f.id ASC 
    LIMIT $data_per_halaman OFFSET $offset
";

$result = mysqli_query($conn, $query_data);

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}

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
                        HitamTeks: '#1F2937',
                        OrenTua: '#FA812F',
                        OrenMuda: '#FAB12F',
                        PutihCard: '#FFFFFF',
                        UnguAksen: '#9E00BA',
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

        .swal2-styled.swal2-confirm {
            background-color: #FA812F !important; 
            color: white !important;
            border: none !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .swal2-styled.swal2-cancel {
            background-color: #FAB12F !important; 
            color: #495057 !important;
            border: 1px solid #ced4da !important;
            font-weight: 600 !important;
        }
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
</head>
<body class="bg-OrenMuda font-sans flex min-h-screen text-HitamTeks">

    <!-- SIDEBAR -->
    <aside id="sidebar"
        class="fixed top-0 left-0 w-72 bg-PutihCard p-8 h-screen rounded-e-3xl flex flex-col justify-between shadow-lg z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
        <div>
            <div class="flex items-center mb-6 pb-4 border-b border-OrenTua">
                <img src="./assets/logo.png" alt="CandyVet Logo" class="w-[100px] h-[100px] object-contain">
                <h2 class="text-HitamTeks text-2xl font-extrabold ml-2">CandyVet</h2>
            </div>
            <ul class="space-y-3">
                <li>
                    <a href="admin"
                        class="flex items-center gap-3 py-3 px-5 bg-OrenTua text-white font-semibold rounded-xl shadow-soft transition-all hover:bg-UnguAksen hover:shadow-lg">
                        <i class='bx bxs-dashboard text-2xl'></i>
                        Riwayat Booking
                    </a>
                </li>
                <li>
                    <a href="ulasan-admin"
                        class="flex items-center gap-3 py-3 px-5 text-HitamTeks hover:bg-gray-100 hover:shadow-soft hover:text-UnguAksen font-semibold rounded-xl transition-all">
                        <i class='bx bx-store text-2xl'></i>
                        Riwayat Ulasan
                    </a>
                </li>
            </ul>
        </div>
        <div class="pt-4 border-t border-OrenTua">
            <a href="#" onclick="confirmlogout()"
                class="flex items-center gap-3 py-3 px-5 text-HitamTeks hover:bg-gray-100 hover:shadow-soft hover:text-UnguAksen font-semibold rounded-xl transition-all">
                <i class='bx bx-log-out text-2xl'></i> Keluar
            </a>
        </div>
    </aside>

    <div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <!-- MAIN CONTENT -->
    <main class="flex-1 lg:ml-72 p-6 sm:p-10 w-full transition-all duration-300">

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div class="flex items-center w-full justify-between md:justify-start">
                <button onclick="toggleSidebar()" class="lg:hidden text-white text-3xl mr-3 focus:outline-none">
                    <i class='bx bx-menu text-red'></i>
                </button>
                <h1 class="text-HitamTeks text-xl md:text-4xl font-extrabold drop-shadow">RIWAYAT BOOKING</h1>
            </div>

            <div class="relative w-full md:w-80">
                <input type="text" placeholder="Cari..." id="searchInput"
                    class="w-full py-3 pl-5 pr-12 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-OrenTua text-gray-700">
                <i class='bx bx-search absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 text-xl'></i>
            </div>
        </header>

        <!-- CARD UTAMA -->
        <div class="bg-PutihCard p-6 sm:p-8 rounded-3xl shadow-soft">

            <!-- Judul -->
            <div class="text-center mb-8">
                <h2 class="text-HitamTeks text-2xl font-bold relative inline-block pb-1.5">
                    <?php 
                        $title_map = ['semua' => 'Semua Bookings', 'Aktif' => 'Booking Aktif', 'Selesai' => 'Booking Selesai', 'Dibatalkan' => 'Booking Dibatalkan'];
                        echo $title_map[$status_filter] ?? 'Semua Bookings';
                    ?>
                    <span class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full h-0.5 bg-OrenTua rounded-full"></span>
                </h2>
            </div>

            <!-- Filter Tabs -->
            <div class="flex gap-2 sm:gap-3 mb-6 flex-wrap justify-center">
                <?php
                $tabs = ['semua'=>'Semua','Aktif'=>'Aktif','Selesai'=>'Selesai','Dibatalkan'=>'Dibatalkan'];
                foreach ($tabs as $key=>$label) {
                    $active = ($status_filter == $key) ? 'bg-OrenTua text-white' : 'bg-gray-100 text-HitamTeks';
                    echo "<a href='admin?status=$key' class='px-2 py-1 md:px-5 md:py-2 font-semibold rounded-xl transition hover:bg-OrenTua hover:text-white $active'>$label</a>";
                }
                ?>
            </div>

            <!-- TABEL -->
            <div class="overflow-x-auto">
                <!-- DESKTOP TABLE -->
                <table class="w-full border-separate table-spacing hidden sm:table">
                    <thead>
                        <tr>
                            <th class='p-4 font-bold text-sm uppercase text-white bg-OrenTua rounded-l-xl border-r border-white text-center'>No.</th>
                            <th class='p-4 font-bold text-sm uppercase text-white bg-OrenTua border-r border-white text-center'>Nama</th>
                            <th class='p-4 font-bold text-sm uppercase text-white bg-OrenTua hidden md:table-cell border-r border-white text-center'>Nama Hewan</th>
                            <th class='p-4 font-bold text-sm uppercase text-white bg-OrenTua hidden md:table-cell border-r border-white text-center'>Jenis Hewan</th>
                            <th class='p-4 font-bold text-sm uppercase text-white bg-OrenTua hidden lg:table-cell border-r border-white text-center'>Tanggal Booking</th>
                            <th class='p-4 font-bold text-sm uppercase text-white bg-OrenTua border-r border-white text-center'>Status</th>
                            <th class='p-4 font-bold text-sm uppercase text-white bg-OrenTua rounded-r-xl text-center'>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if(mysqli_num_rows($result) > 0){
                            $no = $offset + 1;
                            while($row = mysqli_fetch_assoc($result)) {
                                $badge_class = 'bg-yellow-100 text-yellow-800';
                                if($row['status'] == 'Aktif') $badge_class = 'bg-green-100 text-green-700';
                                elseif($row['status'] == 'Selesai') $badge_class = 'bg-blue-100 text-blue-700';
                                elseif($row['status'] == 'Dibatalkan') $badge_class = 'bg-red-100 text-red-700';
                                
                                if ((int)$row['id_jenis_hewan'] == 4) {
                                    $display_jenis_hewan = $row['jenis_hewan_custom'] ?? 'Lainnya';
                                } else {
                                    $display_jenis_hewan = $row['nama_jenis_hewan'] ?? 'Data Salah';
                                }
                                
                                $raw_date = $row['tanggal_booking'] ?? "";
                                $formatted_date = (!empty($raw_date) && $raw_date != '0000-00-00') ? date('d F Y', strtotime($raw_date)) : "-";
                                
                                echo "<tr class='bg-PutihCard hover:bg-orange-50 transition border-b border-gray-200'>";
                                echo "<td class='p-4 font-semibold text-HitamTeks border-r border-gray-200 text-center'>$no</td>";
                                echo "<td class='p-4 font-semibold text-HitamTeks border-r border-gray-200 text-center'>".htmlspecialchars($row['nm_majikan'] ?? "")."</td>";
                                echo "<td class='p-4 text-HitamTeks border-r border-gray-200 text-center hidden md:table-cell'>".htmlspecialchars($row['nm_hewan'] ?? "")."</td>";
                                echo "<td class='p-4 text-HitamTeks border-r border-gray-200 text-center hidden md:table-cell'>".$display_jenis_hewan."</td>";
                                echo "<td class='p-4 text-HitamTeks border-r border-gray-200 text-center hidden lg:table-cell'>".htmlspecialchars($formatted_date)."</td>";
                                echo "<td class='p-4 border-r border-gray-200 text-center'><span class='px-4 py-1.5 rounded-full text-xs font-bold $badge_class'>".htmlspecialchars($row['status'] ?? "")."</span></td>";
                                echo "<td class='p-4 text-center'>
                                    <div class='flex gap-1.5 sm:gap-2 items-center justify-center flex-wrap'>
                                        <button type='button' onclick='showDetailModal({$row['id']})' class='w-8 h-8 flex items-center justify-center bg-gray-100 text-gray-700 rounded-full hover:bg-gray-700 hover:text-white transition' title='Lihat Detail'><i class=\"bx bx-file text-md\"></i></button>
                                        <a href='booking-admin?id={$row['id']}' class='w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-700 rounded-full hover:bg-blue-700 hover:text-white transition' title='Edit Booking'><i class=\"bx bx-pencil text-md\"></i></a>";
                                if($row['status']=='Aktif'){
                                    echo "<a href='#' onclick=\"confirmSelesai({$row['id']}, '{$js_params}')\" class='w-8 h-8 flex items-center justify-center bg-green-100 text-green-700 rounded-full hover:bg-green-700 hover:text-white transition' title='Tandai Selesai'><i class=\"bx bx-check-circle text-md\"></i></a>
                                          <a href='#' onclick=\"confirmBatalkan({$row['id']}, '{$js_params}')\" class='w-8 h-8 flex items-center justify-center bg-red-100 text-red-700 rounded-full hover:bg-red-700 hover:text-white transition' title='Batalkan Booking'><i class=\"bx bx-x-circle text-md\"></i></a>";
                                } elseif ($row['status'] == 'Selesai' || $row['status'] == 'Dibatalkan') {
                                    echo "<a href='#' onclick=\"confirmAktifkan({$row['id']}, '{$js_params}')\" class='w-8 h-8 flex items-center justify-center bg-gray-200 text-HitamTeks rounded-full hover:bg-OrenTua hover:text-white transition' title='Aktifkan Kembali'><i class=\"bx bx-undo text-md\"></i></a>";
                                }
                                echo "</div>
                                </td></tr>";
                                $no++;
                            }
                        } else {
                            echo '<tr><td colspan="7" class="text-center p-12 text-gray-500"><i class="bx bx-folder-open text-3xl mb-2 block"></i>Tidak ada data booking</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>

                <div class="sm:hidden space-y-3">
                    <?php
                    mysqli_data_seek($result, 0);
                    $count = 0;
                    if(mysqli_num_rows($result) > 0){
                        $no = $offset + 1;
                        while($row = mysqli_fetch_assoc($result)) {
                            $count++;
                            $badge_class = 'bg-yellow-100 text-yellow-800';
                            if($row['status'] == 'Aktif') $badge_class = 'bg-green-100 text-green-700';
                            elseif($row['status'] == 'Selesai') $badge_class = 'bg-blue-100 text-blue-700';
                            elseif($row['status'] == 'Dibatalkan') $badge_class = 'bg-red-100 text-red-700';
                            
                            echo "<div class='bg-white rounded-xl p-4 shadow-soft border-l-4 border-OrenTua hover:shadow-md transition mobile-search-row'>";
                            echo "<div class='flex items-start justify-between gap-2 mb-3'>";
                            echo "<div class='flex-1 min-w-0'>";
                            echo "<p class='text-xs text-gray-500 font-medium'>Nama Pemesan</p>";
                            echo "<p class='font-bold text-HitamTeks text-base truncate'>".htmlspecialchars($row['nm_majikan'] ?? "")."</p>";
                            echo "</div>";
                            echo "<span class='px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap $badge_class'>".htmlspecialchars($row['status'] ?? "")."</span>";
                            echo "</div>";
                            echo "<div class='flex gap-2 justify-end pt-2 border-t border-gray-100'>";
                            echo "<button type='button' onclick='showDetailModal({$row['id']})' class='w-8 h-8 flex items-center justify-center bg-gray-100 text-gray-700 rounded-full hover:bg-gray-700 hover:text-white transition text-sm' title='Lihat Detail'><i class=\"bx bx-file text-sm\"></i></button>";
                            echo "<a href='booking-admin?id={$row['id']}' class='w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-700 rounded-full hover:bg-blue-700 hover:text-white transition text-sm' title='Edit Booking'><i class=\"bx bx-pencil text-sm\"></i></a>";
                            if($row['status']=='Aktif'){
                                echo "<a href='#' onclick=\"confirmSelesai({$row['id']}, '{$js_params}')\" class='w-8 h-8 flex items-center justify-center bg-green-100 text-green-700 rounded-full hover:bg-green-700 hover:text-white transition text-sm' title='Tandai Selesai'><i class=\"bx bx-check-circle text-sm\"></i></a>";
                                echo "<a href='#' onclick=\"confirmBatalkan({$row['id']}, '{$js_params}')\" class='w-8 h-8 flex items-center justify-center bg-red-100 text-red-700 rounded-full hover:bg-red-700 hover:text-white transition text-sm' title='Batalkan Booking'><i class=\"bx bx-x-circle text-sm\"></i></a>";
                            } elseif ($row['status'] == 'Selesai' || $row['status'] == 'Dibatalkan') {
                                echo "<a href='#' onclick=\"confirmAktifkan({$row['id']}, '{$js_params}')\" class='w-8 h-8 flex items-center justify-center bg-gray-200 text-HitamTeks rounded-full hover:bg-OrenTua hover:text-white transition text-sm' title='Aktifkan Kembali'><i class=\"bx bx-undo text-sm\"></i></a>";
                            }
                            echo "</div>";
                            echo "</div>";
                            $no++;
                        }
                        if($count == 0) {
                            echo '<div class="text-center p-12 text-gray-500"><i class="bx bx-folder-open text-3xl mb-2 block"></i><p>Tidak ada data booking</p></div>';
                        }
                    } else {
                        echo '<div class="text-center p-12 text-gray-500"><i class="bx bx-folder-open text-3xl mb-2 block"></i><p>Tidak ada data booking</p></div>';
                    }
                    ?>
                </div>

                <!-- PAGINATION -->
                <?php if ($total_halaman > 1): ?>
                <nav class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mt-8 pt-6 border-t border-OrenMuda">
                    <div class="text-sm text-gray-500">
                        Halaman <span class="font-bold text-HitamTeks"><?php echo $halaman_sekarang; ?></span> dari <span class="font-bold text-HitamTeks"><?php echo $total_halaman; ?></span>
                        (Total <?php echo $total_data; ?> booking)
                    </div>
                    <div class="flex gap-2 flex-wrap mx-auto">
                        <?php if($halaman_sekarang > 1): ?>
                            <a href="admin?page=<?php echo $halaman_sekarang - 1; ?><?php echo $status_param_url; ?>" class="px-4 py-2 text-sm font-semibold bg-gray-100 text-HitamTeks rounded-lg hover:bg-OrenTua hover:text-white transition">
                                &laquo;
                            </a>
                        <?php else: ?>
                            <span class="px-4 py-2 text-sm font-semibold bg-gray-50 text-gray-400 rounded-lg cursor-not-allowed">&laquo;</span>
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
                                &raquo;
                            </a>
                        <?php else: ?>
                            <span class="px-4 py-2 text-sm font-semibold bg-gray-50 text-gray-400 rounded-lg cursor-not-allowed">&raquo;</span>
                        <?php endif; ?>
                    </div>
                    <a href="booking-admin">
                        <button class="flex mx-auto items-center gap-2 bg-UnguAksen text-white px-3 py-2 md:px-6 md:py-4 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition" title="Buat Booking Baru">
                            <i class='bx bx-plus text:lg md:text-2xl'></i> Tambah Booking Baru
                        </button>
                    </a>
                </nav>
                <?php else: ?>
                <div class="flex justify-end\ pt-6 border-t border-OrenTua">
                    <a href="booking-admin">
                        <button class="flex items-center gap-2 bg-UnguAksen text-white px-3 py-2 md:px-6 md:py-4 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition" title="Buat Booking Baru">
                            <i class='bx bx-plus text:lg md:text-2xl'></i> Tambah Booking Baru
                        </button>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- MODAL DETAIL -->
    <div id="modalOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 hidden" onclick="hideDetailModal()"></div>

        <div id="detailModal" class="fixed inset-0 z-50 flex justify-center p-4 pt-12 md:pt-16 overflow-y-auto hidden">
            
            <div class="relative z-20 w-full max-w-xl transform overflow-hidden rounded-xl bg-[#FFFFFF] text-[#1F2937] shadow-2xl transition-all h-fit">
                
                <div class="p-5 sm:p-8">
                    <button onclick="hideDetailModal()" class="absolute top-4 right-4 sm:top-4 sm:right-6 text-[#1F2937] hover:opacity-75 print-hide">
                        <span id="closeOverlay" class="material-symbols-outlined text-xl sm:text-2xl font-bold">X</span>
                    </button>

                    <div class="flex flex-col mb-4 sm:mb-6">
                        <p class="text-2xl sm:text-3xl font-black text-[#FA812F]">Detail Booking</p>
                        <p id="modalBookingId" class="text-xs sm:text-sm font-normal text-[#1F2937]/70">Booking ID: ...</p>
                    </div>

                    <div class="space-y-4 sm:space-y-6">
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-[#FA812F] mb-3">Informasi Majikan</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3 sm:gap-x-6 sm:gap-y-4">
                                <div><p class="text-xs sm:text-sm font-medium text-[#1F2937]/80">Nama Majikan</p><p id="modalNamaMajikan" class="text-sm sm:text-base font-semibold">-</p></div>
                                <div><p class="text-xs sm:text-sm font-medium text-[#1F2937]/80">Email</p><p id="modalEmail" class="text-sm sm:text-base font-semibold">-</p></div>
                                <div><p class="text-xs sm:text-sm font-medium text-[#1F2937]/80">No. Telepon</p><p id="modalTelepon" class="text-sm sm:text-base font-semibold">-</p></div>
                                <div><p class="text-xs sm:text-sm font-medium text-[#1F2937]/80">Tanggal Booking</p><p id="modalTanggalBooking" class="text-sm sm:text-base font-semibold">-</p></div>
                            </div>
                        </div>
                        <div class="border-t border-gray-200"></div>
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-[#FA812F] mb-3">Informasi Hewan</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3 sm:gap-x-6 sm:gap-y-4">
                                <div><p class="text-xs sm:text-sm font-medium text-[#1F2937]/80">Nama Hewan</p><p id="modalNamaHewan" class="text-sm sm:text-base font-semibold">-</p></div>
                                <div><p class="text-xs sm:text-sm font-medium text-[#1F2937]/80">Jenis Hewan</p><p id="modalJenisHewan" class="text-sm sm:text-base font-semibold">-</p></div>
                                <div><p class="text-xs sm:text-sm font-medium text-[#1F2937]/80">Jenis Kelamin</p><p id="modalJenisKelamin" class="text-sm sm:text-base font-semibold">-</p></div>
                                <div><p class="text-xs sm:text-sm font-medium text-[#1F2937]/80">Usia Hewan</p><p id="modalUsiaHewan" class="text-sm sm:text-base font-semibold">-</p></div>
                                <div class="sm:col-span-2"><p class="text-xs sm:text-sm font-medium text-[#1F2937]/80">Keluhan</p><p id="modalKeluhan" class="text-sm sm:text-base font-semibold">-</p></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 sm:mt-8 flex justify-end gap-3 print-hide">
                        <button onclick="printDetail()" class="flex w-full sm:w-auto items-center justify-center gap-2 rounded-lg border px-4 py-2.5 text-xs sm:text-sm font-semibold text-white shadow-sm transition-colors bg-[#FA812F] hover:bg-[#E37129]">
                            Cetak
                        </button>
                        <button onclick="hideDetailModal()" class="flex w-full sm:w-auto items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-xs sm:text-sm font-semibold text-black shadow-sm transition-colors hover:bg-gray-100">
                            Tutup
                        </button> 
                    </div>
                </div>
            </div>
        </div>

    <!-- SCRIPT SIDEBAR TOGGLE -->
    <script>
        const sidebar = document.getElementById("sidebar");
        const sidebarOverlay = document.getElementById("sidebarOverlay");

        function toggleSidebar() {
            sidebar.classList.toggle("-translate-x-full");
            sidebarOverlay.classList.toggle("hidden");
        }
    </script>

    <script src="admin.js"></script>
</body>
</html>
<?php if(isset($conn)) mysqli_close($conn); ?>