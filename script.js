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
    // DIUBAH: Hentikan event agar tidak 'bocor' ke document. Ini solusinya.
    event.stopPropagation(); 
    toggleMenu();
});

// Saat salah satu link di menu diklik, panggil fungsi toggleMenu HANYA JIKA menu sedang terbuka
mobileMenuLinks.forEach(link => {
    link.addEventListener('click', () => {
        // Cek dulu apakah menu sedang terbuka, baru ditutup
        if (!mobileMenu.classList.contains('max-h-0')) {
            toggleMenu();
        }
    });
});

document.addEventListener('click', (event) => {
    // Cek apakah menu sedang terbuka
    const isMenuOpen = !mobileMenu.classList.contains('max-h-0');
    // Cek apakah target klik BUKAN bagian dari menu itu sendiri
    const isClickInsideMenu = mobileMenu.contains(event.target);

    // Jika menu sedang terbuka DAN klik terjadi di luar area menu
    if (isMenuOpen && !isClickInsideMenu) {
        toggleMenu(); // Panggil fungsi toggle untuk menutup menu
    }
});