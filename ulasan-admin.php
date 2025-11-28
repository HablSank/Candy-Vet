<?php
session_start();
if(!isset($_SESSION['user'])){
    header('Location:login'); 
    exit;
}

include 'koneksi.php'; 

if (!isset($conn)) {
    die("Koneksi database gagal dimuat. Pastikan 'koneksi.php' mendefinisikan \$conn.");
}

$redirect_params = '';
if(isset($_GET['page'])) $redirect_params .= '&page=' . (int)$_GET['page'];
if(isset($_GET['status'])) $redirect_params .= '&status=' . urlencode($_GET['status']);

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $new_status = '';

    if ($_GET['action'] == 'approve') {
        $new_status = 'Approved';
    } elseif ($_GET['action'] == 'reject') {
        $new_status = 'Rejected';
    }

    if ($new_status) {
        $stmt = $conn->prepare("UPDATE tb_ulasan SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $id);
        
        if ($stmt->execute()) {
            header("Location: ulasan-admin?pesan=" . strtolower($new_status) . $redirect_params);
            exit;
        } else {
            die("Error moderasi: " . $stmt->error);
        }
    }
}

$items_per_page = 5;
$halaman_sekarang = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($halaman_sekarang < 1) $halaman_sekarang = 1;
$offset = ($halaman_sekarang - 1) * $items_per_page;

$status_filter = isset($_GET['status']) ? $_GET['status'] : 'Pending';
$allowed_status = ['Pending', 'Approved', 'Rejected'];
if (!in_array($status_filter, $allowed_status)) {
    $status_filter = 'Pending';
}
$status_param_url = '&status=' . urlencode($status_filter);

$total_ulasan_query = $conn->prepare("SELECT COUNT(id) AS total FROM tb_ulasan WHERE status = ?");
$total_ulasan_query->bind_param("s", $status_filter);
$total_ulasan_query->execute();
$total_ulasan_result = $total_ulasan_query->get_result();
$total_ulasan = $total_ulasan_result->fetch_assoc()['total'];
$total_halaman = ceil($total_ulasan / $items_per_page);

$query_ulasan = "SELECT id, nm_majikan, nm_hewan, ulasan, tgl_ulasan 
                 FROM tb_ulasan 
                 WHERE status = ? 
                 ORDER BY tgl_ulasan DESC 
                 LIMIT ? OFFSET ?";
$stmt_fetch = $conn->prepare($query_ulasan);
$stmt_fetch->bind_param("sii", $status_filter, $items_per_page, $offset);
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();

$data_rows = []; 
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data_rows[] = $row;
    }
}

$ulasan_data_js = [];
if ($total_ulasan > 0) {
    $full_result_query = $conn->prepare("SELECT id, ulasan FROM tb_ulasan WHERE status = ?");
    $full_result_query->bind_param("s", $status_filter);
    $full_result_query->execute();
    $full_result = $full_result_query->get_result();

    while ($row = $full_result->fetch_assoc()) {
        $ulasan_data_js[$row['id']] = $row['ulasan'];
    }
}

