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

$items_per_page = 5;
$halaman_sekarang = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($halaman_sekarang < 1) $halaman_sekarang = 1;
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
    </style>
</head>
<body class="bg-OrenMuda font-sans flex min-h-screen text-HitamTeks">

    <!-- SIDEBAR -->
    <aside class="w-72 bg-PutihCard p-8 fixed h-screen rounded-e-3xl flex flex-col justify-between shadow-lg">
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
                        <i class='bx bx-store text-2xl'></i>
                        Riwayat Ulasan
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="pt-4 border-t border-OrenTua">
            <a href="logout" class="flex items-center gap-3 py-3 px-5 text-HitamTeks hover:bg-gray-100 hover:shadow-soft hover:text-UnguAksen font-semibold rounded-xl transition-all">
                <i class='bx bx-log-out text-2xl'></i> Keluar
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 lg:ml-72 p-10">

        <!-- Header dan Search Bar -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <h1 class="text-HitamTeks text-4xl font-extrabold drop-shadow">RIWAYAT ULASAN</h1>
            <div class="relative w-full md:w-80">
                <input type="text" placeholder="Cari ulasan..." id="searchInput"
                    class="w-full py-3 pl-5 pr-12 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-OrenTua text-gray-700">
                <i class='bx bx-search absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 text-xl'></i>
            </div>
        </header>

        <!-- Pesan Alert -->
        <?php
        if(isset($_GET['pesan'])){
            $alert_text = '';
            if($_GET['pesan'] == 'approved') $alert_text = '✓ Ulasan berhasil disetujui!';
            elseif($_GET['pesan'] == 'rejected') $alert_text = '✓ Ulasan berhasil ditolak!';

            if($alert_text){
                echo '<div class="bg-green-100 text-green-800 font-semibold p-4 rounded-xl mb-6 shadow-soft animate-slideDown">'.$alert_text.'</div>';
            }
        }
        ?>

        <!-- Card Konten Utama -->
        <div class="bg-PutihCard p-8 rounded-3xl shadow-soft">

            <!-- Judul dengan Garis Oren Tua -->
            <div class="text-center mb-8">
                <h2 class="text-HitamTeks text-2xl font-bold relative inline-block pb-1.5">
                    Ulasan Menunggu Persetujuan
                    <span class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full h-0.5 bg-OrenTua rounded-full"></span>
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-separate table-spacing">
                    <thead>
                        <tr>
                            <?php
                            $headers = ['No.', 'Nama Majikan', 'Nama Hewan', 'Tanggal', 'Ulasan', 'Aksi'];
                            foreach($headers as $i => $h){
                                $rounded_l = ($i==0) ? 'rounded-l-xl' : '';
                                $rounded_r = ($i==count($headers)-1) ? 'rounded-r-xl' : '';
                                echo "<th class='p-4 text-left font-bold text-sm uppercase text-white bg-OrenTua $rounded_l $rounded_r'>$h</th>";
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if($result->num_rows > 0){
                            $no = $offset + 1;
                            
                            $data_rows = [];
                            while($row = $result->fetch_assoc()) {
                                $data_rows[] = $row;
                            }
                            
                            $total_rows_on_page = count($data_rows);
                            
                            foreach($data_rows as $index => $row){
                                $border_class = ($index < $total_rows_on_page - 1) ? 'border-b border-OrenTua' : ''; 
                                
                                $ulasan_lengkap = htmlspecialchars($row['ulasan']);
                                $ulasan_singkat = strlen($ulasan_lengkap) > 60 ? substr($ulasan_lengkap, 0, 60) . '...' : $ulasan_lengkap;
                                
                                echo "<tr class='bg-PutihCard hover:bg-orange-50 transition'>"; 
                                
                                echo "<td class='p-4 font-semibold text-HitamTeks $border_class'>$no</td>";
                                echo "<td class='p-4 font-semibold text-HitamTeks $border_class'>".htmlspecialchars($row['nm_majikan'])."</td>";
                                echo "<td class='p-4 text-HitamTeks $border_class'>".htmlspecialchars($row['nm_hewan'])."</td>";
                                
                                $raw_date = $row['tgl_ulasan'] ?? "";
                                if (!empty($raw_date) && $raw_date != '0000-00-00') {
                                    $formatted_date = date('d F Y', strtotime($raw_date)); 
                                } else {
                                    $formatted_date = "-";
                                }
                                echo "<td class='p-4 text-HitamTeks $border_class'>".htmlspecialchars($formatted_date)."</td>";
                                
                                echo "<td class='p-4 text-HitamTeks $border_class max-w-xs'>";
                                echo "<span class='block'>".$ulasan_singkat."</span>";
                                if (strlen($ulasan_lengkap) > 60) {
                                    echo "<button onclick='showFullUlasan(".$row['id'].")' class='text-UnguAksen hover:text-OrenTua font-semibold underline text-xs mt-1'>Baca Selengkapnya</button>";
                                }
                                echo "</td>";
                                
                                echo "<td class='p-4 $border_class'>
                                    <div class='flex gap-2 items-center'>
                                        <a href='ulasan-admin?action=approve&id={$row['id']}{$redirect_params}' class='w-8 h-8 flex items-center justify-center bg-green-100 text-green-700 rounded-full hover:bg-green-700 hover:text-white transition' title='Setujui Ulasan'><i class='bx bx-check text-lg'></i></a>
                                        <a href='ulasan-admin?action=reject&id={$row['id']}{$redirect_params}' class='w-8 h-8 flex items-center justify-center bg-red-100 text-red-700 rounded-full hover:bg-red-700 hover:text-white transition' title='Tolak Ulasan'><i class='bx bx-x text-lg'></i></a>
                                    </div>
                                </td>";
                                
                                echo "</tr>";
                                $no++;
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center p-12 text-gray-500"><i class="bx bx-folder-open text-3xl mb-2 block"></i>Tidak ada ulasan yang menunggu persetujuan</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>

                <?php if ($total_halaman > 1): ?>
                <nav class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mt-8 pt-6 border-t border-OrenMuda">
                    
                    <div class="text-sm text-gray-500">
                        Halaman <span class="font-bold text-HitamTeks"><?php echo $halaman_sekarang; ?></span> dari <span class="font-bold text-HitamTeks"><?php echo $total_halaman; ?></span>
                        (Total <?php echo $total_ulasan; ?> ulasan)
                    </div>

                    <div class="flex gap-2 flex-wrap mx-auto">
                        <?php if($halaman_sekarang > 1): ?>
                            <a href="ulasan-admin?page=<?php echo $halaman_sekarang - 1; ?>" class="px-4 py-2 text-sm font-semibold bg-gray-100 text-HitamTeks rounded-lg hover:bg-OrenTua hover:text-white transition">
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
                            <a href="ulasan-admin?page=<?php echo $i; ?>" class="px-4 py-2 text-sm font-semibold rounded-lg transition <?php echo $active_class; ?> <?php if(!$is_active) echo 'hidden sm:block'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if($halaman_sekarang < $total_halaman): ?>
                            <a href="ulasan-admin?page=<?php echo $halaman_sekarang + 1; ?>" class="px-4 py-2 text-sm font-semibold bg-gray-100 text-HitamTeks rounded-lg hover:bg-OrenTua hover:text-white transition">
                                <span class="relative bottom-0.5">&raquo;</span>
                            </a>
                        <?php else: ?>
                            <span class="px-4 py-2 text-sm font-semibold bg-gray-50 text-gray-400 rounded-lg cursor-not-allowed relative bottom-0.5">&raquo;</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="text-sm text-gray-500 invisible">
                    </div>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        const fullUlasanData = <?php echo json_encode($ulasan_data_js); ?>;
    </script>
    <script src="ulasan-admin.js"></script>
</body>
</html>
<?php if(isset($conn)) mysqli_close($conn); ?>