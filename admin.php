<?php
// Include file koneksi database
include 'koneksi.php'; // Pastikan file ini ada dan berisi koneksi MySQL

// ========== PROSES BATALKAN BOOKING ==========
if(isset($_GET['batalkan'])){
    $id = $_GET['batalkan'];
    
    // Query untuk update status menjadi 'Dibatalkan'
    $query = "UPDATE tb_form SET status = 'Dibatalkan' WHERE id = $id";
    
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
    $query = "UPDATE tb_form SET status = 'Selesai' WHERE id = $id";
    
    if(mysqli_query($conn, $query)){
        header("Location: admin.php?pesan=selesai");
        exit;
    } else {
        echo "Error selesai: " . mysqli_error($conn);
    }
}

// ========== PROSES MENGAKTIFKAN KEMBALI BOOKING (Dibatalkan -> Aktif) ==========
if(isset($_GET['aktifkan'])){
    $id = $_GET['aktifkan'];
    
    // Query untuk update status kembali menjadi 'Aktif'
    $query = "UPDATE tb_form SET status = 'Aktif' WHERE id = $id";
    
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
    // Hindari SQL Injection, namun untuk contoh ini kita asumsikan input bersih
    $status_filter_safe = mysqli_real_escape_string($conn, $status_filter);
    $query = "SELECT * FROM tb_form WHERE status = '$status_filter_safe' ORDER BY id DESC";
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - CandyVet</title>
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
        
        .logo img {
            width: 50px;
            height: 50px;
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
        
        .menu-icon {
            font-size: 24px;
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
            background: #FF9933; /* Mengganti FDB54E dengan FF9933 (warna logo/header) */
            color: white; /* Mengganti teks hitam menjadi putih agar kontras */
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
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none; /* Tambahkan ini agar link terlihat seperti tombol */
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
        
        .btn-edit {
            background: #FFF3E0; /* Warna Orange Muda untuk Edit */
            color: #FF9933;
        }
        
        .btn-edit:hover {
            background: #FF9933;
            color: white;
            transform: scale(1.1);
        }

        .btn-complete { /* Tombol Selesai */
            background: #E8F5E9;
            color: #388E3C;
        }
        
        .btn-complete:hover {
            background: #388E3C;
            color: white;
            transform: scale(1.1);
        }

        .btn-revert { /* Tombol Aktifkan Kembali */
            background: #E6D5F5; 
            color: #8B3DFF;
        }
        
        .btn-revert:hover {
            background: #8B3DFF;
            color: white;
            transform: scale(1.1);
        }
        
        .btn-delete {
            background: #FFEBEE;
            color: #D32F2F;
        }
        
        .btn-delete:hover {
            background: #D32F2F;
            color: white;
            transform: scale(1.1);
        }
        
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
        
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #999;
            font-size: 18px;
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
            <svg width="50" height="50" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="25" cy="25" r="25" fill="#FF9933"/>
                <path d="M25 10C18.37 10 13 15.37 13 22C13 30 25 40 25 40C25 40 37 30 37 22C37 15.37 31.63 10 25 10ZM25 26C22.79 26 21 24.21 21 22C21 19.79 22.79 18 25 18C27.21 18 29 19.79 29 22C29 24.21 27.21 26 25 26Z" fill="white"/>
            </svg>
            <h2>CandyVet</h2>
        </div>
        
        <ul class="menu">
            <li class="menu-item active">
                <span class="menu-icon">📋</span>
                <span class="menu-text">Booking</span>
            </li>
            <li class="menu-item">
                <span class="menu-icon">💝</span>
                <span class="menu-text">Layanan</span>
            </li>
            <li class="menu-item logout">
                <span class="menu-icon">🚪</span>
                <span class="menu-text">Keluar</span>
            </li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h1>RIWAYAT BOOKING</h1>
            <div class="search-box">
                <input type="text" placeholder="Cari" id="searchInput">
                <button>🔍</button>
            </div>
        </div>
        
        <?php
        // Tampilkan notifikasi jika ada
        if(isset($_GET['pesan'])){
            $alert_text = '';
            if($_GET['pesan'] == 'dibatalkan'){
                $alert_text = '✓ Booking berhasil dibatalkan!';
            } elseif($_GET['pesan'] == 'selesai'){
                $alert_text = '✓ Booking berhasil ditandai selesai!';
            } elseif($_GET['pesan'] == 'aktifkan'){
                $alert_text = '✓ Booking berhasil diaktifkan kembali!';
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
                                    <td><strong><?php echo $row['nm_majikan']; ?></strong></td>
                                    <td><?php echo $row['nm_hewan']; ?></td>
                                    <td><?php echo $row['jenis_hewan']; ?></td>
                                    <td><?php echo $row['usia_hewan']; ?> tahun</td>
                                    <td>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="detail_booking.php?id=<?php echo $row['id']; ?>" 
                                               class="btn-icon btn-detail" title="Detail">📄</a>
                                            
                                            <a href="edit_booking.php?id=<?php echo $row['id']; ?>" 
                                               class="btn-icon btn-edit" title="Edit">✏️</a>

                                            <?php if($row['status'] == 'Aktif'){ ?>
                                                <a href="#" class="btn-icon btn-complete" 
                                                   onclick="if(confirm('Tandai booking ini sebagai selesai?')) window.location.href='admin.php?selesai=<?php echo $row['id']; ?>'"
                                                   title="Tandai Selesai">✓</a>
                                                <a href="#" class="btn-icon btn-delete" 
                                                   onclick="if(confirm('Yakin ingin membatalkan booking ini?')) window.location.href='admin.php?batalkan=<?php echo $row['id']; ?>'"
                                                   title="Batalkan">🗑️</a>
                                            <?php } elseif($row['status'] == 'Dibatalkan'){ ?>
                                                <a href="#" class="btn-icon btn-revert" 
                                                   onclick="if(confirm('Aktifkan kembali booking ini?')) window.location.href='admin.php?aktifkan=<?php echo $row['id']; ?>'"
                                                   title="Aktifkan Kembali">🔄</a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="7" class="no-data">📭 Tidak ada data booking</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <button class="fab">
            <span style="font-size: 24px;">➕</span>
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
// Tutup koneksi database
mysqli_close($conn);
?>