if (isset($stmt_fetch)) $stmt_fetch->close(); 
if (isset($total_ulasan_query)) $total_ulasan_query->close();
if (isset($full_result_query)) $full_result_query->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Ulasan - CandyVet</title>
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
        .animate-slideDown { animation: slideDown 0.3s ease; }
        .table-spacing { border-spacing: 0; border-collapse: separate; }
        .table-spacing td, .table-spacing th { border-right: none !important; }
        .table-spacing th.rounded-r-xl, .table-spacing td.rounded-r-xl { border-right: none !important; }
        .swal2-styled.swal2-confirm { background-color: #FA812F !important; color: white !important; border: none !important; font-weight: 600 !important; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); }
        .swal2-styled.swal2-cancel { background-color: #FAB12F !important; color: #495057 !important; border: 1px solid #ced4da !important; font-weight: 600 !important; }
        .swal2-styled.swal2-confirm-red { background-color: #dc3545 !important; }
    </style>
</head>
<body class="bg-OrenMuda font-sans flex min-h-screen text-HitamTeks">

    <aside id="sidebar"
        class="fixed top-0 left-0 w-72 bg-PutihCard p-8 h-screen rounded-e-3xl flex flex-col justify-between shadow-lg z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
        <div>
            <div class="flex items-center mb-6 pb-4 border-b border-OrenTua">
                <img src="./assets/logo.png" alt="CandyVet Logo" class="w-[100px] h-[100px] object-contain">
                <h2 class="text-HitamTeks text-2xl font-extrabold ml-2">CandyVet</h2>
            </div>
            <ul class="space-y-3">
                <li>
                    <a href="admin" class="flex items-center gap-3 py-3 px-5 text-HitamTeks hover:bg-gray-100 hover:shadow-soft hover:text-UnguAksen font-semibold rounded-xl transition-all">
                        <i class='bx bxs-dashboard text-2xl'></i> 
                        Riwayat Booking
                    </a>
                </li>
                <li>
                    <a href="ulasan-admin" class="flex items-center gap-3 py-3 px-5 bg-OrenTua text-white font-semibold rounded-xl shadow-soft transition-all hover:bg-UnguAksen hover:shadow-lg">
                        <i class='bx bxs-message-dots text-2xl'></i>
                        Riwayat Ulasan
                    </a>
                </li>
            </ul>
        </div>
        <div class="pt-4 border-t border-OrenTua">
            <a href="#" onclick="confirmlogout()" class="flex items-center gap-3 py-3 px-5 text-HitamTeks hover:bg-gray-100 hover:shadow-soft hover:text-UnguAksen font-semibold rounded-xl transition-all">
                <i class='bx bx-log-out text-2xl'></i> Keluar
            </a>
        </div>
    </aside>

    <div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <main class="flex-1 lg:ml-72 p-6 sm:p-10 w-full transition-all duration-300">

        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div class="flex items-center w-full justify-between md:justify-start">
                <button onclick="toggleSidebar()" class="lg:hidden text-white text-3xl mr-3 focus:outline-none">
                    <i class='bx bx-menu'></i>
                </button>
                <h1 class="text-HitamTeks text-xl md:text-4xl font-extrabold drop-shadow">RIWAYAT ULASAN</h1>
            </div>
            <div class="relative w-full md:w-80">
                <input type="text" placeholder="Cari ulasan..." id="searchInput"
                    class="w-full py-3 pl-5 pr-12 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-OrenTua text-gray-700">
                <i class='bx bx-search absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 text-xl'></i>
            </div>
        </header>

        <?php
            if (isset($_GET['pesan'])) {
                $pesan = $_GET['pesan'];
                $text = ''; $icon = '';
                if ($pesan == 'approved') {
                    $text = 'Ulasan berhasil disetujui dan kini ditampilkan di halaman depan.'; $icon = 'success';
                } elseif ($pesan == 'rejected') {
                    $text = 'Ulasan berhasil ditolak dan tidak akan ditampilkan.'; $icon = 'success';
                }
                if ($text) {
                    echo "<script>
                        Swal.fire({
                            icon: '{$icon}', title: 'Berhasil!', text: '{$text}',
                            showConfirmButton: false, timer: 2500 
                        });
                    </script>";
                }
            }
        ?>

        <div class="bg-PutihCard p-6 sm:p-8 rounded-3xl shadow-soft">

            <div class="text-center mb-8">
                <?php 
                    $title_map = ['Pending' => 'Ulasan Menunggu Persetujuan', 'Approved' => 'Ulasan Diterima', 'Rejected' => 'Ulasan Ditolak'];
                    $current_title = $title_map[$status_filter] ?? 'Riwayat Ulasan';
                ?>
                <h2 class="text-HitamTeks text-2xl font-bold relative inline-block pb-1.5">
                    <?php echo $current_title; ?>
                    <span class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full h-0.5 bg-OrenTua rounded-full"></span>
                </h2>
            </div>

            <div class="flex gap-2 sm:gap-3 mb-6 flex-wrap justify-center">
                <?php
                $tabs = ['Pending'=>'Menunggu','Approved'=>'Diterima','Rejected'=>'Ditolak'];
                foreach ($tabs as $key=>$label) {
                    $active = ($status_filter == $key) ? 'bg-OrenTua text-white' : 'bg-gray-100 text-HitamTeks';
                    echo "<a href='ulasan-admin?status=$key' class='px-2 py-1 text-sm md:px-5 md:py-2 font-semibold rounded-xl transition hover:scale-[1.02] hover:bg-OrenTua hover:text-white $active'>$label</a>";
                }
                ?>
            </div>

            <div class="overflow-x-auto">

                <table class="w-full border-collapse hidden sm:table">
                    <thead>
                        <tr>
                            <th class='p-4 font-bold text-sm uppercase text-white bg-OrenTua rounded-l-xl'>No.</th>
                            <th class='p-4 font-bold text-sm uppercase text-white bg-OrenTua text-left'>Nama Majikan</th>
                            <th class='p-4 font-bold text-sm uppercase text-white bg-OrenTua text-left'>Nama Hewan</th>
                            <th class='p-4 font-bold text-sm uppercase text-white bg-OrenTua text-left'>Tanggal</th>
                            <th class='p-4 font-bold text-sm uppercase text-white bg-OrenTua text-left'>Ulasan</th>
                            <th class='p-4 font-bold text-sm uppercase text-white bg-OrenTua rounded-r-xl text-center'>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if(count($data_rows) > 0){
                            $no = $offset + 1;
                            $total_rows_on_page = count($data_rows);
                            
                            foreach($data_rows as $index => $row){
                                $tr_border_class = ($index < $total_rows_on_page - 1) ? 'border-b border-OrenTua' : ''; 
                                
                                $ulasan_lengkap = htmlspecialchars($row['ulasan']);
                                $ulasan_singkat = strlen($ulasan_lengkap) > 60 ? substr($ulasan_lengkap, 0, 60) . '...' : $ulasan_lengkap;
                                
                                echo "<tr class='bg-PutihCard hover:bg-orange-50 transition $tr_border_class'>"; 
                                echo "<td class='p-4 font-semibold text-HitamTeks'>$no</td>";
                                echo "<td class='p-4 font-semibold text-HitamTeks'>".htmlspecialchars($row['nm_majikan'])."</td>";
                                echo "<td class='p-4 text-HitamTeks'>".htmlspecialchars($row['nm_hewan'])."</td>";
                                
                                $raw_date = $row['tgl_ulasan'] ?? "";
                                $formatted_date = (!empty($raw_date) && $raw_date != '0000-00-00') ? date('d F Y', strtotime($raw_date)) : "-";
                                echo "<td class='p-4 text-HitamTeks'>".htmlspecialchars($formatted_date)."</td>";
                                
                                echo "<td class='p-4 text-HitamTeks max-w-xs'>";
                                echo "<span class='block'>".$ulasan_singkat."</span>";
                                if (strlen($ulasan_lengkap) > 60) {
                                    echo "<button onclick='showFullUlasan(".$row['id'].")' class='text-UnguAksen hover:text-OrenTua font-semibold underline text-xs mt-1'>Baca Selengkapnya</button>";
                                }
                                echo "</td>";
                                
                                echo "<td class='p-4'>
                                        <div class='flex gap-2 items-center justify-center'>";
                                
                                $id = intval($row['id']);
                                $majikan_js = htmlspecialchars($row['nm_majikan'], ENT_QUOTES, 'UTF-8');
                                $redirect_params_js = "page={$halaman_sekarang}&status=" . urlencode($status_filter);

                                if ($status_filter == 'Approved') {
                                    echo "<a href='#' onclick=\"return confirmRejectUlasan($id, '$majikan_js', '$redirect_params_js');\" class='w-8 h-8 flex items-center justify-center bg-red-100 text-red-700 rounded-full hover:bg-red-700 hover:text-white transition' title='Tolak Ulasan'><i class='bx bx-x text-lg'></i></a>";
                                } elseif ($status_filter == 'Rejected') {
                                    echo "<a href='#' onclick=\"return confirmApproveUlasan($id, '$majikan_js', '$redirect_params_js');\" class='w-8 h-8 flex items-center justify-center bg-green-100 text-green-700 rounded-full hover:bg-green-700 hover:text-white transition' title='Setujui Ulasan'><i class='bx bx-check text-lg'></i></a>";
                                } else {
                                    echo "<a href='#' onclick=\"return confirmApproveUlasan($id, '$majikan_js', '$redirect_params_js');\" class='w-8 h-8 flex items-center justify-center bg-green-100 text-green-700 rounded-full hover:bg-green-700 hover:text-white transition' title='Setujui Ulasan'><i class='bx bx-check text-lg'></i></a>";
                                    echo "<a href='#' onclick=\"return confirmRejectUlasan($id, '$majikan_js', '$redirect_params_js');\" class='w-8 h-8 flex items-center justify-center bg-red-100 text-red-700 rounded-full hover:bg-red-700 hover:text-white transition' title='Tolak Ulasan'><i class='bx bx-x text-lg'></i></a>";
                                }
                                
                                echo "    </div>
                                      </td>";
                                
                                echo "</tr>";
                                $no++;
                            }
                        } else {
                            if ($status_filter == 'Approved') $empty_text = 'Tidak ada ulasan yang disetujui';
                            elseif ($status_filter == 'Rejected') $empty_text = 'Tidak ada ulasan yang ditolak';
                            else $empty_text = 'Tidak ada ulasan yang menunggu';
                            echo '<tr><td colspan="6" class="text-center p-12 text-gray-500"><i class="bx bx-folder-open text-3xl mb-2 block"></i>' . $empty_text . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
                
                <div class="sm:hidden space-y-3">
                    <?php
                    $count_mobile = 0;
                    if(count($data_rows) > 0){
                        foreach($data_rows as $row_mobile) {
                            $count_mobile++;
                            
                            $id_mobile = intval($row_mobile['id']);
                            $majikan_js_mobile = htmlspecialchars($row_mobile['nm_majikan'], ENT_QUOTES, 'UTF-8');
                            $redirect_params_js = "page={$halaman_sekarang}&status=" . urlencode($status_filter);
                            $ulasan_lengkap_mobile = htmlspecialchars($row_mobile['ulasan']);

                            echo "<div class='bg-white rounded-xl p-4 shadow-soft border-l-4 border-OrenTua mobile-search-row'>";
                            
                            echo "<div class='flex items-start justify-between gap-2 mb-2'>";
                            echo "<div class='flex-1 min-w-0'>";
                            echo "<p class='text-xs text-gray-500 font-medium'>Nama Pemesan</p>";
                            echo "<p class='font-bold text-HitamTeks text-base truncate'>".htmlspecialchars($row_mobile['nm_majikan'])."</p>";
                            echo "</div>";
                            echo "</div>";

                            echo "<p class='text-sm text-gray-700 mb-3'>".$ulasan_lengkap_mobile."</p>";

                            echo "<div class='flex gap-2 justify-end pt-2 border-t border-gray-100'>";
                            if ($status_filter == 'Approved') {
                                echo "<a href='#' onclick=\"return confirmRejectUlasan($id_mobile, '$majikan_js_mobile', '$redirect_params_js');\" class='w-8 h-8 flex items-center justify-center bg-red-100 text-red-700 rounded-full hover:bg-red-700 hover:text-white transition' title='Tolak Ulasan'><i class='bx bx-x text-lg'></i></a>";
                            } elseif ($status_filter == 'Rejected') {
                                echo "<a href='#' onclick=\"return confirmApproveUlasan($id_mobile, '$majikan_js_mobile', '$redirect_params_js');\" class='w-8 h-8 flex items-center justify-center bg-green-100 text-green-700 rounded-full hover:bg-green-700 hover:text-white transition' title='Setujui Ulasan'><i class='bx bx-check text-lg'></i></a>";
                            } else {
                                echo "<a href='#' onclick=\"return confirmApproveUlasan($id_mobile, '$majikan_js_mobile', '$redirect_params_js');\" class='w-8 h-8 flex items-center justify-center bg-green-100 text-green-700 rounded-full hover:bg-green-700 hover:text-white transition' title='Setujui Ulasan'><i class='bx bx-check text-lg'></i></a>";
                                echo "<a href='#' onclick=\"return confirmRejectUlasan($id_mobile, '$majikan_js_mobile', '$redirect_params_js');\" class='w-8 h-8 flex items-center justify-center bg-red-100 text-red-700 rounded-full hover:bg-red-700 hover:text-white transition' title='Tolak Ulasan'><i class='bx bx-x text-lg'></i></a>";
                            }
                            echo "</div></div>";
                        }
                    }
                    
                    if($count_mobile == 0) {
                        if ($status_filter == 'Approved') $empty_text = 'Tidak ada ulasan yang disetujui';
                        elseif ($status_filter == 'Rejected') $empty_text = 'Tidak ada ulasan yang ditolak';
                        else $empty_text = 'Tidak ada ulasan yang menunggu';
                         echo '<div class="text-center p-12 text-gray-500"><i class="bx bx-folder-open text-3xl mb-2 block"></i><p>' . $empty_text . '</p></div>';
                    }
                    ?>
                </div>

            </div>
            <?php if ($total_halaman > 1): ?>
                <nav class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 pt-6 border-t border-OrenTua">
                    
                    <div class="text-sm text-gray-500">
                        Halaman <span class="font-bold text-HitamTeks"><?php echo $halaman_sekarang; ?></span> dari <span class="font-bold text-HitamTeks"><?php echo $total_halaman; ?></span>
                        (Total <?php echo $total_ulasan; ?> ulasan)
                    </div>
                
                    <div class="flex gap-2 flex-wrap mx-auto">
                        <?php $status_param = '&status=' . urlencode($status_filter); ?>
                        <?php if($halaman_sekarang > 1): ?>
                            <a href="ulasan-admin?page=<?php echo $halaman_sekarang - 1; ?><?php echo $status_param; ?>" class="px-4 py-2 text-sm font-semibold bg-gray-100 text-HitamTeks rounded-lg hover:bg-OrenTua hover:text-white transition">
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
                            <a href="ulasan-admin?page=<?php echo $i; ?><?php echo $status_param; ?>" class="px-4 py-2 text-sm font-semibold rounded-lg transition <?php echo $active_class; ?> <?php if(!$is_active) echo 'hidden sm:block'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if($halaman_sekarang < $total_halaman): ?>
                            <a href="ulasan-admin?page=<?php echo $halaman_sekarang + 1; ?><?php echo $status_param; ?>" class="px-4 py-2 text-sm font-semibold bg-gray-100 text-HitamTeks rounded-lg hover:bg-OrenTua hover:text-white transition">
                                <span class="relative bottom-0.5">&raquo;</span>
                            </a>
                        <?php else: ?>
                            <span class="px-4 py-2 text-sm font-semibold bg-gray-50 text-gray-400 rounded-lg cursor-not-allowed relative bottom-0.5">&raquo;</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="text-sm text-gray-500 invisible hidden sm:block">
                        (Total <?php echo $total_ulasan; ?> ulasan)
                    </div>

                </nav>
            <?php endif; ?>
        </div>
    </main>

    <script>
        const sidebar = document.getElementById("sidebar");
        const sidebarOverlay = document.getElementById("sidebarOverlay");

        function toggleSidebar() {
            sidebar.classList.toggle("-translate-x-full");
            sidebarOverlay.classList.toggle("hidden");
        }
    </script>

    <script>
        const fullUlasanData = <?php echo json_encode($ulasan_data_js); ?>;
    </script>
    <script src="ulasan-admin.js"></script>
</body>
</html>
<?php if(isset($conn)) mysqli_close($conn); ?>