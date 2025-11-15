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

function toggleInput(selectElement) {
    const hewan_lainnya_input = document.getElementById('hewan_lainnya');
    const selectedValue = selectElement.value;
    
    console.log('Selected value:', selectedValue);
    
    if (selectedValue === 'Lainnya') {
        hewan_lainnya_input.classList.remove('hidden');
        hewan_lainnya_input.setAttribute('required', 'required');
    } else {
        hewan_lainnya_input.classList.add('hidden');
        hewan_lainnya_input.removeAttribute('required');
        hewan_lainnya_input.value = ''; 
    }
}

function confirmreset() {
    Swal.fire({
        title: 'Reset Form?',
        text: "Semua data akan dihapus, lanjutkan?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#FA812F',
        cancelButtonColor: '#FAB12F',
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.querySelectorAll('form')[0].reset();
            
            const jenis_hewan_select = document.getElementById('jenis_hewan');
            if (jenis_hewan_select) {
                toggleInput(jenis_hewan_select);
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const jenis_hewan = document.getElementById('jenis_hewan');
            if (!jenis_hewan || !jenis_hewan.value) {
                e.preventDefault();
                alert('Pilih jenis hewan terlebih dahulu!');
                return false;
            }
            
            if (jenis_hewan.value === 'Lainnya') {
                const hewan_lainnya = document.getElementById('hewan_lainnya');
                if (!hewan_lainnya || !hewan_lainnya.value.trim()) {
                    e.preventDefault();
                    alert('Masukkan jenis hewan lainnya!');
                    return false;
                }
            }
            
            const keluhan = document.getElementById('keluhan');
            if (!keluhan || !keluhan.value.trim()) {
                e.preventDefault();
                alert('Keluhan tidak boleh kosong!');
                return false;
            }
            
            const jenis_kelamin = document.querySelector('input[name="jenis_kelamin_hewan"]:checked');
            if (!jenis_kelamin) {
                e.preventDefault();
                alert('Pilih jenis kelamin!');
                return false;
            }
            
            console.log('Form akan disubmit dengan data:');
            console.log('- Jenis Hewan:', jenis_hewan.value);
            console.log('- Keluhan:', keluhan.value);
            console.log('- Jenis Kelamin:', jenis_kelamin.value);
            
        });
    }
});

function logFormData() {
    const form = document.querySelector('form');
    const formData = new FormData(form);
    
    console.log('===== FORM DATA DEBUG =====');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }
    console.log('===== END DEBUG =====');
}
