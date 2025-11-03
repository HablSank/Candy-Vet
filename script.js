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


document.addEventListener('DOMContentLoaded', (event) => {
    
    const swiper = new Swiper('.mySwiper', {
        effect: 'creative',
        
        grabCursor: true,
        centeredSlides: true,
        slidesPerView: 'auto',
        loop: true,
        
        creativeEffect: {
            prev: {
                translate: ['-120%', 0, -500],
                scale: 0.85,
                opacity: 0.7
            },
            next: {
                translate: ['120%', 0, -500],
                scale: 0.85,
                opacity: 0.7
            },
        },

        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },

        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
});