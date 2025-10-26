<?php
session_start();
if(!isset($_SESSION['user'])){
    header('location:login.php');
}
include 'koneksi.php'; 

// Cek apakah variabel koneksi $conn sudah tersedia dari file 'koneksi.php'
if (!isset($conn)) {
    die("Koneksi database gagal dimuat. Pastikan 'koneksi.php' mendefinisikan \$conn.");
}

// Logic untuk Batalkan, Selesai, Aktifkan
if(isset($_GET['batalkan'])){
    $id = $_GET['batalkan'];
    // Gunakan mysqli_real_escape_string untuk sanitasi input sebelum query
    $query = "UPDATE tb_form SET status = 'Dibatalkan' WHERE id = " . mysqli_real_escape_string($conn, $id);
    
    if(mysqli_query($conn, $query)){
        header("Location: admin.php?pesan=dibatalkan");
        exit;
    } else {
        echo "Error batalkan: " . mysqli_error($conn);
    }
}

if(isset($_GET['selesai'])){
    $id = $_GET['selesai'];
    $query = "UPDATE tb_form SET status = 'Selesai' WHERE id = " . mysqli_real_escape_string($conn, $id);
    
    if(mysqli_query($conn, $query)){
        header("Location: admin.php?pesan=selesai");
        exit;
    } else {
        echo "Error selesai: " . mysqli_error($conn);
    }
}

if(isset($_GET['aktifkan'])){
    $id = $_GET['aktifkan'];
    $query = "UPDATE tb_form SET status = 'Aktif' WHERE id = " . mysqli_real_escape_string($conn, $id);
    
    if(mysqli_query($conn, $query)){
        header("Location: admin.php?pesan=aktifkan");
        exit;
    } else {
        echo "Error aktifkan: " . mysqli_error($conn);
    }
}

// Logic Filter Status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'semua';

