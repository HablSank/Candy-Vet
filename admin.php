<?php
// Include file koneksi database
include 'koneksi.php'; // Pastikan file ini ada dan berisi koneksi MySQL

// Cek apakah koneksi berhasil di-include dan variabel $conn tersedia
if (!isset($conn)) {
    // Tambahkan penanganan error jika koneksi gagal
    die("Koneksi database gagal dimuat. Pastikan 'koneksi.php' mendefinisikan \$conn.");
}

// ========== PROSES BATALKAN BOOKING ==========
if(isset($_GET['batalkan'])){
    $id = $_GET['batalkan'];
    
    // Query untuk update status menjadi 'Dibatalkan'
    // PERHATIAN: PENGGUNAAN VARIABEL LANGSUNG DI QUERY SANGAT RENTAN SQL INJECTION.
    $query = "UPDATE tb_form SET status = 'Dibatalkan' WHERE id = " . mysqli_real_escape_string($conn, $id);
    
    if(mysqli_query($conn, $query)){
        header("Location: admin.php?pesan=dibatalkan");
        exit;
    } else {
        echo "Error batalkan: " . mysqli_error($conn);
    }
}

// ========== PROSES TANDAI SELESAI ==========
if(isset($_GET['selesai'])){
    $id = $_GET['selesai'];
    
    // Query untuk update status menjadi 'Selesai'
    $query = "UPDATE tb_form SET status = 'Selesai' WHERE id = " . mysqli_real_escape_string($conn, $id);
    
    if(mysqli_query($conn, $query)){
        header("Location: admin.php?pesan=selesai");
        exit;
    } else {
        echo "Error selesai: " . mysqli_error($conn);
    }
}

// ========== PROSES MENGAKTIFKAN KEMBALI BOOKING (Dibatalkan/Selesai -> Aktif) ==========
if(isset($_GET['aktifkan'])){
    $id = $_GET['aktifkan'];
    
    // Query untuk update status kembali menjadi 'Aktif'
    // Proses ini digunakan untuk status Dibatalkan DAN Selesai
    $query = "UPDATE tb_form SET status = 'Aktif' WHERE id = " . mysqli_real_escape_string($conn, $id);
    
    if(mysqli_query($conn, $query)){
        header("Location: admin.php?pesan=aktifkan");
        exit;
    } else {
        echo "Error aktifkan: " . mysqli_error($conn);
    }
}

// ========== FILTER STATUS ==========
// Ambil parameter status dari URL (jika ada)
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'semua';

// Buat query berdasarkan filter
if($status_filter == 'semua'){
    $query = "SELECT * FROM tb_form ORDER BY id DESC";
} else {
    // Hindari SQL Injection dengan mysqli_real_escape_string
    $status_filter_safe = mysqli_real_escape_string($conn, $status_filter);
    $query = "SELECT * FROM tb_form WHERE status = '$status_filter_safe' ORDER BY id DESC";
}
$result = mysqli_query($conn, $query);

