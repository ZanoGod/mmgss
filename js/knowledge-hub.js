  // Knowledge Hub: holiday data, card rendering, and accessible detail modal
    // Maps translation keys to folder names and image files
    const knowledgeHolidays = [
      {
        key: 'newYear',
        folder: 'January 1 元日',
        images: ['January 1 元日.jpg', '元日 2.jpg', '元日 3.jpg', '元日 4.jpg', '元日 5.jpg', '元日 6.jpg'],
        season: 'winter',
        seasonIcon: 'fa-snowflake',
        seasonLabel: 'Winter'
      },
      {
        key: 'comingOfAge',
        folder: 'January 12 成人の日',
        images: ['January 12 成人.jpg', '成人 .jpg', '成人 1.jpg', '成人 2.jpg', '成人 3.jpg', '成人 4.jpg', '成人 5.jpg', '成人 6.jpg', '成人 7.jpg'],
        season: 'winter',
        seasonIcon: 'fa-snowflake',
        seasonLabel: 'Winter'
      },
      {
        key: 'nationalFoundation',
        folder: 'February 11 建国記念の日',
        images: ['February 11 建国記念の日.jpg', '建国記念の日.jpg', '建国記念の日 1.jpg', '建国記念の日 2.jpg', '建国記念の日 3.jpg', '建国記念の日 4.jpg'],
        season: 'winter',
        seasonIcon: 'fa-snowflake',
        seasonLabel: 'Winter'
      },
      {
        key: 'emperorsBirthday',
        folder: 'February 23 天皇誕生日',
        images: ['February 23 天皇誕生日.jpg', '天皇誕生日.jpg', '天皇誕生日 1.jpg', '天皇誕生日 2.jpg', '天皇誕生日 3.jpg', '天皇誕生日 4.jpg'],
        season: 'winter',
        seasonIcon: 'fa-snowflake',
        seasonLabel: 'Winter'
      },
      {
        key: 'springEquinox',
        folder: 'March 20 春分の日',
        images: ['March 20 春分の日.jpg', '春分の日.jpg', '春分の日 1.jpg', '春分の日 2.jpg', '春分の日 3.jpg'],
        season: 'spring',
        seasonIcon: 'fa-seedling',
        seasonLabel: 'Spring'
      },
      {
        key: 'showaDay',
        folder: 'April 29 昭和の日',
        images: ['April 29 昭和の日.jpg', '昭和の日 1.jpg', '昭和の日.jpg'],
        season: 'spring',
        seasonIcon: 'fa-seedling',
        seasonLabel: 'Spring'
      },
      {
        key: 'constitutionDay',
        folder: 'May 3 憲法記念日',
        images: ['May 3 憲法記念日.jpg', '憲法記念日.jpg', '憲法記念日 1.jpg', '憲法記念日 2.jpg'],
        season: 'spring',
        seasonIcon: 'fa-seedling',
        seasonLabel: 'Spring'
      },
      {
        key: 'greeneryDay',
        folder: 'May 4 みどりの日',
        images: ['May 4 みどりの日.jpg', 'みどりの日.jpg', 'みどりの日 1.jpg', 'みどりの日 2.jpg', 'みどりの日 3.jpg', 'みどりの日 4.jpg', 'みどりの日 5.jpg', 'みどりの日 6.jpg', 'みどりの日 7.jpg'],
        season: 'spring',
        seasonIcon: 'fa-seedling',
        seasonLabel: 'Spring'
      },
      {
        key: 'childrensDay',
        folder: 'May 5 子供の日',
        images: ['May 5 子供の日.jpg', '子供の日.jpg', '子供の日 1.jpg', '子供の日 2.jpg', '子供の日 3.jpg', '子供の日 4.jpg', '子供の日 5.jpg'],
        season: 'spring',
        seasonIcon: 'fa-seedling',
        seasonLabel: 'Spring'
      },
      {
        key: 'marineDay',
        folder: 'July 20 海の日',
        images: ['July 20 海の日.jpg', '海の日.jpg', '海の日 1.jpg', '海の日 2.jpg', '海の日 3.jpg', '海の日 4.jpg', '海の日 5.jpg'],
        season: 'summer',
        seasonIcon: 'fa-water',
        seasonLabel: 'Summer'
      },
      {
        key: 'mountainDay',
        folder: 'August 11 山の日',
        images: ['August 11 山の日.jpg', '山の日.jpg', '山の日 1.jpg', '山の日 2.jpg', '山の日 3.jpg', '山の日 4.jpg', '山の日 5.jpg', '山の日 6.jpg', '山の日 7.jpg'],
        season: 'summer',
        seasonIcon: 'fa-mountain',
        seasonLabel: 'Summer'
      },
      {
        key: 'respectForAged',
        folder: 'September 21 敬老の日',
        images: ['September 21 敬老の日.jpg', '敬老の日.jpg', '敬老の日 1.jpg', '敬老の日 2.jpg', '敬老の日 3.jpg', '敬老の日 4.jpg'],
        season: 'autumn',
        seasonIcon: 'fa-leaf',
        seasonLabel: 'Autumn'
      },
      {
        key: 'citizensHoliday',
        folder: 'September 22 国民の休日',
        images: ['September 22 国民の休日.jpg', '国民の休日.jpg', '国民の休日 1.jpg', '国民の休日 2.jpg'],
        season: 'autumn',
        seasonIcon: 'fa-leaf',
        seasonLabel: 'Autumn'
      },
      {
        key: 'autumnEquinox',
        folder: 'Sep 23 秋分の日',
        images: ['shubun-autumn-equinox-day-japan.webp', 'sep 23_1.jpg', 'sep 23_2.jpg', 'shubun02.jpg', 'sep 23_3.webp'],
        season: 'autumn',
        seasonIcon: 'fa-leaf',
        seasonLabel: 'Autumn'
      },
      {
        key: 'sportsDay',
        folder: 'Oct12 スポーツの日',
        images: ['oct12_1.jpg', 'oct12_2.jpg', 'oct12_3.jpg', 'oct12_4.jpg'],
        season: 'autumn',
        seasonIcon: 'fa-leaf',
        seasonLabel: 'Autumn'
      },
      {
        key: 'cultureDay',
        folder: 'Nov 3 文化の日',
        images: ['sewing.jpg', 'nov3_1.jpg', 'nov3_2.jpg', 'nov3_3.jpg', 'nov3_4.jpg', 'nov3_5.jpg'],
        season: 'autumn',
        seasonIcon: 'fa-leaf',
        seasonLabel: 'Autumn'
      },
      {
        key: 'laborThanksgiving',
        folder: 'Nov 23 勤労感謝の日',
        images: ['thanks-offerings-takayama-matsuri.jpg', 'Nov23_1.jpg', 'Nov23_2.jpg', 'Nov23_3.jpg', 'Nov23_4.jpg'],
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

    const t = (key) => {
      const fn = window.MMGSSI18n && window.MMGSSI18n.t;
      if (typeof fn === 'function') { try { const value = fn(key); if (value && value !== key) return value; } catch (_) {} }
      return key;
    };
    const imageUrl = (holiday, file) => `assets/images/Activities/knowledgeSection/${encodeURIComponent(holiday.folder)}/${encodeURIComponent(file)}`;
    let activeHoliday = null;
    let activeImage = 0;

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
        const imgs = holiday.images.map((file, i) => `<img src="${imageUrl(holiday,file)}" alt="${t(nameKey)} — image ${i+1}" ${i ? 'loading="lazy"' : 'loading="eager"'}>`).join('');
        return `<article class="knowledge-card reveal" data-holiday-key="${holiday.key}">
          <div class="card-media"><div class="knowledge-card-carousel">${imgs}</div>
            <div class="holiday-badge"><span class="japanese-name" data-i18n="${japaneseKey}">${t(japaneseKey)}</span><span data-i18n="${dateKey}">${t(dateKey)}</span></div>
          </div><div class="card-content"><div class="card-title-row"><h3 class="card-title" data-i18n="${nameKey}">${t(nameKey)}</h3><time class="card-date" data-i18n="${dateKey}">${t(dateKey)}</time></div>
          <p class="card-description" data-i18n="${descKey}">${t(descKey)}</p><div class="card-footer"><span class="card-season"><i class="fas ${holiday.seasonIcon}" aria-hidden="true"></i><span>${season}</span></span>
          <button type="button" class="card-view-btn" data-holiday-key="${holiday.key}" aria-label="View ${t(nameKey)} details"><i class="bi bi-eye" aria-hidden="true"></i><span>View</span></button></div></div></article>`;
      }).join('');
      grid.querySelectorAll('.reveal').forEach(el => el.classList.add('show'));
    }

    function ensureModal() {
      let modal = document.getElementById('holidayModal');
      if (modal) return modal;
      modal = document.createElement('div');
      modal.id = 'holidayModal'; modal.className = 'holiday-modal';
      modal.innerHTML = `<div class="holiday-modal-backdrop" data-close-modal="true"></div><section class="holiday-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="holidayModalTitle" tabindex="-1">
        <button class="holiday-modal-close" type="button" aria-label="Close dialog" data-close-modal="true"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        <div class="holiday-modal-content"><div class="holiday-modal-media"><img id="holidayModalImage" alt=""><button type="button" class="holiday-image-nav prev" aria-label="Previous image">‹</button><button type="button" class="holiday-image-nav next" aria-label="Next image">›</button><div class="holiday-image-count" id="holidayImageCount"></div></div>
        <div class="holiday-modal-body"><div class="holiday-modal-header"><span class="holiday-modal-badge" id="modalJapaneseName"></span><time id="modalDate"></time></div><h2 id="holidayModalTitle" class="holiday-modal-title"></h2><div class="holiday-modal-meta"><span class="holiday-modal-season" id="modalSeason"><i class="fas" aria-hidden="true"></i><span></span></span></div><div class="holiday-modal-divider"></div><p id="modalDescription" class="holiday-modal-description"></p><div class="holiday-modal-thumbs" id="holidayModalThumbs"></div></div></div></section>`;
      document.body.appendChild(modal);
      modal.addEventListener('click', e => { if (e.target.closest('[data-close-modal="true"]')) closeModal(); });
      modal.querySelector('.prev').addEventListener('click', () => showImage(activeImage - 1));
      modal.querySelector('.next').addEventListener('click', () => showImage(activeImage + 1));
      document.addEventListener('keydown', e => {
        if (!modal.classList.contains('open')) return;
        if (e.key === 'Escape') closeModal();
        if (e.key === 'ArrowLeft') showImage(activeImage - 1);
        if (e.key === 'ArrowRight') showImage(activeImage + 1);
      });
      return modal;
    }
    function showImage(index) {
      if (!activeHoliday) return;
      activeImage = (index + activeHoliday.images.length) % activeHoliday.images.length;
      const modal = document.getElementById('holidayModal');
      const file = activeHoliday.images[activeImage];
      const img = modal.querySelector('#holidayModalImage');
      img.onerror = () => { img.removeAttribute('src'); img.alt = 'Image unavailable — restore the Knowledge Hub image assets'; img.style.objectFit = 'contain'; };
      img.onload = () => { img.style.objectFit = 'cover'; };
      img.src = imageUrl(activeHoliday, file);
      img.alt = `${t(`activities.knowledge.holidays.${activeHoliday.key}.name`)} — image ${activeImage + 1}`;
      modal.querySelector('#holidayImageCount').textContent = `${activeImage + 1} / ${activeHoliday.images.length}`;
      modal.querySelectorAll('.holiday-modal-thumb').forEach((b,i) => b.classList.toggle('active', i === activeImage));
    }
    function openHolidayModal(key) {
      activeHoliday = knowledgeHolidays.find(h => h.key === key);
      if (!activeHoliday) return;
      activeImage = 0;
      const modal = ensureModal();
      const nameKey = `activities.knowledge.holidays.${key}.name`, jpKey = `activities.knowledge.holidays.${key}.japanese`, dateKey = `activities.knowledge.holidays.${key}.date`, descKey = `activities.knowledge.holidays.${key}.description`;
      modal.querySelector('#modalJapaneseName').textContent = t(jpKey);
      modal.querySelector('#modalDate').textContent = t(dateKey);
      modal.querySelector('#holidayModalTitle').textContent = t(nameKey);
      modal.querySelector('#modalDescription').textContent = t(descKey);
      const season = seasonLabels[(document.documentElement.lang || 'en').slice(0,2)]?.[activeHoliday.season] || activeHoliday.seasonLabel;
      modal.querySelector('#modalSeason i').className = `fas ${activeHoliday.seasonIcon}`;
      modal.querySelector('#modalSeason span').textContent = season;
      modal.querySelector('#holidayModalThumbs').innerHTML = activeHoliday.images.map((file,i) => `<button type="button" class="holiday-modal-thumb ${i===0?'active':''}" data-image-index="${i}" aria-label="Show image ${i+1}"><img src="${imageUrl(activeHoliday,file)}" alt=""></button>`).join('');
      modal.querySelector('#holidayModalThumbs').onclick = e => { const b=e.target.closest('[data-image-index]'); if(b) showImage(Number(b.dataset.imageIndex)); };
      showImage(0);
      modal.classList.add('open');
      // Explicit inline visibility prevents older/duplicate stylesheet rules from hiding the dialog.
      modal.style.display = 'flex';
      modal.style.visibility = 'visible';
      modal.style.opacity = '1';
      modal.style.pointerEvents = 'auto';
      document.body.classList.add('modal-open');
      modal.querySelector('.holiday-modal-close').focus();
    }
    function closeModal() { const modal=document.getElementById('holidayModal'); if(!modal) return; modal.classList.remove('open'); modal.style.display='none'; modal.style.visibility='hidden'; modal.style.opacity='0'; modal.style.pointerEvents='none'; document.body.classList.remove('modal-open'); }

    // Delegation is attached once to the document, so it survives card re-rendering and language changes.
    document.addEventListener('click', event => {
      const button = event.target.closest('.card-view-btn[data-holiday-key]');
      if (!button) return;
      event.preventDefault(); openHolidayModal(button.dataset.holidayKey);
    });
    document.addEventListener('i18n:ready', renderKnowledgeCards);
    document.addEventListener('i18n:languagechanged', renderKnowledgeCards);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', renderKnowledgeCards, { once: true });
    else renderKnowledgeCards();
