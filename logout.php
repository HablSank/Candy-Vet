<?php
session_start();
session_destroy();

?>


<script type="text/javascript">
    alert('Berhasil keluar');
    location.href = "login.php";
</script>