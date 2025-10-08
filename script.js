        const hamburgerBtn = document.getElementById('hamburger-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const hamburgerIcon = document.getElementById('hamburger-icon');
        const closeIcon = document.getElementById('close-icon');

        hamburgerBtn.addEventListener('click', () => {
            hamburgerIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
            

            if (mobileMenu.classList.contains('max-h-0')) {
                mobileMenu.classList.remove('max-h-0');
                mobileMenu.classList.add('max-h-96');
            } else {
                mobileMenu.classList.remove('max-h-96');
                mobileMenu.classList.add('max-h-0');
            }
        });