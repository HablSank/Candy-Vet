const hamburgerBtn = document.getElementById('hamburger-btn');
const mobileMenu = document.getElementById('mobile-menu');
const hamburgerIcon = document.getElementById('hamburger-icon');
const closeIcon = document.getElementById('close-icon');
const mobileMenuLinks = document.querySelectorAll('#mobile-menu a');


function toggleMenu() {
    mobileMenu.classList.toggle('max-h-0');
    mobileMenu.classList.toggle('max-h-96');
    hamburgerIcon.classList.toggle('hidden');
    closeIcon.classList.toggle('hidden');
}

hamburgerBtn.addEventListener('click', (event) => {
    event.stopPropagation(); 
    toggleMenu();
});

mobileMenuLinks.forEach(link => {
    link.addEventListener('click', () => {
        if (!mobileMenu.classList.contains('max-h-0')) {
            toggleMenu();
        }
    });
});

document.addEventListener('click', (event) => {
    const isMenuOpen = !mobileMenu.classList.contains('max-h-0');
    const isClickInsideMenu = mobileMenu.contains(event.target);

    if (isMenuOpen && !isClickInsideMenu) {
        toggleMenu();
    }
});

function toggleInput(select) {
    const input = document.getElementById('hewan_lainnya');
    if (select.value === 'Lainnya') {
        input.classList.remove('hidden');
        input.required = true;
    } else {
        input.classList.add('hidden');
        input.required = false;
        input.value = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const selectHewan = document.getElementById('jenis_hewan');
    if (selectHewan) {
            toggleInput(selectHewan);
            }
});
    

        
function confirmreset() {
    Swal.fire({
        title: 'Reset Form?',
        text: "Semua data yang sudah diisi akan hilang",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#F4631E',
        cancelButtonColor: '#FAB12F',
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.querySelector('form').reset();
            Swal.fire({
                icon: 'success',
                title: 'Form direset!',
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}