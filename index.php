<?php
include 'koneksi.php';

if(isset($_POST['submit'])) {
    $stmt = $conn->prepare("INSERT INTO tb_form (nm_majikan, email_majikan, no_tlp_majikan, nm_hewan, jenis_hewan, usia_hewan, jenis_kelamin_hewan, keluhan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssssssss", 
        $_POST['nm_majikan'],
        $_POST['email_majikan'],
        $_POST['no_tlp_majikan'],
        $_POST['nm_hewan'],
        $_POST['jenis_hewan'],
        $_POST['usia_hewan'],
        $_POST['jenis_kelamin_hewan'],
        $_POST['keluhan']
    );
    
    if($stmt->execute()){
        echo '<script>alert("Data Berhasil Disimpan"); window.location.href="index.php";</script>';
    } else {
        echo '<script>alert("Data Gagal Disimpan");</script>';
    }
    
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>db_form_booking</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=BBH+Sans+Bogle&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..1000&family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
</head>
<body>
   
    <!--bagian box formulir-->
    <section class="box-formulir">
        <h2>Formulir Booking CandyVet Kebraon</h2>

       <!--bagian form-->
        <form action="index.php" method="POST">
            <div class="box">
                <table border="1" class="table-form">
                    <tr>
                        <td>Nama Majikan</td>
                        <td>:</td>
                        <td><input type="text" name="nm_majikan" placeholder="Masukkan Nama Lengkap Anda" required></td>
                    </tr>
                    <tr>
                        <td>Email Majikan</td>
                        <td>:</td>
                        <td><input type="email" name="email_majikan" placeholder="Masukkan Email Anda" required></td>
                    </tr>
                    <tr>
                        <td>No. Telepon</td>
                        <td>:</td>
                        <td><input type="text" name="no_tlp_majikan" placeholder="Masukkan No. Telepon Anda" required></td>
                    </tr>
                    <tr>
                        <td>Nama Hewan</td>
                        <td>:</td>
                        <td><input type="text" name="nm_hewan" placeholder="Masukkan Nama Hewan" required></td>
                    </tr>
                    <tr>
                        <td>Jenis Hewan</td>
                        <td>:</td>
                        <td><input type="text" name="jenis_hewan" placeholder="Contoh: Kucing, Anjing, Kelinci" required></td>
                    </tr>
                    <tr>
                        <td>Usia Hewan</td>
                        <td>:</td>
                        <td><input type="number" name="usia_hewan" placeholder="Masukkan Usia Hewan (tahun)" required></td>
                    </tr>
                    <tr>
                        <td>Jenis Kelamin Hewan</td>
                        <td>:</td>
                        <td>
                            <label>
                                <input type="radio" name="jenis_kelamin_hewan" value="Jantan" required> Jantan
                            </label>
                            <label>
                                <input type="radio" name="jenis_kelamin_hewan" value="Betina" required> Betina
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td>Keluhan</td>
                        <td>:</td>
                        <td><textarea name="keluhan" placeholder="Masukkan Keluhan Hewan Anda" rows="4" required></textarea></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <button type="submit" name="submit">Submit</button>
                            <button type="reset" name="reset">Reset</button>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
    </section>

</body>
</html>