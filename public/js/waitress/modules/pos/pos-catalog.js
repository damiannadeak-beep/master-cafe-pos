/**
 * Master Cafe POS - POS Module: Menu Catalog & Navigation
 * File: public/js/waitress/modules/pos/pos-catalog.js
 * Deskripsi: Menangani pencarian menu (debounced), filter kategori, paginasi produk,
 *            dan toggle mode nomor meja (Dine-in vs Takeaway).
 */

(function () {
    'use strict';

    function getDefaultLogo() {
        return window.defaultLogoUrl || '/images/logo.png';
    }

    const allMenus = window.allMenus || [];
    let filteredMenus = [...allMenus];
    let currentPage = 1;
    const itemsPerPage = 10;

    // --- LOGIKA TOGGLE MEJA ---
    window.toggleMeja = function () {
        const tipePesanan = document.querySelector('select[name="tipe_pesanan"]')?.value;
        const wrapperMeja = document.getElementById('wrapper-nomor-meja');
        const infoTakeaway = document.getElementById('info-takeaway-badge');
        const mejaSelect = document.querySelector('select[name="id_meja"]');

        if (tipePesanan === 'takeaway') {
            if (wrapperMeja) wrapperMeja.classList.add('d-none');
            if (infoTakeaway) infoTakeaway.classList.remove('d-none');
            if (mejaSelect) {
                mejaSelect.disabled = true;
                mejaSelect.removeAttribute('required');
            }
        } else {
            if (wrapperMeja) wrapperMeja.classList.remove('d-none');
            if (infoTakeaway) infoTakeaway.classList.add('d-none');
            if (mejaSelect) {
                mejaSelect.disabled = false;
                mejaSelect.setAttribute('required', 'required');
            }
        }
    };

    // --- LOGIKA PENCARIAN & FILTER (DEBOUNCED) ---
    let searchTimeout = null;
    window.searchMenu = function (keyword) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            keyword = (keyword || '').toLowerCase().trim();
            const activeBtn = document.querySelector('.btn-soft.active');
            const currentCatBtn = activeBtn ? activeBtn.innerText.toLowerCase().trim() : 'semua';
            const baseMenus = currentCatBtn === 'semua' ? allMenus : allMenus.filter(m => (m.kategori || '').toLowerCase() === currentCatBtn);

            filteredMenus = baseMenus.filter(menu => (menu.nama_menu || '').toLowerCase().includes(keyword));
            currentPage = 1;
            renderMenus();
        }, 120);
    };

    window.filterCategory = function (category, btnElement) {
        document.querySelectorAll('.btn-soft').forEach(btn => btn.classList.remove('active', 'bg-transparent', 'shadow-sm'));
        btnElement.classList.add('active', 'bg-transparent', 'shadow-sm');

        // Bersihkan kotak pencarian saat pindah kategori
        const searchInp = document.getElementById('searchInput');
        if (searchInp) searchInp.value = '';

        if (category === 'semua') {
            filteredMenus = [...allMenus];
        } else {
            filteredMenus = allMenus.filter(menu => (menu.kategori || '').toLowerCase() === category.toLowerCase());
        }
        currentPage = 1;
        renderMenus();
    };

    window.changePage = function (direction) {
        const maxPage = Math.ceil(filteredMenus.length / itemsPerPage) || 1;
        currentPage += direction;
        if (currentPage < 1) currentPage = 1;
        if (currentPage > maxPage) currentPage = maxPage;
        renderMenus();
    };

    window.renderMenus = function () {
        const container = document.getElementById('menu-container');
        if (!container) return;

        const maxPage = Math.ceil(filteredMenus.length / itemsPerPage) || 1;

        // Update Pagination Controls
        const pageInfo = document.getElementById('pageInfo');
        if (pageInfo) pageInfo.innerText = `Halaman ${currentPage} / ${maxPage}`;

        const prevBtn = document.getElementById('prevPage');
        if (prevBtn) prevBtn.disabled = currentPage === 1;

        const nextBtn = document.getElementById('nextPage');
        if (nextBtn) nextBtn.disabled = currentPage === maxPage;

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const menusToShow = filteredMenus.slice(startIndex, endIndex);

        if (menusToShow.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center text-white-50 py-5 mt-4">
                    <i class="bi bi-search" style="font-size: 3rem; opacity: 0.3;"></i>
                    <h5 class="mt-3">Produk tidak ditemukan</h5>
                    <p class="small">Coba gunakan kata kunci lain.</p>
                </div>`;
            return;
        }

        const defaultLogo = getDefaultLogo();

        const cardsHtml = menusToShow.map(menu => {
            const imageHtml = menu.image
                ? `<div class="bg-transparent text-center border-bottom" style="height: 150px;">
                       <img src="${menu.image_url}" onerror="this.onerror=null; this.src='${defaultLogo}';" alt="${menu.nama_menu}" style="object-fit: contain; width: 100%; height: 100%;">
                     </div>`
                : `<div class="card-img-top d-flex justify-content-center align-items-center border-bottom" style="height: 150px; background-color: #14171c;">
                         <img src="${defaultLogo}" alt="Master Cafe" class="rounded-circle shadow-sm" style="height: 64px; width: 64px; object-fit: cover;">
                     </div>`;

            const badgeCat = `<span class="badge position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill shadow-sm bg-info text-white text-capitalize" style="backdrop-filter: blur(4px);">${menu.kategori}</span>`;

            const isHabis = !menu.is_available;
            const disabledStyle = isHabis ? 'opacity: 0.6; filter: grayscale(80%); pointer-events: none;' : 'cursor: pointer;';
            const habisOverlay = isHabis ? `<div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" style="background: rgba(0,0,0,0.5); z-index: 5;"><h4 class="text-white fw-bold border border-2 border-white p-2 rounded">HABIS</h4></div>` : '';

            return `
                <div class="col-6 col-sm-6 col-md-4 col-lg-3">
                    <div class="menu-card card h-100 position-relative overflow-hidden ${!isHabis ? 'hover-lift' : ''}" 
                         onclick="${!isHabis ? `openVariantModal(${menu.id})` : ''}"
                         style="${disabledStyle}">
                         ${habisOverlay}
                         ${badgeCat}
                         ${imageHtml}
                         <div class="card-body text-center p-2 p-md-3">
                             <h6 class="fw-bold mb-1 text-truncate" style="font-size: 0.9rem;" title="${menu.nama_menu}">${menu.nama_menu}</h6>
                             <p class="price mb-0 fw-bold text-primary font-sans" style="font-size: 0.9rem;">${(menu.is_dynamic_price || Number(menu.harga) === 0) ? 'Sesuai Timbangan' : 'Rp ' + parseFloat(menu.harga).toLocaleString('id-ID')}</p>
                         </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = cardsHtml.join('');
    };

})();
