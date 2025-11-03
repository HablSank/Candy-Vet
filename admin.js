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
                    window.location.href = 'logout';
                }
            });
        }    
        
        
        function confirmSelesai(id, params) {
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
                    window.location.href = `admin?selesai=${id}${params}`;
                }
            });
        }

        function confirmBatalkan(id, params) {
            Swal.fire({
                title: 'Batalkan Booking?',
                text: "Anda yakin ingin membatalkan booking ini? Status dapat diaktifkan kembali.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'swal2-confirm-red',
                    cancelButton: 'swal2-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) {

                    window.location.href = `admin?batalkan=${id}${params}`;
                }
            });
        }

        function confirmAktifkan(id, params) {
            Swal.fire({
                title: 'Aktifkan Kembali?',
                text: "Status booking akan diubah kembali menjadi Aktif.",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#9E00BA',
                confirmButtonText: 'Ya, Aktifkan',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'swal2-confirm',
                    cancelButton: 'swal2-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) {

                    window.location.href = `admin?aktifkan=${id}${params}`;
                }
            });
        }

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
        });