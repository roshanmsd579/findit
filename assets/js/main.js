(function () {
  function debounce(fn, delay) {
    let timer;
    return function (...args) {
      clearTimeout(timer);
      timer = setTimeout(() => fn.apply(this, args), delay);
    };
  }

  function setCardVisibility(card, visible) {
    if (visible) {
      card.classList.remove('is-hidden');
      card.style.display = '';
      return;
    }

    card.classList.add('is-hidden');
    setTimeout(() => {
      if (card.classList.contains('is-hidden')) {
        card.style.display = 'none';
      }
    }, 200);
  }

  function initFilterTabs() {
    const tabGroups = document.querySelectorAll('[data-filter-group]');
    if (!tabGroups.length) return;

    tabGroups.forEach((group) => {
      const tabs = group.querySelectorAll('.filter-tab');
      const targetSelector = group.getAttribute('data-target') || '.report-card';
      const cards = document.querySelectorAll(targetSelector);

      tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
          tabs.forEach((t) => t.classList.remove('active'));
          tab.classList.add('active');

          const type = tab.dataset.type || 'all';
          const category = tab.dataset.category || 'all';

          cards.forEach((card) => {
            const cardType = card.dataset.type || 'all';
            const cardCategory = card.dataset.category || 'all';
            const typeMatch = type === 'all' || cardType === type;
            const categoryMatch = category === 'all' || cardCategory === category;
            setCardVisibility(card, typeMatch && categoryMatch);
          });
        });
      });
    });
  }

  function initSearch() {
    const input = document.querySelector('#search-input');
    const cards = document.querySelectorAll('.search-card');
    if (!input || !cards.length) return;

    const resultCountEl = document.querySelector('#results-count');
    const emptyState = document.querySelector('#empty-state');

    const applySearch = debounce(() => {
      const keyword = input.value.trim().toLowerCase();
      let visibleCount = 0;

      cards.forEach((card) => {
        const haystack = [
          card.dataset.title || '',
          card.dataset.description || '',
          card.dataset.location || ''
        ]
          .join(' ')
          .toLowerCase();

        const matched = !keyword || haystack.includes(keyword);
        setCardVisibility(card, matched);
        if (matched) visibleCount += 1;
      });

      if (resultCountEl) {
        resultCountEl.textContent = String(visibleCount);
      }

      if (emptyState) {
        emptyState.classList.toggle('d-none', visibleCount !== 0);
      }
    }, 400);

    input.addEventListener('input', applySearch);
    applySearch();
  }

  function updateSteps(step, maxStep) {
    const dots = document.querySelectorAll('.step-dot');
    const labels = document.querySelectorAll('.step-label');

    dots.forEach((dot) => {
      const dotStep = Number(dot.dataset.step || 0);
      dot.classList.remove('active', 'done');
      if (dotStep < step) dot.classList.add('done');
      if (dotStep === step) dot.classList.add('active');
    });

    labels.forEach((label) => {
      const labelStep = Number(label.dataset.step || 0);
      label.classList.toggle('active', labelStep === step);
    });

    const progress = document.querySelector('#form-progress');
    if (progress) {
      const pct = Math.round((step / maxStep) * 100);
      progress.style.width = `${pct}%`;
      progress.setAttribute('aria-valuenow', String(pct));
    }
  }

  function validateStep(stepEl) {
    const requiredFields = stepEl.querySelectorAll('[required]');
    let allValid = true;

    requiredFields.forEach((field) => {
      const empty = !String(field.value || '').trim();
      field.classList.toggle('is-invalid-custom', empty);
      if (empty) allValid = false;
    });

    if (!allValid) {
      stepEl.classList.remove('shake');
      void stepEl.offsetWidth;
      stepEl.classList.add('shake');
    }

    return allValid;
  }

  function showStep(step, maxStep) {
    const steps = document.querySelectorAll('.form-step');
    steps.forEach((panel) => panel.classList.add('d-none'));
    const activePanel = document.querySelector(`#step-${step}`);
    if (activePanel) activePanel.classList.remove('d-none');
    updateSteps(step, maxStep);

    const prevBtn = document.querySelector('#btn-prev');
    const nextBtn = document.querySelector('#btn-next');
    const submitBtn = document.querySelector('#btn-submit');

    if (prevBtn) prevBtn.classList.toggle('d-none', step === 1);
    if (nextBtn) nextBtn.classList.toggle('d-none', step === maxStep);
    if (submitBtn) submitBtn.classList.toggle('d-none', step !== maxStep);
  }

  function initTypeCategoryCards() {
    const typeCards = document.querySelectorAll('.type-card[data-value]');
    const typeInput = document.querySelector('#report_type');
    typeCards.forEach((card) => {
      card.addEventListener('click', () => {
        typeCards.forEach((c) => c.classList.remove('selected-lost', 'selected-found'));
        const value = card.dataset.value;
        card.classList.add(value === 'found' ? 'selected-found' : 'selected-lost');
        if (typeInput) typeInput.value = value;
      });
    });

    const categoryCards = document.querySelectorAll('.category-card[data-value]');
    const categoryInput = document.querySelector('#report_category');
    categoryCards.forEach((card) => {
      card.addEventListener('click', () => {
        categoryCards.forEach((c) => c.classList.remove('selected'));
        card.classList.add('selected');
        if (categoryInput) categoryInput.value = card.dataset.value;
      });
    });
  }

  function initImagePreview() {
    const fileInput = document.querySelector('#report_image');
    const preview = document.querySelector('#image-preview');
    const uploadArea = document.querySelector('.upload-area');
    if (!fileInput || !preview) return;

    function renderPreview(file) {
      if (!file) return;
      const reader = new FileReader();
      reader.onload = (event) => {
        preview.src = event.target?.result || '';
        preview.classList.remove('d-none');
      };
      reader.readAsDataURL(file);
    }

    fileInput.addEventListener('change', () => renderPreview(fileInput.files?.[0]));

    if (uploadArea) {
      uploadArea.addEventListener('click', () => fileInput.click());
    }
  }

  function initCreateReportForm() {
    const form = document.querySelector('#create-report-form');
    if (!form) return;

    const maxStep = Number(form.dataset.maxStep || 4);
    let currentStep = 1;

    const nextBtn = document.querySelector('#btn-next');
    const prevBtn = document.querySelector('#btn-prev');

    showStep(currentStep, maxStep);
    initTypeCategoryCards();
    initImagePreview();

    form.addEventListener('input', (event) => {
      const target = event.target;
      if (target.classList.contains('is-invalid-custom')) {
        const empty = !String(target.value || '').trim();
        if (!empty) target.classList.remove('is-invalid-custom');
      }
    });

    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        const stepPanel = document.querySelector(`#step-${currentStep}`);
        if (!stepPanel || validateStep(stepPanel)) {
          currentStep = Math.min(maxStep, currentStep + 1);
          showStep(currentStep, maxStep);
        }
      });
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        currentStep = Math.max(1, currentStep - 1);
        showStep(currentStep, maxStep);
      });
    }

    form.addEventListener('submit', (event) => {
      const stepPanel = document.querySelector(`#step-${currentStep}`);
      if (!stepPanel || !validateStep(stepPanel)) {
        event.preventDefault();
        return;
      }

      const confirmed = window.confirm('Submit this report now?');
      if (!confirmed) {
        event.preventDefault();
      }
    });
  }

  function initMap() {
    const mapEl = document.querySelector('#map');
    if (!mapEl || typeof L === 'undefined') return;

    const lat = Number(mapEl.dataset.lat || 31.0064);
    const lng = Number(mapEl.dataset.lng || 75.7597);
    const label = mapEl.dataset.label || 'Reported location';

    const map = L.map('map').setView([lat, lng], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.marker([lat, lng]).addTo(map).bindPopup(label).openPopup();
  }

  document.addEventListener('DOMContentLoaded', () => {
    initFilterTabs();
    initSearch();
    initCreateReportForm();
    initMap();
  });
})();
