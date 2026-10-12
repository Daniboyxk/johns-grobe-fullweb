/* ==========================================================================
   JOHN'S GROBE — Marina Port Premià
   Lògica Interactiva:
   1. Animació de Fotogrames per Scroll (Canvas Scrubbing)
   2. Model 3D Three.js Burger
   3. Carta Gastronòmica & Filtres
   4. Modal de Reserves & Notificacions
   ========================================================================== */

document.addEventListener('DOMContentLoaded', () => {

  // --- 0. MOTOR DE DESPLAÇAMENT SUAU GLOBAL (LENIS SMOOTH SCROLL) ---
  let lenis = null;
  if (typeof Lenis !== 'undefined') {
    const isFirefox = typeof navigator !== 'undefined' && navigator.userAgent.toLowerCase().includes('firefox');
    lenis = new Lenis({
      duration: isFirefox ? 0.7 : 1.05,
      easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
      direction: 'vertical',
      gestureDirection: 'vertical',
      smoothWheel: !isFirefox, // En Firefox, usem el motor natiu asíncron APZ per a màxima fluïdesa
      smoothTouch: false, // Manté el gest tàctil 100% natiu a 120Hz ProMotion a mòbils
      touchMultiplier: 1.5,
    });

    function lenisRaf(time) {
      lenis.raf(time);
      requestAnimationFrame(lenisRaf);
    }
    requestAnimationFrame(lenisRaf);

    window.appLenis = lenis;
  }

  // --- 1. BASE DE DADES OFICIAL DE LA CARTA (CARREGADA DES DE assets/js/data/dishes.js) ---
  const DISHES_CATALOG = (typeof window !== 'undefined' && Array.isArray(window.DISHES_CATALOG) && window.DISHES_CATALOG.length > 0)
    ? window.DISHES_CATALOG
    : [];

  // --- 2. RENDERITZAT, ORDENACIÓ I FILTRAT DE LA CARTA ---
  let activeCategory = 'all';
  let activeDietFilter = null;
  let activeSort = 'featured';
  let searchQuery = '';
  // Categories obertes en mode acordió ("Tots els Plats" - Opció 3)
  // Per defecte obrim 'burgers' (la categoria reina) per estalviar 75% de memòria i render
  const openCategories = new Set(['burgers']);

  // Metadades Oficials de Categories i Jerarquia Gastronòmica
  const CATEGORY_ORDER = {
    'burgers': 1,
    'frankfurts': 2,
    'entrepans': 3,
    'combinats': 4,
    'tapes': 5,
    'texmex': 6,
    'cerveses': 7
  };

  const CATEGORY_METADATA = {
    'burgers': { 
      num: "I",
      code: "01",
      title: "Hamburgueses de Girona", 
      subtitle: "250g de vedella fresca de Girona, receptes de la casa i opció vegana Heura" 
    },
    'frankfurts': { 
      num: "II",
      code: "02",
      title: "Frankfurts & Salsitxes de Baviera", 
      subtitle: "Autèntica xarcuteria tradicional de Baviera fumada en fusta de faig" 
    },
    'entrepans': { 
      num: "III",
      code: "03",
      title: "Entrepans Clàssics & Especials", 
      subtitle: "Pollastre cruixent, vedella, receptes Heura 100% veganes, entrepà vegetal i pa de motlle" 
    },
    'combinats': { 
      num: "IV",
      code: "04",
      title: "Plats Combinats de Port", 
      subtitle: "Dinar complet amb bistec de Girona, entrecot, xistorra amb dos ous, Grobe Chicken i opció 100% vegana" 
    },
    'tapes': { 
      num: "V",
      code: "05",
      title: "Tapes, Patates & Amanides", 
      subtitle: "Patates braves casolanes, croquetes artesanes, moniato i amanides fresques" 
    },
    'texmex': { 
      num: "VI",
      code: "06",
      title: "Especialitats Tex-Mex", 
      subtitle: "Nachos Tijuana complets, guacamole fresc casolà, formatge fos i jalapeños" 
    },
    'cerveses': { 
      num: "VII",
      code: "07",
      title: "Cerveses de Pressió & Begudes", 
      subtitle: "Cerveses alemanyes d'importació, canyes fresques i refrescos" 
    }
  };

  // Funció per renderitzar una targeta individual de plat
  function renderDishCardHtml(dish) {
    const catMeta = CATEGORY_METADATA[dish.category] || { num: '•', title: dish.category };
    
    let badgeHtml = '';
    if (dish.badges && dish.badges.length > 0) {
      let rawBadge = dish.badges[0];
      // Neteja d'emojis per garantir estètica noble i senyorial
      let cleanBadge = rawBadge.replace(/[\u{1F300}-\u{1F9FF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}⭐🌿🌱🐟🔥🥩🍳🍔🌭🌾🥖🍟🌮🍺]/gu, '').trim();
      let badgeClass = 'dish-badge-flag';
      const badgeLower = cleanBadge.toLowerCase();
      if (badgeLower.includes('vegà') || badgeLower.includes('vegana')) {
        badgeClass += ' dish-badge-vegan';
      } else if (badgeLower.includes('vegetarià')) {
        badgeClass += ' dish-badge-veggie';
      } else if (badgeLower.includes('vegetal clàssic') || badgeLower.includes('tonyina')) {
        badgeClass += ' dish-badge-pesce';
      } else if (badgeLower.includes('picant')) {
        badgeClass += ' dish-badge-spicy';
      } else if (badgeLower.includes('bestseller') || badgeLower.includes('especialitat')) {
        badgeClass += ' dish-badge-bestseller';
      }
      badgeHtml = `<div class="${badgeClass}"><span>${cleanBadge}</span></div>`;
    }

    return `
      <article class="dish-card" data-category="${dish.category}" data-dish-id="${dish.id}" tabindex="0" role="button" aria-haspopup="dialog" aria-label="Veure detalls i informació completa de ${dish.name}">
        <div class="dish-card-media">
          <img src="${dish.image}" alt="${dish.name}" class="dish-card-image" loading="lazy">
          ${badgeHtml}
          <span class="dish-card-tap-cue" title="Veure informació completa">
            <span class="material-symbols-outlined">zoom_in</span>
          </span>
        </div>
        
        <div class="dish-card-body">
          <div class="dish-card-category-indicator">
            <span class="category-indicator-num">${catMeta.num}</span>
            <span class="category-indicator-dot">·</span>
            <span class="category-indicator-title">${catMeta.title}</span>
          </div>

          <div class="dish-card-header">
            <h3 class="dish-title">${dish.name}</h3>
            <span class="dish-price-tag">${dish.price.toFixed(2)} €</span>
          </div>

          <p class="dish-description">${dish.description}</p>

          <div class="dish-ingredients-row">
            ${dish.ingredients.map(ing => `<span class="ingredient-badge">${ing}</span>`).join('')}
          </div>

          ${dish.glutenInfo ? `
            <div class="dish-celiac-badge">
              <span>Opció pa per a celíacs (+1,80 €)</span>
            </div>
          ` : ''}
        </div>
      </article>
    `;
  }

  function renderDishes() {
    const gridEl = document.getElementById('dishes-cards-grid');
    if (!gridEl) return;

    let filtered = DISHES_CATALOG.filter(dish => {
      const matchCategory = activeCategory === 'all' || dish.category === activeCategory;
      const matchDiet = !activeDietFilter || (dish.diet && dish.diet.includes(activeDietFilter));
      const matchSearch = !searchQuery || 
        dish.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        dish.description.toLowerCase().includes(searchQuery.toLowerCase()) ||
        dish.ingredients.some(ing => ing.toLowerCase().includes(searchQuery.toLowerCase()));

      return matchCategory && matchDiet && matchSearch;
    });

    // Lògica d'Ordenació Seleccionada
    filtered.sort((a, b) => {
      if (activeSort === 'price-asc') {
        return a.price - b.price;
      } else if (activeSort === 'price-desc') {
        return b.price - a.price;
      } else if (activeSort === 'alpha-asc') {
        return a.name.localeCompare(b.name, 'ca');
      } else {
        // 'featured' per defecte:
        // Si estem veient 'all' (Tots els plats), ordenar primer per la jerarquia oficial de categories
        if (activeCategory === 'all') {
          const catDiff = (CATEGORY_ORDER[a.category] || 99) - (CATEGORY_ORDER[b.category] || 99);
          if (catDiff !== 0) return catDiff;
        }
        // Dins de la mateixa categoria, primer els plats amb badge 'featured'
        const aFeat = (a.diet && a.diet.includes('featured')) ? 1 : 0;
        const bFeat = (b.diet && b.diet.includes('featured')) ? 1 : 0;
        if (bFeat !== aFeat) return bFeat - aFeat;
        return a.name.localeCompare(b.name, 'ca');
      }
    });

    // Actualitzar comptador dinàmic de resultats
    const countEl = document.getElementById('results-count-badge');
    if (countEl) {
      countEl.innerHTML = `Mostrant <strong>${filtered.length}</strong> de ${DISHES_CATALOG.length} plats`;
    }

    // Botó per netejar la cerca
    const clearSearchBtn = document.getElementById('btn-clear-search');
    if (clearSearchBtn) {
      clearSearchBtn.style.display = searchQuery.length > 0 ? 'flex' : 'none';
    }

    // Botó de restablir tots els filtres si n'hi ha algun d'actiu
    const resetBtn = document.getElementById('btn-reset-all-filters');
    const isFiltered = activeCategory !== 'all' || activeDietFilter !== null || searchQuery !== '' || activeSort !== 'featured';
    if (resetBtn) {
      resetBtn.style.display = isFiltered ? 'inline-flex' : 'none';
    }

    if (filtered.length === 0) {
      gridEl.innerHTML = `
        <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 1rem; color: var(--text-muted);">
          <span class="material-symbols-outlined" style="font-size: 3.2rem; color: var(--border-warm); margin-bottom: 1rem;">search_off</span>
          <h4 style="font-family: var(--font-primary); font-size: 1.3rem; color: var(--obsidian-black); margin-bottom: 0.5rem;">No s'han trobat plats</h4>
          <p style="font-size: 0.95rem; margin-bottom: 1rem;">Prova amb una altra cerca o canvia de categoria o filtres.</p>
          <button type="button" class="btn-primary-brand" onclick="document.getElementById('btn-reset-all-filters')?.click()" style="padding: 0.5rem 1.25rem; font-size: 0.88rem;">Veure tots els plats</button>
        </div>
      `;
      return;
    }

    // A la vista completa "Tots els Plats" sense cerca activa ni ordenació per preu, organitzar en acordions interactius (Opció 3)
    const showCategoryDividers = activeCategory === 'all' && searchQuery === '' && activeDietFilter === null && activeSort === 'featured';

    // Botó per expandir/plegar tots els acordions
    const toggleAllBtn = document.getElementById('btn-toggle-all-accordions');
    if (toggleAllBtn) {
      if (showCategoryDividers) {
        toggleAllBtn.style.display = 'inline-flex';
        const allKeys = ['burgers', 'frankfurts', 'entrepans', 'combinats', 'tapes', 'texmex', 'cerveses'];
        const allOpen = allKeys.every(k => openCategories.has(k));
        const iconSpan = toggleAllBtn.querySelector('.material-symbols-outlined');
        const textSpan = toggleAllBtn.querySelector('.toggle-all-text');
        if (iconSpan) iconSpan.textContent = allOpen ? 'unfold_less' : 'unfold_more';
        if (textSpan) textSpan.textContent = allOpen ? 'Plegar tot' : 'Expandir tot';
      } else {
        toggleAllBtn.style.display = 'none';
      }
    }

    if (showCategoryDividers) {
      const categoryKeys = ['burgers', 'frankfurts', 'entrepans', 'combinats', 'tapes', 'texmex', 'cerveses'];
      let html = '';
      categoryKeys.forEach(catKey => {
        const catDishes = filtered.filter(d => d.category === catKey);
        if (catDishes.length > 0) {
          const catMeta = CATEGORY_METADATA[catKey];
          const isOpen = openCategories.has(catKey);
          html += `
            <section class="menu-category-accordion ${isOpen ? 'is-open' : 'is-collapsed'}" data-category-accordion="${catKey}">
              <button type="button" class="category-accordion-trigger" data-toggle-cat="${catKey}" aria-expanded="${isOpen}">
                <div class="category-divider-content">
                  <div class="category-divider-numeral">
                    <span class="numeral-roman">${catMeta.num}</span>
                  </div>
                  <div>
                    <h2 class="category-divider-title">${catMeta.title}</h2>
                    <p class="category-divider-subtitle">${catMeta.subtitle}</p>
                  </div>
                </div>
                <div class="category-accordion-actions">
                  <span class="category-divider-count">${catDishes.length} especialitats</span>
                  <span class="category-accordion-chevron material-symbols-outlined">${isOpen ? 'expand_less' : 'expand_more'}</span>
                </div>
              </button>
              <div class="category-accordion-body" style="${isOpen ? '' : 'display: none;'}">
                <div class="category-cards-grid products-cards-grid ${currentMenuView === 'grid' ? 'view-grid-mode' : 'view-single-mode'}">
                  ${catDishes.map(renderDishCardHtml).join('')}
                </div>
              </div>
            </section>
          `;
        }
      });
      gridEl.innerHTML = html;

      // Event listeners per obrir/tancar cada secció in-place amb màxima fluïdesa
      gridEl.querySelectorAll('.category-accordion-trigger').forEach(trigger => {
        trigger.addEventListener('click', (e) => {
          e.preventDefault();
          const cat = trigger.getAttribute('data-toggle-cat');
          const section = trigger.closest('.menu-category-accordion');
          const body = section ? section.querySelector('.category-accordion-body') : null;
          if (!cat || !section || !body) return;

          const isCurrentlyOpen = openCategories.has(cat);
          if (isCurrentlyOpen) {
            openCategories.delete(cat);
            section.classList.remove('is-open');
            section.classList.add('is-collapsed');
            trigger.setAttribute('aria-expanded', 'false');
            body.style.display = 'none';
          } else {
            openCategories.add(cat);
            section.classList.add('is-open');
            section.classList.remove('is-collapsed');
            trigger.setAttribute('aria-expanded', 'true');
            body.style.display = '';
          }

          // Actualitzar text i icona del botó global "Plegar tot / Expandir tot"
          if (toggleAllBtn) {
            const allKeys = ['burgers', 'frankfurts', 'entrepans', 'combinats', 'tapes', 'texmex', 'cerveses'];
            const allOpen = allKeys.every(k => openCategories.has(k));
            const iconSpan = toggleAllBtn.querySelector('.material-symbols-outlined');
            const textSpan = toggleAllBtn.querySelector('.toggle-all-text');
            if (iconSpan) iconSpan.textContent = allOpen ? 'unfold_less' : 'unfold_more';
            if (textSpan) textSpan.textContent = allOpen ? 'Plegar tot' : 'Expandir tot';
          }
        });
      });
    } else {
      gridEl.innerHTML = filtered.map(renderDishCardHtml).join('');
    }
  }

  // Botó d'acció ràpida per expandir o plegar tots els blocs in-place
  const toggleAllBtn = document.getElementById('btn-toggle-all-accordions');
  if (toggleAllBtn) {
    toggleAllBtn.addEventListener('click', () => {
      const allKeys = ['burgers', 'frankfurts', 'entrepans', 'combinats', 'tapes', 'texmex', 'cerveses'];
      const allOpen = allKeys.every(k => openCategories.has(k));
      
      allKeys.forEach(k => {
        const section = document.querySelector(`[data-category-accordion="${k}"]`);
        if (!section) return;
        const trigger = section.querySelector('.category-accordion-trigger');
        const body = section.querySelector('.category-accordion-body');
        if (allOpen) {
          openCategories.delete(k);
          section.classList.remove('is-open');
          section.classList.add('is-collapsed');
          if (trigger) trigger.setAttribute('aria-expanded', 'false');
          if (body) body.style.display = 'none';
        } else {
          openCategories.add(k);
          section.classList.add('is-open');
          section.classList.remove('is-collapsed');
          if (trigger) trigger.setAttribute('aria-expanded', 'true');
          if (body) body.style.display = '';
        }
      });

      const iconSpan = toggleAllBtn.querySelector('.material-symbols-outlined');
      const textSpan = toggleAllBtn.querySelector('.toggle-all-text');
      if (iconSpan) iconSpan.textContent = allOpen ? 'unfold_more' : 'unfold_less';
      if (textSpan) textSpan.textContent = allOpen ? 'Expandir tot' : 'Plegar tot';
    });
  }

  // Pestañas de Categoria
  const categoryTabs = document.querySelectorAll('.menu-tab-btn');
  categoryTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      categoryTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      activeCategory = tab.getAttribute('data-category');
      renderDishes();
      // Centratge suau i tàctil de la pestanya seleccionada a mòbils
      tab.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    });
  });

  // Targetes Bento que enllacen directament a una categoria
  document.querySelectorAll('[data-category-link]').forEach(bentoTile => {
    bentoTile.addEventListener('click', () => {
      const targetCategory = bentoTile.getAttribute('data-category-link');
      if (targetCategory) {
        activeCategory = targetCategory;
        categoryTabs.forEach(t => {
          t.classList.toggle('active', t.getAttribute('data-category') === targetCategory);
        });
        renderDishes();
      }
    });
  });

  // Selector d'Ordenació
  const sortSelect = document.getElementById('menu-sort-select');
  if (sortSelect) {
    sortSelect.addEventListener('change', (e) => {
      activeSort = e.target.value;
      renderDishes();
    });
  }

  // Filtres Dietètics
  const dietaryChips = document.querySelectorAll('.dietary-chip');
  dietaryChips.forEach(chip => {
    chip.addEventListener('click', () => {
      const diet = chip.getAttribute('data-diet');
      if (activeDietFilter === diet) {
        activeDietFilter = null;
        chip.classList.remove('active');
      } else {
        dietaryChips.forEach(c => c.classList.remove('active'));
        activeDietFilter = diet;
        chip.classList.add('active');
      }
      renderDishes();
    });
  });

  // Input de Cerca & Botó Netejar
  const searchInput = document.getElementById('menu-search-input');
  const clearSearchBtn = document.getElementById('btn-clear-search');
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      searchQuery = e.target.value.trim();
      renderDishes();
    });
  }

  if (clearSearchBtn) {
    clearSearchBtn.addEventListener('click', () => {
      searchQuery = '';
      if (searchInput) {
        searchInput.value = '';
        searchInput.focus();
      }
      renderDishes();
    });
  }

  // Botó Restablir Tots els Filtres
  const resetAllBtn = document.getElementById('btn-reset-all-filters');
  if (resetAllBtn) {
    resetAllBtn.addEventListener('click', () => {
      activeCategory = 'all';
      activeDietFilter = null;
      activeSort = 'featured';
      searchQuery = '';
      if (searchInput) searchInput.value = '';
      if (sortSelect) sortSelect.value = 'featured';
      categoryTabs.forEach(t => t.classList.toggle('active', t.getAttribute('data-category') === 'all'));
      dietaryChips.forEach(c => c.classList.remove('active'));
      renderDishes();
    });
  }

  // Detecció automàtica de categoria des dels paràmetres d'URL (ex: carta.html?cat=combinats)
  const urlParams = new URLSearchParams(window.location.search);
  const catParam = urlParams.get('cat');
  if (catParam && ['burgers', 'frankfurts', 'entrepans', 'combinats', 'tapes', 'texmex', 'cerveses'].includes(catParam)) {
    activeCategory = catParam;
    categoryTabs.forEach(t => {
      const isMatch = t.getAttribute('data-category') === catParam;
      t.classList.toggle('active', isMatch);
      if (isMatch) {
        setTimeout(() => t.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' }), 120);
      }
    });
  }

  // Crida a l'acció (CTA): Botó del Banner de Plats Combinats
  const btnExploreCombinats = document.getElementById('btn-explore-combinats');
  if (btnExploreCombinats) {
    btnExploreCombinats.addEventListener('click', () => {
      activeCategory = 'combinats';
      categoryTabs.forEach(t => {
        t.classList.toggle('active', t.getAttribute('data-category') === 'combinats');
      });
      renderDishes();
      const menuTabsBar = document.getElementById('menu-category-tabs');
      if (menuTabsBar) {
        menuTabsBar.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  }

  // --- GESTIÓ DEL MODAL BOTTOM SHEET DE DETALL DE PLAT (BISTRO GOURMET) ---
  const dishSheetBackdrop = document.getElementById('dish-detail-sheet-backdrop');
  const dishSheetContent = document.getElementById('dish-sheet-content');
  const btnCloseDishSheet = document.getElementById('btn-close-dish-sheet');
  const dishSheetHandleWrap = document.getElementById('dish-sheet-handle-wrap');

  function openDishDetailSheet(dishId) {
    if (!dishSheetBackdrop || !dishSheetContent || typeof DISHES_CATALOG === 'undefined') return;
    const dish = DISHES_CATALOG.find(d => d.id === dishId);
    if (!dish) return;

    const catMeta = CATEGORY_METADATA[dish.category] || { num: '•', title: dish.category };

    // Badges gastronòmics estilitzats per a la fitxa
    let badgesMarkup = '';
    if (dish.badges && dish.badges.length > 0) {
      badgesMarkup = dish.badges.map(b => {
        let clean = b.replace(/[\u{1F300}-\u{1F9FF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}⭐🌿🌱🐟🔥🥩🍳🍔🌭🌾🥖🍟🌮🍺]/gu, '').trim();
        return `<span class="sheet-badge-pill">${clean}</span>`;
      }).join('');
    }

    dishSheetContent.innerHTML = `
      <div class="dish-sheet-hero">
        <div class="dish-sheet-hero-media">
          <img src="${dish.image}" alt="${dish.name}" class="dish-sheet-hero-img">
          <div class="dish-sheet-gradient-overlay"></div>
          ${badgesMarkup ? `<div class="dish-sheet-badges-floating">${badgesMarkup}</div>` : ''}
        </div>
      </div>

      <div class="dish-sheet-main-info">
        <div class="dish-sheet-kicker-row">
          <span class="sheet-kicker-num">${catMeta.num}</span>
          <span class="sheet-kicker-dot">·</span>
          <span class="sheet-kicker-cat">${catMeta.title}</span>
        </div>

        <div class="dish-sheet-title-price-block">
          <h2 class="dish-sheet-name">${dish.name}</h2>
          <div class="dish-sheet-price-tag">${dish.price.toFixed(2)} €</div>
        </div>

        <div class="sheet-divider-line"></div>

        <!-- Descripció Íntegra sense retalls -->
        <div class="dish-sheet-section">
          <h4 class="dish-sheet-section-title">
            <span class="material-symbols-outlined" style="font-size: 1.15rem; color: var(--crimson-red);">restaurant</span>
            <span>Descripció del Plat</span>
          </h4>
          <p class="dish-sheet-full-desc">${dish.description}</p>
        </div>

        <!-- Ingredients Detallats -->
        <div class="dish-sheet-section">
          <h4 class="dish-sheet-section-title">
            <span class="material-symbols-outlined" style="font-size: 1.15rem; color: var(--crimson-red);">nutrition</span>
            <span>Ingredients & Elaboració</span>
          </h4>
          <div class="dish-sheet-ingredients-pills">
            ${dish.ingredients.map(ing => `
              <div class="dish-sheet-ing-pill">
                <span class="material-symbols-outlined pill-check-icon">check_circle</span>
                <span>${ing}</span>
              </div>
            `).join('')}
          </div>
        </div>

        <!-- Informació Celíacs (Curta & Neta) -->
        ${dish.glutenInfo ? `
          <div class="dish-sheet-celiac-compact">
            <span class="material-symbols-outlined celiac-compact-icon">verified</span>
            <span class="celiac-compact-text">${dish.glutenInfo}</span>
          </div>
        ` : ''}

        <!-- Botons d'Acció al Peu de la Fitxa (Recollir + Glovo & Uber) -->
        <div class="dish-sheet-actions-bar">
          <a href="tel:937521077" class="btn-sheet-call-takeaway" title="Comandar per recollir">
            <span class="material-symbols-outlined">call</span>
            <span>Recollir: 937 52 10 77</span>
          </a>
          <div class="dish-sheet-delivery-group">
            <a href="https://glovoapp.com/es/es/el-masnou/stores/john-s-grobe-el-masnou" target="_blank" rel="noopener noreferrer" class="btn-sheet-delivery-icon btn-sheet-glovo" title="Demanar per Glovo" aria-label="Glovo">
              <svg viewBox="0 0 32 32" width="22" height="22" aria-hidden="true">
                <circle cx="16" cy="16" r="15" fill="#FFC244"/>
                <path d="M16 6.5C11.3 6.5 7.5 10.3 7.5 15c0 3.2 1.8 6 4.4 7.4l-.5 2.5c-.1.4.3.8.7.7l3.2-1.8c.2 0 .5.1.7.1 4.7 0 8.5-3.8 8.5-8.5s-3.8-8.4-8.5-8.4zm.5 12c-2 0-3.6-1.6-3.6-3.6s1.6-3.6 3.6-3.6 3.6 1.6 3.6 3.6-1.6 3.6-3.6 3.6z" fill="#00A082"/>
              </svg>
            </a>
            <a href="https://www.ubereats.com/es/store/johns-grobe/7NvvBDRvXBaBMQdqKzeyug?srsltid=AU7gw4W2e9UdK0gnC2ZBDe7mzHemxtuFtGhw5kj_aa1WI_cfRHgoIXlI" target="_blank" rel="noopener noreferrer" class="btn-sheet-delivery-icon btn-sheet-uber" title="Demanar per Uber Eats" aria-label="Uber Eats">
              <svg viewBox="0 0 32 32" width="22" height="22" aria-hidden="true">
                <rect width="32" height="32" rx="16" fill="#06C167"/>
                <path d="M9 13.5c0-.8.7-1.5 1.5-1.5h11c.8 0 1.5.7 1.5 1.5v5c0 .8-.7 1.5-1.5 1.5h-11c-.8 0-1.5-.7-1.5-1.5v-5z" fill="#FFFFFF"/>
                <path d="M12 9.5h8v1.8h-8z" fill="#FFFFFF"/>
                <circle cx="16" cy="16" r="1.8" fill="#06C167"/>
              </svg>
            </a>
          </div>
        </div>
      </div>
    `;

    dishSheetBackdrop.classList.add('open');
    dishSheetBackdrop.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');

    // Listener pel botó secundari tancar
    const secClose = document.getElementById('btn-sheet-secondary-close');
    if (secClose) {
      secClose.addEventListener('click', closeDishDetailSheet);
    }
  }

  function closeDishDetailSheet() {
    if (!dishSheetBackdrop) return;
    dishSheetBackdrop.classList.remove('open');
    dishSheetBackdrop.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
  }

  if (btnCloseDishSheet) {
    btnCloseDishSheet.addEventListener('click', closeDishDetailSheet);
  }

  if (dishSheetBackdrop) {
    dishSheetBackdrop.addEventListener('click', (e) => {
      if (e.target === dishSheetBackdrop) {
        closeDishDetailSheet();
      }
    });
  }

  if (dishSheetHandleWrap) {
    dishSheetHandleWrap.addEventListener('click', closeDishDetailSheet);
  }

  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && dishSheetBackdrop && dishSheetBackdrop.classList.contains('open')) {
      closeDishDetailSheet();
    }
  });

  // Delegació d'esdeveniments per clic a targeta de plat (Carta i Portada)
  const cardContainers = [
    document.getElementById('dishes-cards-grid'),
    document.getElementById('bestsellers-grid')
  ].filter(Boolean);

  cardContainers.forEach(container => {
    container.addEventListener('click', (e) => {
      const card = e.target.closest('.dish-card');
      if (card) {
        const dishId = card.getAttribute('data-dish-id');
        if (dishId) {
          openDishDetailSheet(dishId);
        }
      }
    });

    container.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        const card = e.target.closest('.dish-card');
        if (card) {
          const dishId = card.getAttribute('data-dish-id');
          if (dishId) {
            e.preventDefault();
            openDishDetailSheet(dishId);
          }
        }
      }
    });
  });

  // --- SELECTOR DINÀMIC DE VISTA: 1 COLUMNA (ORIGINAL) vs 2 COLUMNES (COMPACTE) ---
  const btnViewSingle = document.getElementById('btn-view-single');
  const btnViewGrid = document.getElementById('btn-view-grid');
  const productsGridEl = document.getElementById('dishes-cards-grid');
  let currentMenuView = localStorage.getItem('jg_menu_view') || 'single';

  function setMenuView(mode) {
    currentMenuView = mode;
    try {
      localStorage.setItem('jg_menu_view', mode);
    } catch (e) {}

    if (productsGridEl) {
      if (mode === 'grid') {
        productsGridEl.classList.remove('view-single-mode');
        productsGridEl.classList.add('view-grid-mode');
      } else {
        productsGridEl.classList.remove('view-grid-mode');
        productsGridEl.classList.add('view-single-mode');
      }

      productsGridEl.querySelectorAll('.category-cards-grid').forEach(grid => {
        if (mode === 'grid') {
          grid.classList.remove('view-single-mode');
          grid.classList.add('view-grid-mode');
        } else {
          grid.classList.remove('view-grid-mode');
          grid.classList.add('view-single-mode');
        }
      });
    }

    if (btnViewSingle && btnViewGrid) {
      btnViewSingle.classList.toggle('active', mode === 'single');
      btnViewGrid.classList.toggle('active', mode === 'grid');
    }
  }

  if (btnViewSingle) {
    btnViewSingle.addEventListener('click', () => setMenuView('single'));
  }
  if (btnViewGrid) {
    btnViewGrid.addEventListener('click', () => setMenuView('grid'));
  }

  // Inicialitzar la vista guardada
  setMenuView(currentMenuView);

  // --- 3. ANIMACIÓ DE FOTOGRAMES PER SCROLL (JOHN'S GROBE - FONS DE VÍDEO ANIMAT) ---
  function initScrollFrameAnimation() {
    const section = document.getElementById('inici');
    const canvas = document.getElementById('hero-scroll-canvas');

    if (!section || !canvas) return;

    const ctx = canvas.getContext('2d', { alpha: false });
    if (ctx) {
      ctx.imageSmoothingEnabled = true;
      ctx.imageSmoothingQuality = 'high';
    }
    const totalFrames = 70; // 70 fotogrames d'alta definició extrets del vídeo de la barra
    const frames = [];
    let loadedCount = 0;
    let currentFrameIndex = -1;

    // Resolució adaptativa segons dispositiu: 1920x1080 a escriptori, 960x540 a mòbil
    // Estalvia un 75% de memòria VRAM a pantalles mòbils mantenint màxima nitidesa i 60fps
    function adaptCanvasResolution() {
      const isMobile = window.innerWidth < 768;
      const targetW = isMobile ? 960 : 1920;
      const targetH = isMobile ? 540 : 1080;
      if (canvas.width !== targetW || canvas.height !== targetH) {
        canvas.width = targetW;
        canvas.height = targetH;
        if (currentFrameIndex >= 0 && frames[currentFrameIndex]) {
          renderFrame(currentFrameIndex);
        }
      }
    }
    adaptCanvasResolution();

    function onFrameReady(index) {
      loadedCount++;
      if (loadedCount === 1 || index === 1) {
        targetProgress = getScrollProgress();
        currentProgress = targetProgress;
        const initIdx = Math.min(Math.max(Math.round(currentProgress * (totalFrames - 1)), 0), totalFrames - 1);
        renderFrame(initIdx);
      }
    }

    // Precàrrega asíncrona amb img.decode() per descompressió a la GPU en segon pla (Zero micro-talls)
    function preloadFrames() {
      // Prioritat crítica 1: El primer fotograma es carrega immediatament per evitar pantalla negra
      const firstImg = new Image();
      firstImg.src = 'scrollanimation/frame_001.webp';
      firstImg.onload = () => onFrameReady(1);
      frames[0] = firstImg;

      for (let i = 2; i <= totalFrames; i++) {
        const img = new Image();
        const paddedIndex = String(i).padStart(3, '0');
        img.src = `scrollanimation/frame_${paddedIndex}.webp`;

        if ('decode' in img) {
          img.decode().then(() => {
            onFrameReady(i);
          }).catch(() => {
            if (img.complete) onFrameReady(i);
          });
        } else {
          img.onload = () => onFrameReady(i);
        }

        frames[i - 1] = img;
      }
    }

    function renderFrame(index) {
      if (!frames[index] || !frames[index].complete || frames[index].naturalWidth === 0) return;
      currentFrameIndex = index;
      ctx.drawImage(frames[index], 0, 0, canvas.width, canvas.height);
    }

    let targetProgress = 0;
    let currentProgress = 0;
    let isLoopRunning = false;
    let lastTime = 0;
    const stickyElement = section.querySelector('.hero-scroll-sticky');

    function getScrollProgress() {
      const rect = section.getBoundingClientRect();
      const viewportH = (stickyElement && stickyElement.offsetHeight > 0) ? stickyElement.offsetHeight : window.innerHeight;
      const scrollDistance = rect.height - viewportH;
      if (scrollDistance <= 0) return 0;
      return Math.min(Math.max(-rect.top / scrollDistance, 0), 1);
    }

    function smoothAnimationLoop(timestamp) {
      if (!lastTime) lastTime = timestamp;
      const dt = Math.min((timestamp - lastTime) / 1000, 0.05); // Màxim 50ms per protecció
      lastTime = timestamp;

      const diff = targetProgress - currentProgress;
      
      // Amortiment físic exponencial independent dels Hz de la pantalla (60Hz / 120Hz ProMotion)
      const decay = 1 - Math.exp(-14 * dt);

      if (Math.abs(diff) > 0.00015) {
        currentProgress += diff * decay;
      } else {
        currentProgress = targetProgress;
      }

      const targetIndex = Math.min(Math.max(Math.round(currentProgress * (totalFrames - 1)), 0), totalFrames - 1);
      if (targetIndex !== currentFrameIndex) {
        renderFrame(targetIndex);
      }

      if (Math.abs(targetProgress - currentProgress) > 0.00015) {
        window.requestAnimationFrame(smoothAnimationLoop);
      } else {
        currentProgress = targetProgress;
        isLoopRunning = false;
        lastTime = 0;
      }
    }

    const scrollCue = document.getElementById('hero-scroll-cue');

    function onScrollUpdate() {
      targetProgress = getScrollProgress();
      if (!isLoopRunning) {
        isLoopRunning = true;
        lastTime = 0;
        window.requestAnimationFrame(smoothAnimationLoop);
      }

      // Ocultar suavement el cue de scroll en iniciar el moviment
      if (scrollCue) {
        if (targetProgress > 0.03 || window.scrollY > 40) {
          scrollCue.classList.add('is-hidden');
        } else {
          scrollCue.classList.remove('is-hidden');
        }
      }
    }

    if (lenis) {
      lenis.on('scroll', onScrollUpdate);
    }
    window.addEventListener('scroll', onScrollUpdate, { passive: true });
    window.addEventListener('resize', () => {
      adaptCanvasResolution();
      onScrollUpdate();
    }, { passive: true });
    preloadFrames();


    // Interacció elèctrica amb el rètol de neó ("El gust és nostre")
    const neonSign = document.getElementById('hero-neon-sign');
    if (neonSign) {
      neonSign.addEventListener('click', () => {
        neonSign.classList.remove('neon-rebooting');
        void neonSign.offsetWidth; // Forçar reflow per reiniciar l'animació
        neonSign.classList.add('neon-rebooting');
        setTimeout(() => {
          neonSign.classList.remove('neon-rebooting');
        }, 950);
      });
    }
  }

  // --- 5. MODAL DE RESERVA DE TAULA ---
  const bookingModal = document.getElementById('booking-modal-overlay');
  const btnOpenBookingNav = document.getElementById('btn-open-booking-nav');
  const btnHeroBooking = document.getElementById('btn-hero-booking');
  const btnOpenBookingBottom = document.getElementById('btn-open-booking-bottom');
  const btnTakeawayBooking = document.getElementById('btn-takeaway-booking');
  const btnMobileBooking = document.getElementById('btn-mobile-booking');
  const btnCloseBooking = document.getElementById('btn-close-booking');
  const bookingForm = document.getElementById('booking-form');

  function openBooking() {
    if (bookingModal) {
      bookingModal.classList.add('active');
      document.body.style.overflow = 'hidden';
      if (lenis) lenis.stop();
    }
  }

  function closeBooking() {
    if (bookingModal) {
      bookingModal.classList.remove('active');
      document.body.style.overflow = '';
      if (lenis) lenis.start();
    }
  }

  const btnHistoryBooking = document.getElementById('btn-history-booking');

  if (btnOpenBookingNav) btnOpenBookingNav.addEventListener('click', openBooking);
  if (btnHeroBooking) btnHeroBooking.addEventListener('click', openBooking);
  if (btnHistoryBooking) btnHistoryBooking.addEventListener('click', openBooking);
  if (btnOpenBookingBottom) btnOpenBookingBottom.addEventListener('click', openBooking);
  if (btnTakeawayBooking) btnTakeawayBooking.addEventListener('click', openBooking);
  if (btnMobileBooking) btnMobileBooking.addEventListener('click', openBooking);
  if (btnCloseBooking) btnCloseBooking.addEventListener('click', closeBooking);

  if (bookingModal) {
    bookingModal.addEventListener('click', (e) => {
      if (e.target === bookingModal) closeBooking();
    });
  }

  // Interacció amb Pills de Comensals al formulari de reserva (Mínim 3 persones)
  const guestPills = document.querySelectorAll('.btn-guest-pill');
  const guestsInput = document.getElementById('booking-guests');
  guestPills.forEach(pill => {
    pill.addEventListener('click', () => {
      guestPills.forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      if (guestsInput) {
        guestsInput.value = pill.getAttribute('data-guests') || '3';
      }
    });
  });

  // Interacció amb Zona de Seient (Terrassa / Interior)
  const seatingPills = document.querySelectorAll('.btn-seating-pill');
  seatingPills.forEach(pill => {
    pill.addEventListener('click', () => {
      seatingPills.forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
    });
  });

  if (bookingForm) {
    bookingForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const name = document.getElementById('booking-name').value.trim();
      const phone = document.getElementById('booking-phone').value.trim();
      const date = document.getElementById('booking-date').value;
      const time = document.getElementById('booking-time').value;
      const guests = guestsInput ? guestsInput.value : '3';
      const activeSeating = document.querySelector('.btn-seating-pill.active');
      const seatingText = activeSeating ? activeSeating.textContent.trim() : 'Sense preferència';
      const notesInput = document.getElementById('booking-notes');
      const notes = notesInput && notesInput.value.trim() ? notesInput.value.trim() : 'Cap';

      // Missatge formal estructurat per a WhatsApp sense emojis infantils
      const whatsappNumber = '34656612873';
      const messageLines = [
        "SOL·LICITUD DE RESERVA — JOHN'S GROBE (Marina Port Premià)",
        "--------------------------------------------------",
        `Client: ${name}`,
        `Telèfon: ${phone}`,
        `Data: ${date}`,
        `Hora: ${time} h`,
        `Comensals: ${guests} persones`,
        `Zona: ${seatingText}`,
        `Observacions: ${notes}`,
        "--------------------------------------------------",
        "Petició tramitada des de la web oficial johnsgrobe.com"
      ];

      const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(messageLines.join('\n'))}`;

      showToast("Sol·licitud registrada. Obrint WhatsApp per confirmar la reserva...");
      
      // Obrir WhatsApp
      window.open(whatsappUrl, '_blank', 'noopener,noreferrer');

      bookingForm.reset();
      if (guestsInput) guestsInput.value = '3';
      
      // Restablir píndola de 3 persones com a activa
      guestPills.forEach(p => {
        if (p.getAttribute('data-guests') === '3') {
          p.classList.add('active');
        } else {
          p.classList.remove('active');
        }
      });

      closeBooking();
    });
  }

  // Formulari de Newsletter
  const newsletterForm = document.getElementById('newsletter-form');
  if (newsletterForm) {
    newsletterForm.addEventListener('submit', (e) => {
      e.preventDefault();
      showToast("Gràcies per subscriure't a les novetats de John's Grobe!");
      newsletterForm.reset();
    });
  }

  // Menú Mòbil
  const mobileMenuBtn = document.getElementById('mobile-menu-btn');
  const mobileNavPanel = document.getElementById('mobile-nav-panel');
  if (mobileMenuBtn && mobileNavPanel) {
    mobileMenuBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      mobileMenuBtn.classList.toggle('open');
      mobileNavPanel.classList.toggle('open');
    });

    mobileNavPanel.querySelectorAll('.mobile-nav-link, .capsule-mobile-item-link, #btn-mobile-booking, .btn-secondary-brand').forEach(link => {
      link.addEventListener('click', () => {
        mobileMenuBtn.classList.remove('open');
        mobileNavPanel.classList.remove('open');
      });
    });

    // Tancar en fer clic a fora
    document.addEventListener('click', (e) => {
      if (!mobileNavPanel.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
        mobileMenuBtn.classList.remove('open');
        mobileNavPanel.classList.remove('open');
      }
    });
  }

  // --- 6. TOAST NOTIFICATION HELPER ---
  function showToast(message) {
    const toast = document.getElementById('toast-notification');
    const toastText = document.getElementById('toast-text');
    if (!toast || !toastText) return;

    toastText.textContent = message;
    toast.classList.add('active');

    setTimeout(() => {
      toast.classList.remove('active');
    }, 4000);
  }

  // --- 7. BOTÓ TORNAR A DALT (BACK TO TOP) ---
  const backToTopBtn = document.getElementById('btn-back-to-top');
  if (backToTopBtn) {
    backToTopBtn.addEventListener('click', () => {
      if (lenis) {
        lenis.scrollTo(0, { duration: 1.1 });
      } else {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    });
  }

  // --- 8. SCROLL SPY, NAVBAR BLUR & BACK TO TOP VISIBILITY ---
  const navbar = document.getElementById('navbar');

  window.addEventListener('scroll', () => {
    const scrollPos = window.scrollY;

    if (navbar) {
      if (scrollPos > 30) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    }

    if (backToTopBtn) {
      if (scrollPos > 380) {
        backToTopBtn.classList.add('visible');
      } else {
        backToTopBtn.classList.remove('visible');
      }
    }
  }, { passive: true });

  // --- 9. COMPARADOR INTERACTIU BEFORE & AFTER (2006 VS AVUI) ---
  function initBeforeAfterSlider() {
    const slider = document.getElementById('before-after-slider');
    const beforeLayer = document.getElementById('ba-before-layer');
    const handle = document.getElementById('ba-handle');

    if (!slider || !beforeLayer || !handle) return;

    let isDragging = false;

    function updateSlider(clientX) {
      const rect = slider.getBoundingClientRect();
      const offsetX = clientX - rect.left;
      let percentage = (offsetX / rect.width) * 100;
      percentage = Math.max(0, Math.min(100, percentage));

      beforeLayer.style.width = `${percentage}%`;
      handle.style.left = `${percentage}%`;
      handle.setAttribute('aria-valuenow', Math.round(percentage));
    }

    function onPointerDown(e) {
      isDragging = true;
      try {
        if (slider.setPointerCapture && e.pointerId !== undefined) {
          slider.setPointerCapture(e.pointerId);
        }
      } catch (err) {}
      const clientX = e.clientX || (e.touches && e.touches[0] && e.touches[0].clientX);
      if (clientX !== undefined) updateSlider(clientX);
    }

    function onPointerMove(e) {
      if (!isDragging) return;
      const clientX = e.clientX || (e.touches && e.touches[0] && e.touches[0].clientX);
      if (clientX !== undefined) updateSlider(clientX);
    }

    function onPointerUp(e) {
      if (!isDragging) return;
      isDragging = false;
      try {
        if (slider.releasePointerCapture && e && e.pointerId !== undefined) {
          slider.releasePointerCapture(e.pointerId);
        }
      } catch (err) {}
    }

    // Suport universal de punter modern (tacte, stylus, ratolí)
    slider.addEventListener('pointerdown', onPointerDown);
    slider.addEventListener('pointermove', onPointerMove);
    slider.addEventListener('pointerup', onPointerUp);
    slider.addEventListener('pointercancel', onPointerUp);

    // Compatibilitat amb mousedown clàssic i touchmove com a seguretat
    window.addEventListener('mouseup', onPointerUp);
    slider.addEventListener('touchstart', onPointerDown, { passive: true });
    window.addEventListener('touchmove', onPointerMove, { passive: true });
    window.addEventListener('touchend', onPointerUp, { passive: true });
  }

  // --- 10. CARRUSEL INTERACTIU DE PLATS COMBINATS (SLIDER HORITZONTAL) ---
  function initCombinatsCarousel() {
    const stage = document.getElementById('combinats-floating-stage');
    if (!stage) return;

    const track = document.getElementById('combinats-slides-track');
    const slides = stage.querySelectorAll('.combinat-slide');
    const navPills = stage.querySelectorAll('.stage-nav-pill');
    const btnPrev = document.getElementById('btn-combinat-prev');
    const btnNext = document.getElementById('btn-combinat-next');

    if (!slides.length) return;

    let currentIndex = 0;
    let autoPlayTimer = null;

    function showSlide(index) {
      if (index < 0) index = slides.length - 1;
      if (index >= slides.length) index = 0;
      currentIndex = index;

      if (track) {
        track.style.transform = `translateX(-${currentIndex * 100}%)`;
      }

      slides.forEach((s, idx) => {
        s.classList.toggle('active', idx === currentIndex);
      });

      navPills.forEach((p, idx) => {
        p.classList.toggle('active', idx === currentIndex);
      });
    }

    // Sincronització suau amb l'scroll durant la pausa / retenció de la secció
    const pinWrapper = document.getElementById('combinats-scroll-pin-wrapper');
    if (pinWrapper) {
      let manualInteractionLock = false;
      let manualLockTimer = null;

      function triggerManualInteraction(index) {
        manualInteractionLock = true;
        if (manualLockTimer) clearTimeout(manualLockTimer);
        manualLockTimer = setTimeout(() => {
          manualInteractionLock = false;
        }, 2200);
        showSlide(index);
        resetAutoPlay();
      }

      if (btnPrev) {
        btnPrev.addEventListener('click', () => triggerManualInteraction(currentIndex - 1));
      }
      if (btnNext) {
        btnNext.addEventListener('click', () => triggerManualInteraction(currentIndex + 1));
      }

      window.addEventListener('scroll', () => {
        if (manualInteractionLock) return;
        const rect = pinWrapper.getBoundingClientRect();
        const pinDistance = pinWrapper.offsetHeight - window.innerHeight;
        if (pinDistance <= 40) return;

        const scrolledInside = -rect.top;
        if (scrolledInside >= 0 && scrolledInside <= pinDistance) {
          const progress = scrolledInside / pinDistance;
          const targetSlide = Math.min(Math.floor(progress * slides.length), slides.length - 1);
          if (targetSlide !== currentIndex) {
            showSlide(targetSlide);
          }
        }
      }, { passive: true });
    } else {
      if (btnPrev) {
        btnPrev.addEventListener('click', () => {
          showSlide(currentIndex - 1);
          resetAutoPlay();
        });
      }
      if (btnNext) {
        btnNext.addEventListener('click', () => {
          showSlide(currentIndex + 1);
          resetAutoPlay();
        });
      }
    }

    navPills.forEach((pill, idx) => {
      pill.addEventListener('click', () => {
        showSlide(idx);
        resetAutoPlay();
      });
    });

    function startAutoPlay() {
      stopAutoPlay();
      autoPlayTimer = setInterval(() => {
        showSlide(currentIndex + 1);
      }, 5500);
    }

    function stopAutoPlay() {
      if (autoPlayTimer) clearInterval(autoPlayTimer);
    }

    function resetAutoPlay() {
      stopAutoPlay();
      startAutoPlay();
    }

    stage.addEventListener('mouseenter', stopAutoPlay);
    stage.addEventListener('mouseleave', startAutoPlay);

    // Suport de gestos tàctils (Swipe en mòbil)
    let touchStartX = 0;
    stage.addEventListener('touchstart', (e) => {
      touchStartX = e.touches[0].clientX;
    }, { passive: true });

    stage.addEventListener('touchend', (e) => {
      const touchEndX = e.changedTouches[0].clientX;
      const diffX = touchStartX - touchEndX;
      if (Math.abs(diffX) > 40) {
        if (diffX > 0) {
          showSlide(currentIndex + 1);
        } else {
          showSlide(currentIndex - 1);
        }
        resetAutoPlay();
      }
    }, { passive: true });

    // Inicialització en posició 0
    showSlide(0);
    startAutoPlay();
  }

  // --- 11. REVELACIÓ SUAU DE SCROLL PER A TÍTOLS I TEXTOS ---
  function initScrollReveal() {
    const targets = document.querySelectorAll(
      '.section-title-bold, .section-tag-red, .bestsellers-title, .bestsellers-desc, ' +
      '.nosaltres-content-panel h2, .nosaltres-content-panel p, .reviews-title-center h2, ' +
      '.takeaway-title-main, .location-info-column h2, .menu-intro-header h1, .menu-intro-header p, ' +
      '.menu-category-section-divider, .combinats-section-title, .combinats-section-desc'
    );

    if (!('IntersectionObserver' in window)) {
      targets.forEach(el => el.classList.add('is-inview'));
      return;
    }

    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-inview');
          obs.unobserve(entry.target);
        }
      });
    }, {
      root: null,
      threshold: 0.1,
      rootMargin: '0px 0px -30px 0px'
    });

    targets.forEach(el => {
      el.classList.add('reveal-on-scroll');
      observer.observe(el);
    });
  }

  // Inicialització
  renderDishes();
  initScrollFrameAnimation();
  initBeforeAfterSlider();
  initCombinatsCarousel();
  initScrollReveal();

});