if($status_filter == 'semua'){
    $query = "SELECT * FROM tb_form ORDER BY id ASC";
} else {
    $status_filter_safe = mysqli_real_escape_string($conn, $status_filter);
    $query = "SELECT * FROM tb_form WHERE status = '$status_filter_safe' ORDER BY id ASC";
}
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}
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
        /* Gaya umum */
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
            background-color: #dc3545 !important;
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
                    <a href="admin.php" class="flex items-center gap-3 py-3 px-5 bg-OrenTua text-white font-semibold rounded-xl shadow-soft transition-all hover:bg-UnguAksen hover:shadow-lg">
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
            <script>
            function confirmlogout() {
                Swal.fire({
                    title: 'Yakin ingin keluar?',
                    text: "Anda akan logout dari sistem",
                    icon: 'question',
                    showCancelButton: true,
                    // Menggunakan OrenTua 
                    confirmButtonColor: '#FA812F', 
                    // Menggunakan OrenMuda 
                    cancelButtonColor: '#FAB12F', 
                    confirmButtonText: 'Ya, Logout',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'swal2-confirm',
                        cancelButton: 'swal2-cancel'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'logout.php';
                    }
                });
            }
            </script>
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

        <!-- Pesan Alert -->
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
                    echo "<a href='admin.php?status=$key' class='px-5 py-2 font-semibold rounded-xl transition hover:scale-[1.02] hover:bg-OrenTua hover:text-white $active'>$label</a>";
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
                            $no = 1;
                            $total_rows = mysqli_num_rows($result); // Ambil total baris untuk logika border
                            mysqli_data_seek($result, 0); // Pastikan pointer hasil kembali ke awal
                            
                            while($row = mysqli_fetch_assoc($result)){
                                $badge_class = 'bg-yellow-100 text-yellow-800';
                                if($row['status'] == 'Aktif') $badge_class = 'bg-green-100 text-green-700'; // Aktif/Sedang Berjalan
                                elseif($row['status'] == 'Selesai') $badge_class = 'bg-blue-100 text-blue-700';
                                elseif($row['status'] == 'Dibatalkan') $badge_class = 'bg-red-100 text-red-700';
                                
                                // Terapkan border-b (horizontal line) Oren Tua kecuali pada baris terakhir
                                $border_class = ($no < $total_rows) ? 'border-b border-OrenTua' : '';
                                
                                // Baris tabel menggunakan latar belakang Putih (bg-PutihCard) 
                                echo "<tr class='bg-PutihCard hover:bg-orange-50 transition'>"; // Hover tetap menggunakan warna terang
                                
                                // Terapkan p-4 (padding) dan border_class ke setiap cell
                                echo "<td class='p-4 font-semibold text-HitamTeks $border_class'>$no</td>";
                                echo "<td class='p-4 font-semibold text-HitamTeks $border_class'>".htmlspecialchars($row['nm_majikan'] ?? "")."</td>";
                                echo "<td class='p-4 text-HitamTeks $border_class'>".htmlspecialchars($row['nm_hewan'] ?? "")."</td>";
                                echo "<td class='p-4 text-HitamTeks $border_class'>".htmlspecialchars($row['jenis_hewan'] ?? "")."</td>";
                                
                                // Kolom Tanggal Booking
                                $raw_date = $row['tanggal_booking'] ?? "";
                                if (!empty($raw_date) && $raw_date != '0000-00-00') {
                                    $formatted_date = date('d F Y', strtotime($raw_date)); 
                                } else {
                                    $formatted_date = "-";
                                }

                                echo "<td class='p-4 text-HitamTeks $border_class'>".htmlspecialchars($formatted_date)."</td>";

                                // Status cell
                                echo "<td class='p-4 $border_class'><span class='px-4 py-1.5 rounded-full text-xs font-bold $badge_class'>".htmlspecialchars($row['status'] ?? "")."</span></td>";
                                
                                // Cell Aksi (Posisi tombol Detail dan Edit dibalik)
                                echo "<td class='p-4 $border_class'>
                                        <div class='flex gap-2 items-center'>
                                            
                                            <!-- Tombol Detail (File) - Sekarang di depan -->
                                            <a href='detail-booking.php?id={$row['id']}' class='w-8 h-8 flex items-center justify-center bg-gray-100 text-gray-700 rounded-full hover:bg-gray-700 hover:text-white transition' title='Lihat Detail'><i class=\"bx bx-file text-md\"></i></a>

                                            <!-- Tombol Edit (Pencil) - Sekarang di belakang -->
                                            <a href='booking-admin.php?id={$row['id']}' class='w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-700 rounded-full hover:bg-blue-700 hover:text-white transition' title='Edit Booking'><i class=\"bx bx-pencil text-md\"></i></a>
                                            ";
                                            
                                if($row['status']=='Aktif'){
                                    echo "
                                            <a href='#' onclick=\"confirmSelesai({$row['id']})\" class='w-8 h-8 flex items-center justify-center bg-green-100 text-green-700 rounded-full hover:bg-green-700 hover:text-white transition' title='Tandai Selesai'><i class=\"bx bx-check-circle text-md\"></i></a>
                                            <a href='#' onclick=\"confirmBatalkan({$row['id']})\" class='w-8 h-8 flex items-center justify-center bg-red-100 text-red-700 rounded-full hover:bg-red-700 hover:text-white transition' title='Batalkan Booking'><i class=\"bx bx-x-circle text-md\"></i></a>
                                            ";
                                } elseif ($row['status'] == 'Selesai' || $row['status'] == 'Dibatalkan') {
                                    echo "
                                            <a href='#' onclick=\"confirmAktifkan({$row['id']})\" class='w-8 h-8 flex items-center justify-center bg-gray-200 text-HitamTeks rounded-full hover:bg-OrenTua hover:text-white transition' title='Aktifkan Kembali'><i class=\"bx bx-undo text-md\"></i></a>
                                            ";
                                }

                                echo "</div></td></tr>";
                                $no++;
                            }
                        } else {
                            // Colspan diubah dari 6 menjadi 7 karena penambahan kolom 'Tanggal Booking'
                            echo '<tr><td colspan="7" class="text-center p-12 text-gray-500"><i class="bx bx-folder-open text-3xl mb-2 block"></i>Tidak ada data booking</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tombol Tambah Booking Baru (Aksen Ungu tetap dipertahankan untuk kontras CTA) -->
        <a href="booking-admin.php" class="fixed bottom-10 right-10">
            <button class="flex items-center gap-2 bg-UnguAksen text-white px-6 py-4 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition" title="Buat Booking Baru">
                <i class='bx bx-plus text-2xl'></i> Tambah Booking Baru
            </button>
        </a>

    </main>

    <script>
        // Fungsi-fungsi konfirmasi untuk aksi tabel
        function confirmSelesai(id) {
            Swal.fire({
                title: 'Tandai Selesai?',
                text: "Booking ini akan ditandai sebagai selesai. Lanjutkan?",
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Ya, Selesaikan',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'swal2-confirm',
                    cancelButton: 'swal2-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // KOREKSI SINTAKSIS JAVASCRIPT: Menggunakan backtick (`) untuk string literal
                    window.location.href = `admin.php?selesai=${id}`;
                }
            });
        }

        function confirmBatalkan(id) {
            Swal.fire({
                title: 'Batalkan Booking?',
                text: "Anda yakin ingin membatalkan booking ini? Status dapat diaktifkan kembali.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545', // Merah untuk Batalkan
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'swal2-confirm-red', // Gunakan kelas custom untuk warna merah
                    cancelButton: 'swal2-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // KOREKSI SINTAKSIS JAVASCRIPT: Menggunakan backtick (`) untuk string literal
                    window.location.href = `admin.php?batalkan=${id}`;
                }
            });
        }

        function confirmAktifkan(id) {
            Swal.fire({
                title: 'Aktifkan Kembali?',
                text: "Status booking akan diubah kembali menjadi Aktif.",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#9E00BA', // Menggunakan Ungu Aksen
                confirmButtonText: 'Ya, Aktifkan',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'swal2-confirm',
                    cancelButton: 'swal2-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // KOREKSI SINTAKSIS JAVASCRIPT: Menggunakan backtick (`) untuk string literal
                    window.location.href = `admin.php?aktifkan=${id}`;
                }
            });
        }
        
        // Fungsi Pencarian (memfilter baris tabel berdasarkan input)
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            let found = false;
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchText)) {
                    row.style.display = '';
                    found = true;
                } else {
                    row.style.display = 'none';
                }
            });
            // Opsional: Tambahkan logika untuk menampilkan pesan "Tidak ada hasil"
        });
    </script>
</body>
</html>
<?php if(isset($conn)) mysqli_close($conn); ?>
