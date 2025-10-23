<?php
session_start();
if(!isset($_SESSION['user'])){
    header('location:login.php');
}
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        orenGelap: '#F4631E',
                        orenTerang: '#FAB12F',
                        ungu: '#9E00BA',
                        krem: '#FFF7ED',
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
            border-spacing: 0 10px;
        }
    </style>
</head>
<body class="bg-krem font-sans flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-72 bg-orenTerang p-8 fixed h-screen lg:block rounded-e-3xl">
        <div class="flex items-center mb-10">
            <img src="./assets/logo.png" alt="CandyVet Logo" class="w-[100px] h-[100px] object-contain">
            <h2 class="text-orenGelap text-3xl font-extrabold">CandyVet</h2>
        </div>
        <ul class="space-y-3">
            <li>
                <a href="#" class="flex items-center gap-3 py-3 px-5 bg-orenGelap text-white font-semibold rounded-xl shadow-soft hover:bg-ungu transition-all">
                    <svg class="w-6 h-6 object-contain" fill="currentColor" viewBox="0 0 36 35">
                        <path d="M22 11.6667C21.4333 11.6667 20.9587 11.48 20.576 11.1067C20.1933 10.7333 20.0013 10.2718 20 9.72222V1.94444C20 1.39352 20.192 0.932037 20.576 0.56C20.96 0.187963 21.4347 0.0012963 22 0H34C34.5667 0 35.042 0.186667 35.426 0.56C35.81 0.933333 36.0013 1.39481 36 1.94444V9.72222C36 10.2731 35.808 10.7353 35.424 11.1086C35.04 11.4819 34.5653 11.668 34 11.6667H22ZM2 19.4444C1.43333 19.4444 0.958667 19.2578 0.576 18.8844C0.193334 18.5111 0.00133333 18.0496 0 17.5V1.94444C0 1.39352 0.192 0.932037 0.576 0.56C0.96 0.187963 1.43467 0.0012963 2 0H14C14.5667 0 15.042 0.186667 15.426 0.56C15.81 0.933333 16.0013 1.39481 16 1.94444V17.5C16 18.0509 15.808 18.5131 15.424 18.8864C15.04 19.2597 14.5653 19.4457 14 19.4444H2ZM22 35C21.4333 35 20.9587 34.8133 20.576 34.44C20.1933 34.0667 20.0013 33.6052 20 33.0555V17.5C20 16.9491 20.192 16.4876 20.576 16.1156C20.96 15.7435 21.4347 15.5568 22 15.5556H34C34.5667 15.5556 35.042 15.7422 35.426 16.1156C35.81 16.4889 36.0013 16.9504 36 17.5V33.0555C36 33.6065 35.808 34.0686 35.424 34.4419C35.04 34.8153 34.5653 35.0013 34 35H22ZM2 35C1.43333 35 0.958667 34.8133 0.576 34.44C0.193334 34.0667 0.00133333 33.6052 0 33.0555V25.2778C0 24.7268 0.192 24.2654 0.576 23.8933C0.96 23.5213 1.43467 23.3346 2 23.3333H14C14.5667 23.3333 15.042 23.52 15.426 23.8933C15.81 24.2667 16.0013 24.7281 16 25.2778V33.0555C16 33.6065 15.808 34.0686 15.424 34.4419C15.04 34.8153 14.5653 35.0013 14 35H2Z" fill="black"/>
                    </svg> 
                    Booking
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center gap-3 py-3 px-5 text-orenGelap hover:bg-white hover:shadow-soft font-semibold rounded-xl transition-all">
                    <svg class="w-6 h-6 object-contain" fill="currentColor" viewBox="0 0 40 36">
                        <path xmlns="http://www.w3.org/2000/svg" d="M23.5851 14.5148C23.2413 14.1711 22.7751 13.9781 22.289 13.9781C21.8028 13.9781 21.3366 14.1711 20.9928 14.5148L19.6966 15.8092C19.0051 16.4771 18.0789 16.8467 17.1175 16.8383C16.1561 16.83 15.2364 16.4443 14.5566 15.7645C13.8768 15.0847 13.4912 14.165 13.4828 13.2036C13.4745 12.2423 13.844 11.3161 14.512 10.6245L24.8336 0.299178C27.2773 -0.25664 29.834 -0.0278916 32.1403 0.952898C34.4465 1.93369 36.3848 3.61659 37.6796 5.76231C38.9743 7.90804 39.5597 10.4073 39.3524 12.9049C39.1451 15.4024 38.1556 17.771 36.5248 19.6738L32.6601 23.588L23.5851 14.5148ZM3.49179 3.49285C5.38833 1.59517 7.86976 0.393002 10.5345 0.0809156C13.1992 -0.231171 15.8912 0.365084 18.175 1.77318L11.9178 8.03218C10.5626 9.38468 9.79008 11.2136 9.76534 13.128C9.7406 15.0425 10.4656 16.8908 11.7854 18.2778C13.1052 19.6649 14.9151 20.4808 16.8285 20.5512C18.7418 20.6216 20.6068 19.9409 22.025 18.6545L22.289 18.4033L30.0678 26.1803L22.289 33.9592C21.6014 34.6466 20.6689 35.0327 19.6966 35.0327C18.7244 35.0327 17.7919 34.6466 17.1043 33.9592L3.48995 20.3448C1.25536 18.1101 0 15.0792 0 11.9188C0 8.75852 1.2572 5.72762 3.49179 3.49285Z" fill="black"/>
                    </svg> 
                    Layanan
                </a>
            </li>
            <li class="absolute bottom-8 left-8 right-8">
                <a href="#" onclick="confirmlogout()" class="flex items-center gap-3 py-3 px-5 text-orenGelap hover:bg-white hover:shadow-soft font-semibold rounded-xl transition-all">
                    <i class='bx bx-log-out text-2xl'></i> Keluar
                </a>
                <script>
                function confirmlogout() {
                    Swal.fire({
                        title: 'Yakin ingin keluar?',
                        text: "Anda akan logout dari sistem",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#F4631E',
                        cancelButtonColor: '#FAB12F',
                        confirmButtonText: 'Ya, Logout',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'logout.php';
                        }
                    });
                }
                </script>
            </li>
        </ul>
    </aside>

    <!-- Main -->
    <main class="flex-1 lg:ml-72 p-10">

        <!-- Header -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <h1 class="text-orenGelap text-4xl font-extrabold drop-shadow">Riwayat Booking</h1>
            <div class="relative w-full md:w-80">
                <input type="text" placeholder="Cari..." id="searchInput"
                    class="w-full py-3 pl-5 pr-12 rounded-full border border-gray-200 focus:outline-none focus:ring-2 focus:ring-orenGelap">
                <i class='bx bx-search absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 text-xl'></i>
            </div>
        </header>

        <!-- Alert -->
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

        <!-- Card -->
        <div class="bg-white p-8 rounded-3xl shadow-soft">

            <div class="text-center mb-8">
                <h2 class="text-orenGelap text-3xl font-bold relative inline-block pb-3">
                    <?php echo ucfirst($status_filter); ?> Bookings
                    <span class="absolute bottom-0 left-1/2 -translate-x-1/2 w-20 h-1 bg-ungu rounded-full"></span>
                </h2>
            </div>

            <!-- Tabs -->
            <div class="flex gap-3 mb-6 flex-wrap">
                <?php
                $tabs = ['semua'=>'Semua','Aktif'=>'Aktif','Selesai'=>'Selesai','Dibatalkan'=>'Dibatalkan'];
                foreach ($tabs as $key=>$label) {
                    $active = ($status_filter == $key) ? 'bg-orenGelap text-white' : 'bg-gray-100 text-gray-600';
                    echo "<a href='admin.php?status=$key' class='px-5 py-2 font-semibold rounded-xl transition hover:scale-[1.02] hover:bg-orenTerang hover:text-white $active'>$label</a>";
                }
                ?>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full border-separate table-spacing">
                    <thead>
                        <tr>
                            <?php
                            $headers = ['No.', 'Nama Majikan', 'Nama Hewan', 'Jenis Hewan', 'Usia', 'Status', 'Aksi'];
                            foreach($headers as $i => $h){
                                $rounded = ($i==0) ? 'rounded-l-xl' : (($i==count($headers)-1)?'rounded-r-xl':'');
                                echo "<th class='p-4 text-left font-bold text-sm uppercase text-white bg-orenGelap $rounded'>$h</th>";
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if(mysqli_num_rows($result) > 0){
                            $no = 1;
                            while($row = mysqli_fetch_assoc($result)){
                                $badge_class = 'bg-green-100 text-green-700';
                                if($row['status'] == 'Selesai') $badge_class = 'bg-blue-100 text-blue-700';
                                elseif($row['status'] == 'Dibatalkan') $badge_class = 'bg-red-100 text-red-700';
                                echo "<tr class='bg-krem hover:bg-orange-100 transition'>";
                                echo "<td class='p-4 font-semibold'>$no</td>";
                                echo "<td class='p-4 font-semibold text-gray-800'>".htmlspecialchars($row['nm_majikan'])."</td>";
                                echo "<td class='p-4'>".htmlspecialchars($row['nm_hewan'])."</td>";
                                echo "<td class='p-4'>".htmlspecialchars($row['jenis_hewan'])."</td>";
                                echo "<td class='p-4'>".htmlspecialchars($row['usia_hewan'])." tahun</td>";
                                echo "<td class='p-4'><span class='px-4 py-1.5 rounded-full text-xs font-bold $badge_class'>".htmlspecialchars($row['status'])."</span></td>";
                                echo "<td class='p-4'>
                                    <div class='flex gap-2'>
                                        <a href='detail_booking.php?id={$row['id']}' class='w-9 h-9 flex items-center justify-center bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-600 hover:text-white transition'><i class=\"bx bx-file text-lg\"></i></a>
                                        <a href='edit_booking.php?id={$row['id']}' class='w-9 h-9 flex items-center justify-center bg-orenTerang text-white rounded-lg hover:bg-orenGelap transition'><i class=\"bx bx-pencil text-lg\"></i></a>";
                                
                                if($row['status']=='Aktif'){
                                    echo "
                                        <a href='admin.php?selesai={$row['id']}' onclick=\"return confirm('Tandai selesai?')\" class='w-9 h-9 flex items-center justify-center bg-green-100 text-green-700 rounded-lg hover:bg-green-700 hover:text-white transition'><i class=\"bx bx-check-circle\"></i></a>
                                        <a href='admin.php?batalkan={$row['id']}' onclick=\"return confirm('Batalkan booking?')\" class='w-9 h-9 flex items-center justify-center bg-red-100 text-red-700 rounded-lg hover:bg-red-700 hover:text-white transition'><i class=\"bx bx-x-circle\"></i></a>
                                    ";
                                } else {
                                    echo "
                                        <a href='admin.php?aktifkan={$row['id']}' onclick=\"return confirm('Aktifkan kembali?')\" class='w-9 h-9 flex items-center justify-center bg-ungu text-white rounded-lg hover:opacity-90 transition'><i class=\"bx bx-undo\"></i></a>
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
            </div>
        </div>

        <!-- FAB Button -->
        <a href="booking-admin.php" class="fixed bottom-10 right-10">
            <button class="flex items-center gap-2 bg-ungu text-white px-6 py-4 rounded-full font-bold shadow-soft hover:-translate-y-1 hover:shadow-lg transition">
                <i class='bx bx-plus text-2xl'></i> Tambah Booking Baru
            </button>
        </a>

    </main>

    <script src="admin.js"></script>

</body>
</html>
<?php if(isset($conn)) mysqli_close($conn); ?>
