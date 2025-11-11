function showFullUlasan(ulasanId) {
    const fullText = fullUlasanData[ulasanId];
    if (fullText) {
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
    }
}

document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
            
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchValue) ? '' : 'none';
    });
});