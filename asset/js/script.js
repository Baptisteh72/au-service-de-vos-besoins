 /* --- A. Carrousel automatique --- */

       const track = document.getElementById('track');
if (track) {
    const slides = track.querySelectorAll('img');
    let index = 0;

    setInterval(() => {
        index++;
        if (index >= slides.length) index = 0;
        track.style.transform = `translateX(-${index * 100}%)`;
    }, 3000);
}

/* --- B. Menu déroulant --- */
const floatingBtn = document.getElementById('floatingBtn');
const floatingMenu = document.getElementById('floatingMenu');

if (floatingBtn && floatingMenu) {
    const btnIcon = floatingBtn.querySelector('.btn-icon');
    const btnText = floatingBtn.querySelector('.btn-text');

    function closeMenu() {
        floatingMenu.classList.remove('open');
        floatingBtn.classList.remove('active');
        floatingBtn.setAttribute('aria-expanded', 'false');
        btnIcon.textContent = '☰';
        btnText.textContent = 'Menu';
    }

    floatingBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = floatingMenu.classList.toggle('open');
        floatingBtn.classList.toggle('active');
        floatingBtn.setAttribute('aria-expanded', isOpen);

        if (isOpen) {
            btnIcon.textContent = '✕';
            btnText.textContent = 'Fermer';
        } else {
            btnIcon.textContent = '☰';
            btnText.textContent = 'Menu';
        }
    });

    document.querySelectorAll('.floating-link').forEach(link => {
        link.addEventListener('click', () => closeMenu());
    });

    document.addEventListener('click', (e) => {
        if (!floatingMenu.contains(e.target) && !floatingBtn.contains(e.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && floatingMenu.classList.contains('open')) {
            closeMenu();
        }
    });
}

/* --- C. Loupe interactive & Lightbox plein écran --- */
const zoomContainer = document.getElementById('zoomContainer');
const zoomImg = document.getElementById('zoomImg');
const loupeLens = document.getElementById('loupeLens');
const lightboxModal = document.getElementById('lightboxModal');
const lightboxImg = document.getElementById('lightboxImg');
const lightboxClose = document.getElementById('lightboxClose');

if (zoomContainer && zoomImg && loupeLens) {
    const zoomLevel = 2.2;

    zoomContainer.addEventListener('mousemove', (e) => {
        if (window.innerWidth <= 768) return;

        loupeLens.style.display = 'block';
        const rect = zoomImg.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const lensHalfW = loupeLens.offsetWidth / 2;
        const lensHalfH = loupeLens.offsetHeight / 2;

        loupeLens.style.left = `${x - lensHalfW}px`;
        loupeLens.style.top = `${y - lensHalfH}px`;
        loupeLens.style.backgroundImage = `url('${zoomImg.src}')`;
        loupeLens.style.backgroundSize = `${zoomImg.offsetWidth * zoomLevel}px ${zoomImg.offsetHeight * zoomLevel}px`;

        const bgX = (x * zoomLevel) - lensHalfW;
        const bgY = (y * zoomLevel) - lensHalfH;
        loupeLens.style.backgroundPosition = `-${bgX}px -${bgY}px`;
    });

    zoomContainer.addEventListener('mouseleave', () => {
        loupeLens.style.display = 'none';
    });

    if (lightboxModal && lightboxImg) {
        zoomContainer.addEventListener('click', () => {
            lightboxImg.src = zoomImg.src;
            lightboxModal.classList.add('active');
        });
    }
}

// Fermeture de la Lightbox (clic sur la croix, clic en dehors ou touche Échap)
if (lightboxClose && lightboxModal) {
    lightboxClose.addEventListener('click', () => {
        lightboxModal.classList.remove('active');
    });

    lightboxModal.addEventListener('click', (e) => {
        if (e.target === lightboxModal) {
            lightboxModal.classList.remove('active');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lightboxModal.classList.contains('active')) {
            lightboxModal.classList.remove('active');
        }
    });
}