// Cek jika query gagal (misalnya karena koneksi hilang)
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #FDB54E;
            min-height: 100vh;
            display: flex;
        }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            background: #FFF4E6;
            padding: 30px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 0 30px;
            margin-bottom: 40px;
        }
        
        /* Style untuk gambar logo */
        .logo img {
            width: 60px; /* Sesuaikan ukuran sesuai kebutuhan */
            height: 60px; /* Sesuaikan ukuran sesuai kebutuhan */
            object-fit: contain; /* Memastikan gambar tidak terdistorsi */
        }
        
        .logo h2 {
            color: #FF9933;
            font-size: 28px;
            font-weight: 700;
        }
        
        .menu {
            list-style: none;
        }
        
        .menu-item {
            padding: 15px 30px;
            margin: 5px 0;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 18px;
            font-weight: 600;
            color: #333;
            text-decoration: none;
        }
        
        .menu-item.active {
            background: #8B3DFF;
            color: white;
            border-radius: 0 25px 25px 0;
            margin-right: 20px;
        }
        
        .menu-item:hover {
            background: #E6D5F5;
            border-radius: 0 25px 25px 0;
            margin-right: 20px;
        }
        
        .menu-item.active:hover {
            background: #7B2FEF;
        }

        /* Style untuk ikon Boxicons di sidebar */
        .menu-icon i {
            font-size: 24px;
        }
        /* Pastikan warna ikon diubah saat menu aktif */
        .menu-item.active .menu-icon i {
            color: white; 
        }
        
        .logout {
            position: absolute;
            bottom: 30px;
            width: 100%;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 40px;
        }
        
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: white;
            font-size: 36px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .search-box {
            position: relative;
            width: 400px;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 45px 12px 20px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            outline: none;
        }
        
        .search-box button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            padding: 8px 15px;
        }
        
        /* Alert */
        .alert {
            background: white;
            padding: 15px 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            color: #155724;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Card Container */
        .card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        
        .card-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .card-header h2 {
            color: #333;
            font-size: 28px;
            font-weight: 700;
            position: relative;
            display: inline-block;
            padding-bottom: 15px;
        }
        
        .card-header h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: #8B3DFF;
            border-radius: 2px;
        }
        
        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .tab {
            padding: 12px 25px;
            background: #F5F5F5;
            border: none;
            border-radius: 12px;
            text-decoration: none;
            color: #666;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .tab:hover {
            background: #E6D5F5;
            transform: translateY(-2px);
        }
        
        .tab.active {
            background: #8B3DFF;
            color: white;
        }
        
        /* Table */
        .table-wrapper {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }
        
        thead tr {
            background: #FF9933; /* Warna Header/Aksen */
            color: white;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 700;
            font-size: 15px;
            text-transform: capitalize;
        }
        
        th:first-child {
            border-radius: 12px 0 0 12px;
        }
        
        th:last-child {
            border-radius: 0 12px 12px 0;
        }
        
        tbody tr {
            background: #FFF8F0;
            transition: all 0.3s;
        }
        
        tbody tr:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        td {
            padding: 18px 15px;
            border: none;
        }
        
        tbody tr td:first-child {
            border-radius: 12px 0 0 12px;
        }
        
        tbody tr td:last-child {
            border-radius: 0 12px 12px 0;
        }
        
        /* Badge Status */
        .badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            display: inline-block;
        }
        
        .badge-aktif {
            background: #E8F5E9;
            color: #2E7D32;
        }
        
        .badge-baru {
            background: #FFF3E0;
            color: #E65100;
        }
        
        .badge-terkonfirmasi {
            background: #E3F2FD;
            color: #1565C0;
        }
        
        .badge-selesai {
            background: #E8EAF6;
            color: #283593;
        }
        
        .badge-dibatalkan {
            background: #FFEBEE;
            color: #C62828;
        }
        
        /* Action Buttons */
        .action-btns {
            display: flex;
            gap: 8px;
        }
        
        .btn-icon {
            width: 35px;
            height: 35px;
            border: none;
            border-radius: 8px; /* Bentuk kotak dengan sudut melengkung */
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        
        /* Style untuk SVG/Boxicons di dalam tombol */
        .btn-icon svg, .btn-icon i {
            width: 16px;
            height: 16px;
            stroke: currentColor; /* Hanya berlaku untuk SVG */
            fill: none; /* Hanya berlaku untuk SVG */
            color: currentColor; /* Untuk Boxicons */
            transition: all 0.3s;
        }
        /* Override Boxicons size */
        .btn-icon i {
            font-size: 18px; 
        }

        /* Ikon Pencarian */
        .search-box button svg {
            width: 20px;
            height: 20px;
        }
        
        .btn-detail {
            background: #E3F2FD;
            color: #1976D2;
        }
        
        .btn-detail:hover {
            background: #1976D2;
            color: white;
            transform: scale(1.1);
        }
        .btn-detail:hover svg, .btn-detail:hover i { stroke: white; color: white; }
        
        .btn-edit {
            background: #FFF3E0;
            color: #FF9933;
        }
        
        .btn-edit:hover {
            background: #FF9933;
            color: white;
            transform: scale(1.1);
        }
        .btn-edit:hover svg, .btn-edit:hover i { stroke: white; color: white; }

        .btn-complete {
            background: #E8F5E9;
            color: #388E3C;
        }
        
        .btn-complete:hover {
            background: #388E3C;
            color: white;
            transform: scale(1.1);
        }
        .btn-complete:hover svg, .btn-complete:hover i { stroke: white; color: white; }

        .btn-revert {
            background: #E6D5F5; 
            color: #8B3DFF;
        }
        
        .btn-revert:hover {
            background: #8B3DFF;
            color: white;
            transform: scale(1.1);
        }
        .btn-revert:hover svg, .btn-revert:hover i { stroke: white; color: white; }
        
        .btn-delete {
            background: #FFEBEE;
            color: #D32F2F;
        }
        
        .btn-delete:hover {
            background: #D32F2F;
            color: white;
            transform: scale(1.1);
        }
        .btn-delete:hover svg, .btn-delete:hover i { stroke: white; color: white; }
        
        /* Floating Action Button */
        .fab {
            position: fixed;
            bottom: 40px;
            right: 40px;
            background: #8B3DFF;
            color: white;
            padding: 18px 30px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            box-shadow: 0 8px 20px rgba(139, 61, 255, 0.4);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .fab:hover {
            background: #7B2FEF;
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(139, 61, 255, 0.5);
        }
        
        .fab i {
            font-size: 24px;
        }
        
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #999;
            font-size: 18px;
        }
        .no-data i {
            font-size: 30px;
            display: block;
            margin-bottom: 10px;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 80px;
            }
            
            .main-content {
                margin-left: 80px;
            }
            
            .logo h2,
            .menu-text {
                display: none;
            }
            
            .logo {
                justify-content: center;
            }
            
            .menu-item {
                justify-content: center;
                padding: 15px;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .header {
                flex-direction: column;
                gap: 20px;
            }
            
            .search-box {
                width: 100%;
            }
            
            table {
                font-size: 13px;
            }
            
            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="candyvet-removebg-preview 1.png" alt="CandyVet Logo"> 
            <h2>CandyVet</h2>
        </div>
        
        <ul class="menu">
            <li class="menu-item active">
                <span class="menu-icon"><i class='bx bx-clipboard'></i></span>
                <span class="menu-text">Booking</span>
            </li>
            <li class="menu-item">
                <span class="menu-icon"><i class='bx bx-heart'></i></span>
                <span class="menu-text">Layanan</span>
            </li>
            <li class="menu-item logout">
                <span class="menu-icon"><i class='bx bx-log-out'></i></span>
                <span class="menu-text">Keluar</span>
            </li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h1>RIWAYAT BOOKING</h1>
            <div class="search-box">
                <input type="text" placeholder="Cari" id="searchInput">
                <button>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
            </div>
        </div>
        
        <?php
        // Tampilkan notifikasi 
        if(isset($_GET['pesan'])){
            $alert_text = '';
            if($_GET['pesan'] == 'dibatalkan'){
                $alert_text = '✓ Booking berhasil dibatalkan!';
            } elseif($_GET['pesan'] == 'selesai'){
                $alert_text = '✓ Booking berhasil ditandai selesai!';
            } elseif($_GET['pesan'] == 'aktifkan'){
                $alert_text = '✓ Status booking berhasil diaktifkan kembali!';
            }
            if($alert_text) {
                echo '<div class="alert">' . $alert_text . '</div>';
            }
        }
        ?>
        
        <div class="card">
            <div class="card-header">
                <h2><?php echo ucfirst($status_filter); ?> Bookings</h2>
            </div>
            
            <div class="filter-tabs">
                <a href="admin.php?status=semua" 
                    class="tab <?php echo ($status_filter == 'semua') ? 'active' : ''; ?>">
                    Semua
                </a>
                <a href="admin.php?status=Aktif" 
                    class="tab <?php echo ($status_filter == 'Aktif') ? 'active' : ''; ?>">
                    Aktif
                </a>
                <a href="admin.php?status=Selesai" 
                    class="tab <?php echo ($status_filter == 'Selesai') ? 'active' : ''; ?>">
                    Selesai
                </a>
                <a href="admin.php?status=Dibatalkan" 
                    class="tab <?php echo ($status_filter == 'Dibatalkan') ? 'active' : ''; ?>">
                    Dibatalkan
                </a>
            </div>
            
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Majikan</th>
                            <th>Nama Hewan</th>
                            <th>Jenis Hewan</th>
                            <th>Usia</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if(mysqli_num_rows($result) > 0){
                            $no = 1;
                            while($row = mysqli_fetch_assoc($result)){
                                // Tentukan class badge berdasarkan status
                                $badge_class = 'badge-aktif';
                                if($row['status'] == 'Selesai'){
                                    $badge_class = 'badge-selesai';
                                } elseif($row['status'] == 'Dibatalkan'){
                                    $badge_class = 'badge-dibatalkan';
                                }
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['nm_majikan']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['nm_hewan']); ?></td>
                                    <td><?php echo htmlspecialchars($row['jenis_hewan']); ?></td>
                                    <td><?php echo htmlspecialchars($row['usia_hewan']); ?> tahun</td>
                                    <td>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="detail_booking.php?id=<?php echo $row['id']; ?>" 
                                            class="btn-icon btn-detail" title="Detail">
                                             <i class='bx bx-file-text'></i> </a>
                                            
                                            <a href="edit_booking.php?id=<?php echo $row['id']; ?>" 
                                            class="btn-icon btn-edit" title="Edit">
                                             <i class='bx bx-pencil'></i> </a>

                                            <?php 
                                            // Aksi untuk status 'Aktif'
                                            if($row['status'] == 'Aktif'){ 
                                            ?>
                                            <a href="#" class="btn-icon btn-complete" 
                                             onclick="if(confirm('Tandai booking ini sebagai selesai?')) window.location.href='admin.php?selesai=<?php echo $row['id']; ?>'"
                                             title="Tandai Selesai">
                                             <i class='bx bx-check-circle'></i> </a>
                                            <a href="#" class="btn-icon btn-delete" 
                                             onclick="if(confirm('Yakin ingin membatalkan booking ini?')) window.location.href='admin.php?batalkan=<?php echo $row['id']; ?>'"
                                             title="Batalkan">
                                             <i class='bx bx-trash'></i> </a>
                                            <?php 
                                            // Aksi untuk status 'Selesai' atau 'Dibatalkan'
                                            } elseif($row['status'] == 'Selesai' || $row['status'] == 'Dibatalkan'){ 
                                            ?>
                                            <a href="#" class="btn-icon btn-revert" 
                                             onclick="if(confirm('Aktifkan kembali status booking ini menjadi Aktif?')) window.location.href='admin.php?aktifkan=<?php echo $row['id']; ?>'"
                                             title="Aktifkan Kembali">
                                             <i class='bx bx-undo'></i> </a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="7" class="no-data"><i class="bx bx-folder-open"></i> Tidak ada data booking</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <button class="fab">
                        <i class='bx bx-plus'></i>
            Tambah Booking Baru
        </button>
    </div>
    
    <script>
        // Simple search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
<?php
// Tutup koneksi database di akhir file PHP
if (isset($conn)) {
    mysqli_close($conn);
}
?>