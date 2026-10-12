// Knowledge Hub: holiday data, card rendering, and accessible detail modal using premium-carousel component
// All image filenames and folders use English names for production server compatibility
(() => {
  "use strict";

  // Maps translation keys to English folder names and image files
  const knowledgeHolidays = [
    { 
      key: 'newYear', 
      folder: 'January-1-New-Year', 
      images: ['new-year-1.jpg', 'new-year-2.jpg', 'new-year-3.jpg', 'new-year-4.jpg', 'new-year-5.jpg', 'new-year-6.jpg'], 
      season: 'winter', 
      seasonIcon: 'fa-snowflake', 
      seasonLabel: 'Winter' 
    },
    { 
      key: 'comingOfAge', 
      folder: 'January-12-Coming-of-Age-Day', 
      images: ['coming-of-age-1.jpg', 'coming-of-age-2.jpg', 'coming-of-age-3.jpg', 'coming-of-age-4.jpg', 'coming-of-age-5.jpg', 'coming-of-age-6.jpg', 'coming-of-age-7.jpg', 'coming-of-age-8.jpg', 'coming-of-age-9.jpg'], 
      season: 'winter', 
      seasonIcon: 'fa-snowflake', 
      seasonLabel: 'Winter' 
    },
    { 
      key: 'nationalFoundation', 
      folder: 'February-11-National-Foundation-Day', 
      images: ['national-foundation-1.jpg', 'national-foundation-2.jpg', 'national-foundation-3.jpg', 'national-foundation-4.jpg', 'national-foundation-5.jpg', 'national-foundation-6.jpg'], 
      season: 'winter', 
      seasonIcon: 'fa-snowflake', 
      seasonLabel: 'Winter' 
    },
    { 
      key: 'emperorsBirthday', 
      folder: 'February-23-Emperors-Birthday', 
      images: ['emperors-birthday-1.jpg', 'emperors-birthday-2.jpg', 'emperors-birthday-3.jpg', 'emperors-birthday-4.jpg', 'emperors-birthday-5.jpg', 'emperors-birthday-6.jpg'], 
      season: 'winter', 
      seasonIcon: 'fa-snowflake', 
      seasonLabel: 'Winter' 
    },
    { 
      key: 'springEquinox', 
      folder: 'March-20-Spring-Equinox', 
      images: ['spring-equinox-1.jpg', 'spring-equinox-2.jpg', 'spring-equinox-3.jpg', 'spring-equinox-4.jpg', 'spring-equinox-5.jpg'], 
      season: 'spring', 
      seasonIcon: 'fa-seedling', 
      seasonLabel: 'Spring' 
    },
    { 
      key: 'showaDay', 
      folder: 'April-29-Showa-Day', 
      images: ['showa-day-1.jpg', 'showa-day-2.jpg', 'showa-day-3.jpg'], 
      season: 'spring', 
      seasonIcon: 'fa-seedling', 
      seasonLabel: 'Spring' 
    },
    { 
      key: 'constitutionDay', 
      folder: 'May-3-Constitution-Memorial-Day', 
      images: ['constitution-day-1.jpg', 'constitution-day-2.jpg', 'constitution-day-3.jpg', 'constitution-day-4.jpg'], 
      season: 'spring', 
      seasonIcon: 'fa-seedling', 
      seasonLabel: 'Spring' 
    },
    { 
      key: 'greeneryDay', 
      folder: 'May-4-Greenery-Day', 
      images: ['greenery-day-1.jpg', 'greenery-day-2.jpg', 'greenery-day-3.jpg', 'greenery-day-4.jpg', 'greenery-day-5.jpg', 'greenery-day-6.jpg', 'greenery-day-7.jpg', 'greenery-day-8.jpg', 'greenery-day-9.jpg'], 
      season: 'spring', 
      seasonIcon: 'fa-seedling', 
      seasonLabel: 'Spring' 
    },
    { 
      key: 'childrensDay', 
      folder: 'May-5-Childrens-Day', 
      images: ['childrens-day-1.jpg', 'childrens-day-2.jpg', 'childrens-day-3.jpg', 'childrens-day-4.jpg', 'childrens-day-5.jpg', 'childrens-day-6.jpg', 'childrens-day-7.jpg'], 
      season: 'spring', 
      seasonIcon: 'fa-seedling', 
      seasonLabel: 'Spring' 
    },
    { 
      key: 'marineDay', 
      folder: 'July-20-Marine-Day', 
      images: ['marine-day-1.jpg', 'marine-day-2.jpg', 'marine-day-3.jpg', 'marine-day-4.jpg', 'marine-day-5.jpg', 'marine-day-6.jpg', 'marine-day-7.jpg'], 
      season: 'summer', 
      seasonIcon: 'fa-water', 
      seasonLabel: 'Summer' 
    },
    { 
      key: 'mountainDay', 
      folder: 'August-11-Mountain-Day', 
      images: ['mountain-day-1.jpg', 'mountain-day-2.jpg', 'mountain-day-3.jpg', 'mountain-day-4.jpg', 'mountain-day-5.jpg', 'mountain-day-6.jpg', 'mountain-day-7.jpg', 'mountain-day-8.jpg', 'mountain-day-9.jpg'], 
      season: 'summer', 
      seasonIcon: 'fa-mountain', 
      seasonLabel: 'Summer' 
    },
    { 
      key: 'respectForAged', 
      folder: 'September-21-Respect-for-the-Aged-Day', 
      images: ['respect-for-aged-1.jpg', 'respect-for-aged-2.jpg', 'respect-for-aged-3.jpg', 'respect-for-aged-4.jpg', 'respect-for-aged-5.jpg', 'respect-for-aged-6.jpg'], 
      season: 'autumn', 
      seasonIcon: 'fa-leaf', 
      seasonLabel: 'Autumn' 
    },
    { 
      key: 'citizensHoliday', 
      folder: 'September-22-Citizens-Holiday', 
      images: ['citizens-holiday-1.jpg', 'citizens-holiday-2.jpg', 'citizens-holiday-3.jpg', 'citizens-holiday-4.jpg'], 
      season: 'autumn', 
      seasonIcon: 'fa-leaf', 
      seasonLabel: 'Autumn' 
    },
    { 
      key: 'autumnEquinox', 
      folder: 'September-23-Autumn-Equinox', 
      images: ['autumn-equinox-1.webp', 'autumn-equinox-2.jpg', 'autumn-equinox-3.jpg', 'autumn-equinox-4.jpg', 'autumn-equinox-5.webp'], 
      season: 'autumn', 
      seasonIcon: 'fa-leaf', 
      seasonLabel: 'Autumn' 
    },
    { 
      key: 'sportsDay', 
      folder: 'October-12-Sports-Day', 
      images: ['sports-day-1.jpg', 'sports-day-2.jpg', 'sports-day-3.jpg', 'sports-day-4.jpg'], 
      season: 'autumn', 
      seasonIcon: 'fa-leaf', 
      seasonLabel: 'Autumn' 
    },
    { 
      key: 'cultureDay', 
      folder: 'November-3-Culture-Day', 
      images: ['culture-day-1.jpg', 'culture-day-2.jpg', 'culture-day-3.jpg', 'culture-day-4.jpg', 'culture-day-5.jpg', 'culture-day-6.jpg'], 
      season: 'autumn', 
      seasonIcon: 'fa-leaf', 
      seasonLabel: 'Autumn' 
    },
    { 
      key: 'laborThanksgiving', 
      folder: 'November-23-Labor-Thanksgiving-Day', 
      images: ['labor-thanksgiving-1.jpg', 'labor-thanksgiving-2.jpg', 'labor-thanksgiving-3.jpg', 'labor-thanksgiving-4.jpg', 'labor-thanksgiving-5.jpg'], 
      season: 'autumn', 
      seasonIcon: 'fa-leaf', 
      seasonLabel: 'Autumn' 
    }
  ];

  // Season labels for i18n
  const seasonLabels = {
    en: { winter: 'Winter', spring: 'Spring', summer: 'Summer', autumn: 'Autumn' },
    ja: { winter: '冬', spring: '春', summer: '夏', autumn: '秋' }
  };

  // Translation helper
  const t = (key) => {
    const fn = window.MMGSSI18n && window.MMGSSI18n.t;
    if (typeof fn === 'function') {
      try {
        const value = fn(key);
        if (value && value !== key) return value;
      } catch (_) {}
    }
    return key;
  };

  // Image URL builder
  // Resolve every image from the page URL, not the current directory of a script.
  // Each folder/file is encoded separately so special characters remain valid.
  const imageUrl = (holiday, file) => {
    const path = [
      'assets',
      'images',
      'Activities',
      'knowledgeSection',
      holiday.folder,
      file
    ].map((part, index) => index < 4 ? part : encodeURIComponent(part)).join('/');
    return new URL(path, document.baseURI).href;
  };

  // Render knowledge cards with premium-carousel
  function renderKnowledgeCards() {
    const grid = document.getElementById('knowledgeGrid');
    if (!grid) return;
    
    const lang = (document.documentElement.lang || 'en').slice(0, 2);
    
    grid.innerHTML = knowledgeHolidays.map((holiday, index) => {
      const nameKey = `activities.knowledge.holidays.${holiday.key}.name`;
      const japaneseKey = `activities.knowledge.holidays.${holiday.key}.japanese`;
      const dateKey = `activities.knowledge.holidays.${holiday.key}.date`;
      const descKey = `activities.knowledge.holidays.${holiday.key}.description`;
      const season = seasonLabels[lang]?.[holiday.season] || holiday.seasonLabel;
      
      // Create carousel images as direct children for premium-carousel component
      const carouselImages = holiday.images.map((file, i) => 
        `<img src="${imageUrl(holiday, file)}" alt="${t(nameKey)} — image ${i+1}" ${i === 0 ? 'loading="eager"' : 'loading="lazy"'} data-caption="">`
      ).join('');

      return `
        <article class="knowledge-card reveal" style="transition-delay: ${index * 40}ms" data-holiday-key="${holiday.key}">
          <div class="card-media">
            <premium-carousel autoplay interval="6000" aspect="16/10" loop>
              ${carouselImages}
            </premium-carousel>
            <div class="holiday-badge">
              <span class="japanese-name" data-i18n="${japaneseKey}">${t(japaneseKey)}</span>
              <span data-i18n="${dateKey}">${t(dateKey)}</span>
            </div>
          </div>
          <div class="card-content">
            <div class="card-title-row">
              <h3 class="card-title" data-i18n="${nameKey}">${t(nameKey)}</h3>
              <time class="card-date" data-i18n="${dateKey}">${t(dateKey)}</time>
            </div>
            <p class="card-description" data-i18n="${descKey}">${t(descKey)}</p>
            <div class="card-footer">
              <span class="card-season">
                <i class="fas ${holiday.seasonIcon}" aria-hidden="true"></i>
                <span>${season}</span>
              </span>
              <button type="button" class="card-view-btn" data-holiday-key="${holiday.key}" aria-label="${t(nameKey)} details">
                <i class="bi bi-eye" aria-hidden="true"></i>
                <span data-i18n="activities.knowledge.view">View</span>
              </button>
            </div>
          </div>
        </article>
      `;
    }).join('');

    // Initialize premium-carousel components after DOM insertion
    setTimeout(() => {
      grid.querySelectorAll('premium-carousel').forEach(el => {
        if (!el.initialized) {
          el.connectedCallback();
        }
      });

      // Trigger reveal animations
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('show');
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
      
      grid.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    }, 0);
  }

  // Modal management
  let modal = null;

  function ensureModal() {
    if (modal) return modal;
    
    modal = document.createElement('div');
    modal.id = 'holidayModal';
    modal.className = 'holiday-modal';
    modal.innerHTML = `
      <div class="holiday-modal-backdrop" tabindex="-1"></div>
      <div class="holiday-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="holidayModalTitle">
        <button class="holiday-modal-close" type="button" aria-label="Close">
          <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
        <div class="holiday-modal-content">
          <div class="holiday-modal-media">
            <premium-carousel autoplay interval="5000" aspect="16/9" loop id="modalCarousel"></premium-carousel>
          </div>
          <div class="holiday-modal-body">
            <div class="holiday-modal-header">
              <span class="holiday-modal-badge" id="modalJapaneseName"></span>
              <time id="modalDate"></time>
            </div>
            <h2 id="holidayModalTitle" class="holiday-modal-title"></h2>
            <div class="holiday-modal-meta">
              <span class="holiday-modal-season" id="modalSeason">
                <i class="fas" aria-hidden="true"></i>
                <span></span>
              </span>
            </div>
            <div class="holiday-modal-divider"></div>
            <p class="holiday-modal-description" id="modalDescription"></p>
          </div>
        </div>
      </div>
    `;
    document.body.appendChild(modal);

    // Close handlers
    const closeModal = () => {
      modal.classList.remove('open');
      document.body.classList.remove('modal-open');
      // Pause carousel
      const carousel = modal.querySelector('premium-carousel');
      if (carousel && carousel.pause) carousel.pause();
    };

    modal.querySelector('.holiday-modal-close').addEventListener('click', closeModal);
    modal.querySelector('.holiday-modal-backdrop').addEventListener('click', closeModal);
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
    });

    return modal;
  }

  function openHolidayModal(holidayKey) {
    const holiday = knowledgeHolidays.find(h => h.key === holidayKey);
    if (!holiday) return;

    const currentLang = document.documentElement.lang || 'en';
    const nameKey = `activities.knowledge.holidays.${holiday.key}.name`;
    const japaneseKey = `activities.knowledge.holidays.${holiday.key}.japanese`;
    const dateKey = `activities.knowledge.holidays.${holiday.key}.date`;
    const descKey = `activities.knowledge.holidays.${holiday.key}.description`;
    const seasonLabel = seasonLabels[currentLang.slice(0,2)]?.[holiday.season] || holiday.seasonLabel;

    // Build carousel images for modal
    const carouselImages = holiday.images.map((file, i) => 
      `<img src="${imageUrl(holiday, file)}" alt="${t(nameKey)} — image ${i+1}" ${i === 0 ? 'loading="eager"' : 'loading="lazy"'} data-caption="">`
    ).join('');

    ensureModal();

    // Update modal content
    modal.querySelector('#modalJapaneseName').textContent = t(japaneseKey);
    modal.querySelector('#modalJapaneseName').dataset.i18n = japaneseKey;
    modal.querySelector('#modalDate').textContent = t(dateKey);
    modal.querySelector('#modalDate').dataset.i18n = dateKey;
    modal.querySelector('#holidayModalTitle').textContent = t(nameKey);
    modal.querySelector('#holidayModalTitle').dataset.i18n = nameKey;
    modal.querySelector('#modalDescription').textContent = t(descKey);
    modal.querySelector('#modalDescription').dataset.i18n = descKey;
    
    const seasonIcon = modal.querySelector('.holiday-modal-season i');
    seasonIcon.className = `fas ${holiday.seasonIcon}`;
    modal.querySelector('.holiday-modal-season span').textContent = seasonLabel;

    // Replace the custom element instead of manually re-running connectedCallback.
    // Custom elements initialize automatically when connected; forcing a second
    // initialization after render has removed the original <img> nodes causes an
    // empty carousel and the black panel seen in the modal.
    const oldCarousel = modal.querySelector('#modalCarousel');
    const carouselEl = document.createElement('premium-carousel');
    carouselEl.id = 'modalCarousel';
    carouselEl.setAttribute('aspect', '16/9');
    carouselEl.setAttribute('autoplay', '');
    carouselEl.setAttribute('interval', '5000');
    carouselEl.setAttribute('loop', '');
    carouselEl.innerHTML = carouselImages;
    oldCarousel.replaceWith(carouselEl);

    // Show modal
    requestAnimationFrame(() => {
      modal.classList.add('open');
      document.body.classList.add('modal-open');
      modal.querySelector('.holiday-modal-close').focus();
    });
  }

  // Click delegation for view buttons (works after re-renders)
  document.addEventListener('click', (event) => {
    const button = event.target.closest('.card-view-btn[data-holiday-key]');
    if (!button) return;
    event.preventDefault();
    openHolidayModal(button.dataset.holidayKey);
  });

  // Initialize on i18n ready
  document.addEventListener('i18n:ready', renderKnowledgeCards);
  document.addEventListener('i18n:languagechanged', renderKnowledgeCards);
  
  // Fallback if already loaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      if (window.MMGSSI18n?.ready()) renderKnowledgeCards();
    }, { once: true });
  } else if (window.MMGSSI18n?.ready()) {
    renderKnowledgeCards();
  }
})();