// ============ MODAL DETAIL FUNCTIONS ============

function printDetail() {
    window.print();
}

function hideDetailModal() {
    document.body.classList.remove('modal-open');
    document.getElementById('modalOverlay').classList.add('hidden');
    document.getElementById('detailModal').classList.add('hidden');
}

async function showDetailModal(id) {
    document.body.classList.add('modal-open');
    document.getElementById('modalOverlay').classList.remove('hidden');
    document.getElementById('detailModal').classList.remove('hidden');
// Set loading state
    document.getElementById('modalNamaMajikan').textContent = 'Memuat...';
    document.getElementById('modalEmail').textContent = 'Memuat...';
    document.getElementById('modalTelepon').textContent = 'Memuat...';
    document.getElementById('modalTanggalBooking').textContent = 'Memuat...';
    document.getElementById('modalNamaHewan').textContent = 'Memuat...';
    document.getElementById('modalJenisHewan').textContent = 'Memuat...';
    document.getElementById('modalJenisKelamin').textContent = 'Memuat...';
    document.getElementById('modalUsiaHewan').textContent = 'Memuat...';
    document.getElementById('modalKeluhan').textContent = 'Memuat...';
    document.getElementById('modalBookingId').textContent = `Booking ID: ${id}`;
    
    try {
        // Fetch data dari server
        const response = await fetch(`get_booking_detail.php?id=${id}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        // Debug log
        console.log('Data yang diterima:', data);
        
        // Set semua field
        document.getElementById('modalNamaMajikan').textContent = data.nm_majikan || '-';
        document.getElementById('modalEmail').textContent = data.email_majikan || '-';
        document.getElementById('modalTelepon').textContent = data.no_tlp_majikan || '-';
        document.getElementById('modalTanggalBooking').textContent = data.tanggal_booking_formatted || '-';
        document.getElementById('modalNamaHewan').textContent = data.nm_hewan || '-';
        document.getElementById('modalJenisHewan').textContent = data.jenis_hewan || '-';
        document.getElementById('modalJenisKelamin').textContent = data.jenis_kelamin_hewan || '-';
        document.getElementById('modalUsiaHewan').textContent = data.usia_hewan_formatted || '-';
        document.getElementById('modalKeluhan').textContent = data.keluhan || '-';

    } catch (error) {
        console.error('Error:', error);
        document.getElementById('modalNamaMajikan').textContent = 'Gagal memuat data';
        document.getElementById('modalEmail').textContent = '-';
        document.getElementById('modalTelepon').textContent = '-';
        document.getElementById('modalNamaHewan').textContent = '-';
        document.getElementById('modalJenisHewan').textContent = '-';
        document.getElementById('modalJenisKelamin').textContent = '-';
        document.getElementById('modalUsiaHewan').textContent = '-';
        document.getElementById('modalKeluhan').textContent = 'Error: ' + error.message;
    }
}

// ============ KONFIRMASI ACTIONS ============

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

function confirmlogout() {
    Swal.fire({
        title: 'Yakin ingin keluar?',
        text: "Anda akan logout dari sistem",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#FA812F',
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

// ============ SEARCH FUNCTIONALITY ============

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });
    }
});