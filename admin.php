<?php
include 'koneksi.php'; 

if (!isset($conn)) {
    die("Koneksi database gagal dimuat. Pastikan 'koneksi.php' mendefinisikan \$conn.");
}

if(isset($_GET['batalkan'])){
    $id = $_GET['batalkan'];
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
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#8B3DFF',
                        'secondary': '#FF9933',
                        'background': '#FDB54E',
                        'sidebar': '#FFF4E6',
                        'dark-text': '#333',
                    },
                    boxShadow: {
                        'custom': '0 8px 30px rgba(0,0,0,0.1)',
                    }
                }
            }
        }
    </script>
    <style>
        /* CSS Tambahan untuk animasi dan border-spacing */
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-slideDown {
            animation: slideDown 0.3s ease;
        }
        .table-spacing {
            border-spacing: 0 10px;
        }
        @media (min-width: 1024px) {
            .sidebar {
                display: block !important;
            }
        }
    </style>
</head>
<body class="font-sans min-h-screen flex bg-background">
    <div class="sidebar w-72 bg-sidebar p-8 fixed h-screen overflow-y-auto hidden lg:block">
        <div class="logo flex items-center gap-4 mb-10">
            <img src="candyvet-removebg-preview 1.png" alt="CandyVet Logo" class="w-16 h-16 object-contain"> 
            <h2 class="text-secondary text-3xl font-bold">CandyVet</h2>
        </div>
        
        <ul class="menu list-none">
            <li class="menu-item">
                <a href="#" class="flex items-center gap-4 py-4 px-6 text-lg font-semibold text-white bg-primary rounded-r-3xl my-1 transition-all duration-300 mr-5 shadow-lg active:bg-primary hover:bg-purple-700">
                    <span class="text-2xl"><i class='bx bx-clipboard'></i></span>
                    <span class="menu-text">Booking</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="flex items-center gap-4 py-4 px-6 text-lg font-semibold text-dark-text hover:bg-purple-100 rounded-r-3xl my-1 transition-all duration-300 mr-5">
                    <span class="text-2xl"><i class='bx bx-heart'></i></span>
                    <span class="menu-text">Layanan</span>
                </a>
            </li>
            <li class="menu-item absolute bottom-8 w-full left-0">
                <a href="#" class="flex items-center gap-4 py-4 px-6 text-lg font-semibold text-dark-text hover:bg-purple-100 rounded-r-3xl my-1 transition-all duration-300 mr-5">
                    <span class="text-2xl"><i class='bx bx-log-out'></i></span>
                    <span class="menu-text">Keluar</span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="main-content flex-1 p-10 lg:ml-72">
        
        <header class="header flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <h1 class="text-white text-4xl font-bold drop-shadow-md">RIWAYAT BOOKING</h1>
            <div class="search-box relative w-full md:w-96">
                <input type="text" placeholder="Cari" id="searchInput" class="w-full py-3 pl-5 pr-12 border-none rounded-full text-base focus:outline-none focus:ring-2 focus:ring-primary">
                <button class="absolute right-1 top-1/2 transform -translate-y-1/2 bg-transparent border-none cursor-pointer p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
            </div>
        </header>
        
        <?php
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
                echo '<div class="alert bg-white p-4 rounded-xl mb-5 text-green-700 font-semibold shadow-lg animate-slideDown">' . $alert_text . '</div>';
            }
        }
        ?>
        
        <div class="card bg-white rounded-3xl p-8 shadow-custom">
            
            <div class="card-header text-center mb-8">
                <h2 class="text-dark-text text-3xl font-bold relative inline-block pb-4">
                    <?php echo ucfirst($status_filter); ?> Bookings
                    <span class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-24 h-1 bg-primary rounded-full"></span>
                </h2>
            </div>
            
            <div class="filter-tabs flex gap-4 mb-6 flex-wrap">
                <a href="admin.php?status=semua" 
                    class="tab px-6 py-2 bg-gray-100 rounded-xl text-gray-600 font-semibold text-base transition duration-300 hover:bg-purple-100 hover:scale-[1.02] <?php echo ($status_filter == 'semua') ? 'bg-primary text-white hover:bg-primary' : ''; ?>">
                    Semua
                </a>
                <a href="admin.php?status=Aktif" 
                    class="tab px-6 py-2 bg-gray-100 rounded-xl text-gray-600 font-semibold text-base transition duration-300 hover:bg-purple-100 hover:scale-[1.02] <?php echo ($status_filter == 'Aktif') ? 'bg-primary text-white hover:bg-primary' : ''; ?>">
                    Aktif
                </a>
                <a href="admin.php?status=Selesai" 
                    class="tab px-6 py-2 bg-gray-100 rounded-xl text-gray-600 font-semibold text-base transition duration-300 hover:bg-purple-100 hover:scale-[1.02] <?php echo ($status_filter == 'Selesai') ? 'bg-primary text-white hover:bg-primary' : ''; ?>">
                    Selesai
                </a>
                <a href="admin.php?status=Dibatalkan" 
                    class="tab px-6 py-2 bg-gray-100 rounded-xl text-gray-600 font-semibold text-base transition duration-300 hover:bg-purple-100 hover:scale-[1.02] <?php echo ($status_filter == 'Dibatalkan') ? 'bg-primary text-white hover:bg-primary' : ''; ?>">
                    Dibatalkan
                </a>
            </div>
            
            <div class="table-wrapper overflow-x-auto">
                <table class="w-full border-separate table-spacing">
                    <thead>
                        <tr>
                            <th class="p-4 text-left font-bold text-sm uppercase text-white bg-secondary rounded-l-xl">No.</th>
                            <th class="p-4 text-left font-bold text-sm uppercase text-white bg-secondary">Nama Majikan</th>
                            <th class="p-4 text-left font-bold text-sm uppercase text-white bg-secondary">Nama Hewan</th>
                            <th class="p-4 text-left font-bold text-sm uppercase text-white bg-secondary">Jenis Hewan</th>
                            <th class="p-4 text-left font-bold text-sm uppercase text-white bg-secondary">Usia</th>
                            <th class="p-4 text-left font-bold text-sm uppercase text-white bg-secondary">Status</th>
                            <th class="p-4 text-left font-bold text-sm uppercase text-white bg-secondary rounded-r-xl">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if(mysqli_num_rows($result) > 0){
                            $no = 1;
                            while($row = mysqli_fetch_assoc($result)){
                                $badge_class = 'bg-green-100 text-green-800';
                                if($row['status'] == 'Selesai'){
                                    $badge_class = 'bg-indigo-100 text-indigo-800';
                                } elseif($row['status'] == 'Dibatalkan'){
                                    $badge_class = 'bg-red-100 text-red-800';
                                }
                                ?>
                                <tr class="bg-orange-50 transition duration-300 hover:shadow-lg hover:scale-[1.005]">
                                    <td class="p-4 rounded-l-xl"><?php echo $no++; ?></td>
                                    <td class="p-4"><strong><?php echo htmlspecialchars($row['nm_majikan']); ?></strong></td>
                                    <td class="p-4"><?php echo htmlspecialchars($row['nm_hewan']); ?></td>
                                    <td class="p-4"><?php echo htmlspecialchars($row['jenis_hewan']); ?></td>
                                    <td class="p-4"><?php echo htmlspecialchars($row['usia_hewan']); ?> tahun</td>
                                    <td class="p-4">
                                        <span class="badge px-4 py-2 rounded-full text-xs font-bold inline-block <?php echo $badge_class; ?>">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="p-4 rounded-r-xl">
                                        <div class="action-btns flex gap-2">
                                            <a href="detail_booking.php?id=<?php echo $row['id']; ?>" 
                                            class="btn-icon w-9 h-9 flex items-center justify-center rounded-lg cursor-pointer text-blue-700 bg-blue-100 transition duration-300 hover:bg-blue-700 hover:text-white hover:scale-110" title="Detail">
                                             <i class='bx bx-file-text text-lg'></i> </a>
                                            
                                            <a href="edit_booking.php?id=<?php echo $row['id']; ?>" 
                                            class="btn-icon w-9 h-9 flex items-center justify-center rounded-lg cursor-pointer text-secondary bg-orange-100 transition duration-300 hover:bg-secondary hover:text-white hover:scale-110" title="Edit">
                                             <i class='bx bx-pencil text-lg'></i> </a>

                                            <?php 
                                            if($row['status'] == 'Aktif'){ 
                                            ?>
                                            <a href="#" class="btn-icon w-9 h-9 flex items-center justify-center rounded-lg cursor-pointer text-green-700 bg-green-100 transition duration-300 hover:bg-green-700 hover:text-white hover:scale-110" 
                                             onclick="if(confirm('Tandai booking ini sebagai selesai?')) window.location.href='admin.php?selesai=<?php echo $row['id']; ?>'"
                                             title="Tandai Selesai">
                                             <i class='bx bx-check-circle text-lg'></i> </a>
                                            <a href="#" class="btn-icon w-9 h-9 flex items-center justify-center rounded-lg cursor-pointer text-red-700 bg-red-100 transition duration-300 hover:bg-red-700 hover:text-white hover:scale-110" 
                                             onclick="if(confirm('Yakin ingin membatalkan booking ini?')) window.location.href='admin.php?batalkan=<?php echo $row['id']; ?>'"
                                             title="Batalkan">
                                             <i class='bx bx-trash text-lg'></i> </a>
                                            <?php 
                                            } elseif($row['status'] == 'Selesai' || $row['status'] == 'Dibatalkan'){ 
                                            ?>
                                            <a href="#" class="btn-icon w-9 h-9 flex items-center justify-center rounded-lg cursor-pointer text-primary bg-purple-100 transition duration-300 hover:bg-primary hover:text-white hover:scale-110" 
                                             onclick="if(confirm('Aktifkan kembali status booking ini menjadi Aktif?')) window.location.href='admin.php?aktifkan=<?php echo $row['id']; ?>'"
                                             title="Aktifkan Kembali">
                                             <i class='bx bx-undo text-lg'></i> </a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="7" class="text-center p-16 text-gray-500 text-lg rounded-xl"><i class="bx bx-folder-open text-3xl block mb-2"></i> Tidak ada data booking</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <button class="fab fixed bottom-10 right-10 bg-primary text-white px-8 py-4 rounded-full font-bold shadow-lg transition duration-300 hover:bg-purple-700 hover:-translate-y-1 hover:shadow-xl flex items-center gap-2">
            <i class='bx bx-plus text-2xl'></i>
            Tambah Booking Baru
        </button>
    </div>
    
    <script>
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
if (isset($conn)) {
    mysqli_close($conn);
}
?>