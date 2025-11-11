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


$items_per_page = 10;
$halaman_sekarang = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($halaman_sekarang - 1) * $items_per_page;


$total_ulasan_query = $conn->query("SELECT COUNT(id) AS total FROM tb_ulasan WHERE status = 'Pending'");
$total_ulasan = $total_ulasan_query->fetch_assoc()['total'];
$total_halaman = ceil($total_ulasan / $items_per_page);


$query_ulasan = "SELECT id, nm_majikan, nm_hewan, ulasan, tgl_ulasan FROM tb_ulasan WHERE status = 'Pending' ORDER BY tgl_ulasan DESC LIMIT ? OFFSET ?";
$stmt_fetch = $conn->prepare($query_ulasan);
$stmt_fetch->bind_param("ii", $items_per_page, $offset);
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();


$ulasan_data_js = [];
if ($total_ulasan > 0) {
    $full_result = $conn->query("SELECT id, ulasan FROM tb_ulasan WHERE status = 'Pending'");
    while ($row = $full_result->fetch_assoc()) {
        $ulasan_data_js[$row['id']] = $row['ulasan'];
    }
}

if (isset($stmt_fetch)) {
    $stmt_fetch->close(); 
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Ulasan - Admin</title>
    <link href="/dist/output.css" rel="stylesheet"> 
    <script src="https://cdn.tailwindcss.com"></script> 
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Definisi warna dan font yang diambil dari admin.php */
        body { font-family: 'Poppins', sans-serif; }
        .text-HitamTeks { color: #1F2937; }
        .bg-OrenTua { background-color: #FA812F; }
        .text-UnguAksen { color: #9E00BA; }
        .shadow-soft { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.06); }
        .table-custom-header th { font-weight: 700; color: #1F2937; font-size: 14px; }
    </style>
</head>
<body class="bg-[#FEF3E2]">
    
    <div class="flex h-screen overflow-hidden">
        <aside class="w-64 bg-white shadow-lg flex flex-col fixed inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out z-40" id="sidebar">
            <div class="p-6 border-b border-gray-100 flex items-center justify-center">
                <a href="admin" class="flex items-center space-x-2">
                    <img src="assets/logo.png" alt="CandyVet Logo" class="h-10 w-auto">
                    <span class="font-bold text-2xl text-gray-800"><span class="text-OrenTua">Candy</span><span class="text-[#F4631E]">Vet</span></span>
                </a>
            </div>
            
            <nav class="flex-grow p-4 space-y-2">
                <ul class="space-y-3">
                    <li>
                        <a href="admin" class="flex items-center gap-3 py-3 px-5 text-HitamTeks hover:bg-gray-100 hover:shadow-soft hover:text-UnguAksen font-semibold rounded-xl transition-all">
                            <i class='bx bxs-dashboard text-2xl'></i> 
                            Riwayat Booking
                        </a>
                    </li>
                    
                    <li>
                        <a href="ulasan-admin" class="flex items-center gap-3 py-3 px-5 bg-OrenTua text-white font-semibold rounded-xl shadow-soft transition-all hover:bg-opacity-90">
                            <i class='bx bx-star text-2xl'></i>
                            Ulasan Menunggu
                        </a>
                    </li>
                    
                    <li>
                        <a href="logout" class="flex items-center gap-3 py-3 px-5 text-HitamTeks hover:bg-gray-100 hover:shadow-soft hover:text-red-500 font-semibold rounded-xl transition-all">
                            <i class='bx bx-log-out text-2xl'></i>
                            Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        <div class="flex-1 flex flex-col overflow-hidden md:ml-64">
            <header class="bg-white shadow-soft p-4 flex items-center justify-between sticky top-0 z-30">
                <h1 class="text-xl font-bold text-HitamTeks">Dashboard Ulasan</h1>
                <button id="toggle-sidebar" class="md:hidden p-2 text-HitamTeks">
                    <i class='bx bx-menu text-3xl'></i>
                </button>
            </header>
            
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 md:p-10 bg-[#FEF3E2]">
                
                <div class="bg-white shadow-lg rounded-xl p-6 md:p-8 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-3xl font-bold text-HitamTeks">Dashboard Ulasan</h2>
                        </div>

                    <div class="mt-6">
                        <h3 class="text-xl font-bold text-gray-700 mb-4">Ulasan Menunggu Persetujuan (<?php echo $total_ulasan; ?>)</h3>
                    </div>

                    <?php if ($total_ulasan > 0): ?>
                        <div class="bg-white shadow-soft rounded-xl overflow-x-auto border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 table-custom-header">
                                    <tr>
                                        <th class="px-6 py-3 text-left">No.</th>
                                        <th class="px-6 py-3 text-left">Majikan</th>
                                        <th class="px-6 py-3 text-left">Hewan</th>
                                        <th class="px-6 py-3 text-left">Tanggal</th>
                                        <th class="px-6 py-3 text-left">Ulasan</th>
                                        <th class="px-6 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php 
                                    $no = $offset + 1; 
                                    while ($row = $result->fetch_assoc()): 
                                        $ulasan_lengkap = htmlspecialchars($row['ulasan']);
                                        $ulasan_singkat = strlen($ulasan_lengkap) > 50 ? substr($ulasan_lengkap, 0, 50) . '...' : $ulasan_lengkap;
                                    ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800"><?php echo $no++; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-HitamTeks"><?php echo htmlspecialchars($row['nm_majikan']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($row['nm_hewan']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo date('d F Y', strtotime($row['tgl_ulasan'])); ?></td>
                                        
                                        <td class="px-6 py-4 max-w-xs text-sm text-gray-700">
                                            <?php echo $ulasan_singkat; ?>
                                            <?php if (strlen($ulasan_lengkap) > 50): ?>
                                                <button 
                                                    onclick="showFullUlasan(<?php echo $row['id']; ?>)" 
                                                    class="text-UnguAksen hover:text-OrenTua font-semibold underline ml-1 text-xs whitespace-nowrap"
                                                >
                                                    Baca Selengkapnya
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <a href="ulasan-admin?action=approve&id=<?php echo $row['id'] . $redirect_params; ?>" class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 mr-2">
                                                <i class='bx bx-check text-base'></i>
                                                <span class="hidden sm:inline ml-1">Setujui</span>
                                            </a>
                                            <a href="ulasan-admin?action=reject&id=<?php echo $row['id'] . $redirect_params; ?>" class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                <i class='bx bx-x text-base'></i>
                                                <span class="hidden sm:inline ml-1">Tolak</span>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <nav class="flex items-center justify-between mt-6">
                            <div class="text-sm text-gray-600">
                                Menampilkan halaman <?php echo $halaman_sekarang; ?> dari <?php echo $total_halaman; ?>
                            </div>
                            <div class="flex items-center space-x-2">
                                <?php if($halaman_sekarang > 1): ?>
                                    <a href="ulasan-admin?page=<?php echo $halaman_sekarang - 1; ?>" class="px-4 py-2 text-sm font-semibold bg-gray-100 text-HitamTeks rounded-lg hover:bg-OrenTua hover:text-white transition">
                                        &laquo;
                                    </a>
                                <?php else: ?>
                                    <span class="px-4 py-2 text-sm font-semibold bg-gray-50 text-gray-400 rounded-lg cursor-not-allowed">&laquo;</span>
                                <?php endif; ?>

                                <?php 
                                $start_page = max(1, $halaman_sekarang - 1);
                                $end_page = min($total_halaman, $halaman_sekarang + 1);

                                for($i = $start_page; $i <= $end_page; $i++):
                                    $is_active = ($i == $halaman_sekarang);
                                    $active_class = $is_active ? 'bg-OrenTua text-white' : 'bg-gray-100 text-HitamTeks hover:bg-OrenTua hover:text-white';
                                ?>
                                    <a href="ulasan-admin?page=<?php echo $i; ?>" class="px-4 py-2 text-sm font-semibold rounded-lg transition <?php echo $active_class; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if($halaman_sekarang < $total_halaman): ?>
                                    <a href="ulasan-admin?page=<?php echo $halaman_sekarang + 1; ?>" class="px-4 py-2 text-sm font-semibold bg-gray-100 text-HitamTeks rounded-lg hover:bg-OrenTua hover:text-white transition">
                                        &raquo;
                                    </a>
                                <?php else: ?>
                                    <span class="px-4 py-2 text-sm font-semibold bg-gray-50 text-gray-400 rounded-lg cursor-not-allowed">&raquo;</span>
                                <?php endif; ?>
                            </div>
                        </nav>
                    <?php else: ?>
                        <div class="p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg shadow-soft">
                            Tidak ada ulasan yang menunggu persetujuan saat ini.
                        </div>
                    <?php endif; ?>

                </div>
                </main>
        </div>
        </div>
    
    <script>
        // Data ulasan lengkap untuk ditampilkan di modal
        const fullUlasanData = <?php echo json_encode($ulasan_data_js); ?>;

        function showFullUlasan(ulasanId) {
            const fullText = fullUlasanData[ulasanId];
            if (fullText) {
                Swal.fire({
                    title: 'Isi Ulasan',
                    // Gunakan pre-wrap agar baris baru terbaca dan styling mirip admin.php
                    html: <div style="text-align: left; white-space: pre-wrap; word-break: break-word; max-height: 400px; overflow-y: auto; padding: 16px; border: 1px solid #e5e7eb; background-color: #f3f4f6; border-radius: 8px; font-size: 14px; color: #374151;">${fullText}</div>,
                    icon: 'info',
                    showCloseButton: true,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'w-11/12 md:w-1/2 lg:w-1/3'
                    }
                });
            }
        }

        // Script Sidebar Toggle
        document.getElementById('toggle-sidebar').addEventListener('click', function() {
            var sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        });
    </script>
</body>
</html>