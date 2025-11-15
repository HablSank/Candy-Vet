window.showFullUlasan = function(ulasanId) {
    const fullText = (typeof fullUlasanData !== 'undefined') ? fullUlasanData[ulasanId] : null;
    if (!fullText) return;
    
    if (typeof Swal === 'undefined') {
        alert(fullText);
        return;
    }
    
    Swal.fire({
        title: 'Isi Ulasan Lengkap',
        html: `<div style="text-align: left; white-space: pre-wrap; word-break: break-word; max-height: 400px; overflow-y: auto; padding: 16px; border: 1px solid #e5e7eb; background-color: #f3f4f6; border-radius: 8px; font-size: 14px; color: #374151;">${fullText}</div>`,
        icon: 'info',
        showCloseButton: true,
        showConfirmButton: false,
        customClass: {
            popup: 'w-11/12 md:w-2/3 lg:w-1/2'
        }
    });
};


window.confirmApproveUlasan = function(id, majikanName, redirectParams) {
    if (typeof Swal === 'undefined') {
        if (confirm(`Setujui ulasan dari ${majikanName}?`)) {
            window.location.href = `ulasan-admin?action=approve&id=${id}${redirectParams}`;
        }
        return false;
    }
    
    Swal.fire({
        title: 'Setujui Ulasan?',
        text: `Ulasan dari ${majikanName} akan diterima dan ditampilkan.`,
        icon: 'success',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#FAB12F',
        confirmButtonText: 'Ya, Setujui',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'swal2-confirm',
            cancelButton: 'swal2-cancel'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `ulasan-admin?action=approve&id=${id}${redirectParams}`;
        }
    });
    return false;
};


window.confirmRejectUlasan = function(id, majikanName, redirectParams) {
    if (typeof Swal === 'undefined') {
        if (confirm(`Tolak ulasan dari ${majikanName}?`)) {
            window.location.href = `ulasan-admin?action=reject&id=${id}${redirectParams}`;
        }
        return false;
    }
    
    Swal.fire({
        title: 'Tolak Ulasan?',
        text: `Ulasan dari ${majikanName} akan ditolak. Anda dapat mengubahnya kembali nanti.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#FAB12F',
        confirmButtonText: 'Ya, Tolak',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'swal2-confirm',
            cancelButton: 'swal2-cancel'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `ulasan-admin?action=reject&id=${id}${redirectParams}`;
        }
    });
    return false;
};


document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr, .mobile-search-row');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });
    }
});

