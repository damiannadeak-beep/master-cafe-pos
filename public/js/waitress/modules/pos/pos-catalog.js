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

    // --- LOGIKA PENCARIAN & FILTER (DEBOUNCED 2-TIER) ---
    let searchTimeout = null;
    let posMainCat = 'semua';
    let posSubCat = 'semua';

    function renderPosSubCategoryPills() {
        const wrapper = document.getElementById('pos-sub-category-wrapper');
        const container = document.getElementById('pos-sub-category-pills');
        if (!container) return;

        const data = window.posSubCategoryData || {};
        let subs = [];

        if (posMainCat === 'makanan') {
            subs = data.makanan || [];
            if (wrapper) wrapper.style.setProperty('display', 'block', 'important');
        } else if (posMainCat === 'minuman') {
            subs = data.minuman || [];
            if (wrapper) wrapper.style.setProperty('display', 'block', 'important');
        } else {
            if (wrapper) wrapper.style.setProperty('display', 'none', 'important');
            container.innerHTML = '';
            return;
        }

        let html = `
            <button type="button" class="btn btn-sm btn-pos-sub-cat active rounded-pill px-3 py-1 flex-shrink-0" 
                    onclick="filterPosSubCategory('semua', this)"
                    style="background-color: rgba(192, 142, 92, 0.25); color: #e2a873; border: 1px solid rgba(192, 142, 92, 0.7); font-size: 0.78rem; font-weight: 600;">
                Semua ${posMainCat === 'makanan' ? 'Makanan' : 'Minuman'}
            </button>
        `;

        subs.forEach(sub => {
            const safeSub = sub.replace(/'/g, "\\'");
            html += `
                <button type="button" class="btn btn-sm btn-pos-sub-cat rounded-pill px-3 py-1 flex-shrink-0" 
                        onclick="filterPosSubCategory('${safeSub}', this)"
                        style="background-color: rgba(255,255,255,0.06); color: #cbd5e1; border: 1px solid rgba(255,255,255,0.15); font-size: 0.78rem;">
                    ${sub}
                </button>
            `;
        });

        container.innerHTML = html;
        container.scrollLeft = 0;
    }

    window.filterPosMainCategory = function(mainCat, btnElement) {
        posMainCat = (mainCat || 'semua').toLowerCase();
        posSubCat = 'semua';

        document.querySelectorAll('.btn-pos-main-cat').forEach(btn => btn.classList.remove('active', 'bg-transparent', 'shadow-sm'));
        if (btnElement) btnElement.classList.add('active', 'bg-transparent', 'shadow-sm');

        const searchInp = document.getElementById('searchInput');
        if (searchInp) searchInp.value = '';

        renderPosSubCategoryPills();
        applyPosFilter();
    };

    window.filterPosSubCategory = function(subCat, btnElement) {
        posSubCat = (subCat || 'semua').toLowerCase();

        document.querySelectorAll('.btn-pos-sub-cat').forEach(b => {
            b.classList.remove('active');
            b.style.backgroundColor = 'rgba(255,255,255,0.05)';
            b.style.color = '#e2e8f0';
            b.style.borderColor = 'rgba(255,255,255,0.15)';
        });

        if (btnElement) {
            btnElement.classList.add('active');
            btnElement.style.backgroundColor = 'rgba(192, 142, 92, 0.2)';
            btnElement.style.color = '#c08e5c';
            btnElement.style.borderColor = 'rgba(192, 142, 92, 0.6)';
        }

        applyPosFilter();
    };

    function applyPosFilter() {
        const keyword = (document.getElementById('searchInput')?.value || '').toLowerCase().trim();

        filteredMenus = allMenus.filter(menu => {
            const mCat = (menu.kategori || '').toLowerCase();
            const mSub = (menu.sub_kategori || '').toLowerCase();
            const mName = (menu.nama_menu || '').toLowerCase();

            const matchMain = (posMainCat === 'semua' || mCat === posMainCat);
            const matchSub = (posSubCat === 'semua' || mSub === posSubCat);
            const matchKeyword = (keyword === '' || mName.includes(keyword));

            return matchMain && matchSub && matchKeyword;
        });

        currentPage = 1;
        renderMenus();
    }

    window.filterCategory = function(category, btnElement) {
        window.filterPosMainCategory(category, btnElement);
    };

    window.searchMenu = function(keyword) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyPosFilter();
        }, 120);
    };

    function initPosSubPillsWheel() {
        renderPosSubCategoryPills();
        const posScrollContainer = document.getElementById('pos-sub-category-pills');
        if (posScrollContainer) {
            posScrollContainer.addEventListener('wheel', function(e) {
                if (e.deltaY !== 0) {
                    e.preventDefault();
                    posScrollContainer.scrollLeft += e.deltaY;
                }
            }, { passive: false });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPosSubPillsWheel);
    } else {
        initPosSubPillsWheel();
    }

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

            const displayCat = menu.sub_kategori || menu.kategori;
            const badgeCat = `<span class="badge position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill shadow-sm bg-info text-white text-capitalize" style="backdrop-filter: blur(4px);">${displayCat}</span>`;

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
