    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
        });
    });