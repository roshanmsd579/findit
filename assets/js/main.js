(function () {
  'use strict';

  const body = document.body;
  const toastContainer = document.getElementById('toast-container');

  function showToast(message, type) {
    if (!toastContainer) return;
    const colorMap = {
      success: 'text-bg-success',
      error: 'text-bg-danger',
      info: 'text-bg-primary',
      warning: 'text-bg-warning'
    };
    const toast = document.createElement('div');
    toast.className = `toast align-items-center border-0 ${colorMap[type] || colorMap.info}`;
    toast.role = 'alert';
    toast.ariaLive = 'assertive';
    toast.ariaAtomic = 'true';
    toast.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: 4000 });
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
  }
  window.showToast = showToast;

  function setTheme(theme) {
    if (theme === 'light') {
      body.classList.add('light-mode');
      localStorage.setItem('findit_theme', 'light');
    } else {
      body.classList.remove('light-mode');
      localStorage.setItem('findit_theme', 'dark');
    }
    const icon = document.getElementById('theme-icon');
    const label = document.getElementById('theme-label');
    if (icon) icon.textContent = theme === 'light' ? '☀️' : '🌙';
    if (label) label.textContent = theme === 'light' ? 'Light' : 'Dark';
  }

  const savedTheme = localStorage.getItem('findit_theme') || 'dark';
  setTheme(savedTheme);
  const toggleBtn = document.getElementById('theme-toggle');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      const current = body.classList.contains('light-mode') ? 'light' : 'dark';
      setTheme(current === 'light' ? 'dark' : 'light');
    });
  }

  document.querySelectorAll('[data-filter-group]').forEach((group) => {
    const targetSelector = group.getAttribute('data-target');
    const cards = targetSelector ? document.querySelectorAll(targetSelector) : [];
    group.querySelectorAll('.filter-tab').forEach((tab) => {
      tab.addEventListener('click', () => {
        group.querySelectorAll('.filter-tab').forEach((t) => t.classList.remove('active'));
        tab.classList.add('active');
        const type = tab.dataset.type || 'all';
        const category = tab.dataset.category || 'all';

        cards.forEach((card) => {
          const typeMatch = type === 'all' || card.dataset.type === type;
          const categoryMatch = category === 'all' || card.dataset.category === category;
          const show = typeMatch && categoryMatch;
          card.style.transition = 'opacity 0.2s ease';
          card.style.opacity = show ? '1' : '0';
          setTimeout(() => {
            card.parentElement.style.display = show ? '' : 'none';
          }, show ? 0 : 150);
        });
      });
    });
  });

  const searchInput = document.getElementById('search-input');
  if (searchInput) {
    const items = Array.from(document.querySelectorAll('#search-results .search-item'));
    const resultsCount = document.getElementById('results-count');
    const emptyState = document.getElementById('empty-state');
    const typeFilter = document.getElementById('type-filter');
    const categoryFilter = document.getElementById('category-filter');
    const locationFilter = document.getElementById('location-filter');
    const statusFilter = document.getElementById('status-filter');

    const runSearch = () => {
      const q = searchInput.value.toLowerCase().trim();
      const t = typeFilter ? typeFilter.value : 'all';
      const c = categoryFilter ? categoryFilter.value : 'all';
      const l = locationFilter ? locationFilter.value : 'all';
      const s = statusFilter ? statusFilter.value : 'all';
      let visible = 0;

      items.forEach((item) => {
        const text = item.textContent.toLowerCase();
        const okQ = q === '' || text.includes(q);
        const okT = t === 'all' || item.dataset.type === t;
        const okC = c === 'all' || item.dataset.category === c;
        const okL = l === 'all' || item.dataset.location === l;
        const okS = s === 'all' || item.dataset.status === s;
        const show = okQ && okT && okC && okL && okS;
        item.style.display = show ? '' : 'none';
        if (show) visible += 1;
      });

      if (resultsCount) resultsCount.textContent = `${visible} result(s)`;
      if (emptyState) emptyState.classList.toggle('d-none', visible > 0);
    };

    let debounce;
    searchInput.addEventListener('input', () => {
      clearTimeout(debounce);
      debounce = setTimeout(runSearch, 400);
    });
    [typeFilter, categoryFilter, locationFilter, statusFilter].forEach((el) => {
      if (el) el.addEventListener('change', runSearch);
    });

    const resetBtn = document.getElementById('reset-filters');
    if (resetBtn) {
      resetBtn.addEventListener('click', () => {
        searchInput.value = '';
        if (typeFilter) typeFilter.value = 'all';
        if (categoryFilter) categoryFilter.value = 'all';
        if (locationFilter) locationFilter.value = 'all';
        if (statusFilter) statusFilter.value = 'active';
        runSearch();
      });
    }
    runSearch();
  }

  const form = document.getElementById('create-report-form');
  if (form) {
    let currentStep = 1;
    const steps = Array.from(form.querySelectorAll('.form-step'));
    const stepDots = Array.from(form.querySelectorAll('.step-dot'));
    const typeInput = document.getElementById('type-input');
    const categoryInput = document.getElementById('category-input');

    const showStep = (n) => {
      currentStep = n;
      steps.forEach((step) => step.classList.toggle('d-none', Number(step.dataset.step) !== n));
      stepDots.forEach((dot) => {
        const stepNum = Number(dot.dataset.step);
        dot.classList.remove('active', 'done');
        if (stepNum < n) dot.classList.add('done');
        if (stepNum === n) dot.classList.add('active');
      });
    };

    const shakeInvalid = (el) => {
      el.classList.add('is-invalid', 'shake');
      setTimeout(() => el.classList.remove('shake'), 300);
    };

    const validateStep = (n) => {
      let valid = true;
      if (n === 1) {
        if (!typeInput.value) {
          valid = false;
          const firstType = document.querySelector('[data-type-card]');
          if (firstType) shakeInvalid(firstType);
        }
        if (!categoryInput.value) {
          valid = false;
          const firstCat = document.querySelector('[data-category-card]');
          if (firstCat) shakeInvalid(firstCat);
        }
        const title = document.getElementById('title-input');
        if (title && title.value.trim().length < 6) {
          valid = false;
          shakeInvalid(title);
        }
      }
      if (n === 2) {
        form.querySelectorAll('.required-step-2').forEach((field) => {
          if (!field.value.trim()) {
            valid = false;
            shakeInvalid(field);
          }
        });
      }
      return valid;
    };

    form.querySelectorAll('[data-type-card]').forEach((card) => {
      card.addEventListener('click', () => {
        const value = card.dataset.typeCard;
        typeInput.value = value;
        form.querySelectorAll('[data-type-card]').forEach((c) => c.classList.remove('sel-lost', 'sel-found'));
        card.classList.add(value === 'lost' ? 'sel-lost' : 'sel-found');
        document.querySelectorAll('.lost-only').forEach((x) => x.classList.toggle('d-none', value !== 'lost'));
      });
    });

    form.querySelectorAll('[data-category-card]').forEach((card) => {
      card.addEventListener('click', () => {
        categoryInput.value = card.dataset.categoryCard;
        form.querySelectorAll('[data-category-card]').forEach((c) => c.classList.remove('selected'));
        card.classList.add('selected');
      });
    });

    form.querySelectorAll('.next-step').forEach((btn) => {
      btn.addEventListener('click', () => {
        if (validateStep(currentStep)) showStep(currentStep + 1);
        else showToast('Please fill required fields.', 'warning');
      });
    });
    form.querySelectorAll('.prev-step').forEach((btn) => btn.addEventListener('click', () => showStep(currentStep - 1)));

    const rewardToggle = document.getElementById('reward-toggle');
    const rewardWrap = document.getElementById('reward-amount-wrap');
    if (rewardToggle && rewardWrap) {
      rewardToggle.addEventListener('change', () => rewardWrap.classList.toggle('d-none', !rewardToggle.checked));
    }

    const photoInput = document.getElementById('photo-input');
    const photoPreview = document.getElementById('photo-preview');
    if (photoInput && photoPreview) {
      photoInput.addEventListener('change', () => {
        const file = photoInput.files && photoInput.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = () => {
          photoPreview.src = String(reader.result);
          photoPreview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
      });
    }

    let stream = null;
    const startCamera = document.getElementById('start-camera');
    const captureBtn = document.getElementById('capture-image');
    const retakeBtn = document.getElementById('retake-image');
    const video = document.getElementById('camera-stream');
    const canvas = document.getElementById('camera-canvas');
    const webcamInput = document.getElementById('webcam-image-input');

    if (startCamera && captureBtn && retakeBtn && video && canvas && webcamInput) {
      startCamera.addEventListener('click', async () => {
        try {
          stream = await navigator.mediaDevices.getUserMedia({ video: true });
          video.srcObject = stream;
          video.classList.remove('d-none');
          captureBtn.classList.remove('d-none');
          retakeBtn.classList.add('d-none');
          showToast('Camera ready.', 'info');
        } catch (err) {
          showToast('Unable to access camera.', 'error');
        }
      });

      captureBtn.addEventListener('click', () => {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const data = canvas.toDataURL('image/jpeg', 0.9);
        webcamInput.value = data;
        if (photoPreview) {
          photoPreview.src = data;
          photoPreview.classList.remove('d-none');
        }
        captureBtn.classList.add('d-none');
        retakeBtn.classList.remove('d-none');
        if (stream) {
          stream.getTracks().forEach((track) => track.stop());
          video.classList.add('d-none');
        }
      });

      retakeBtn.addEventListener('click', () => {
        webcamInput.value = '';
        retakeBtn.classList.add('d-none');
        startCamera.click();
      });
    }

    showStep(1);
  }

  const mapEl = document.getElementById('map');
  if (mapEl && typeof L !== 'undefined') {
    const hasData = mapEl.dataset.lat && mapEl.dataset.lng;
    const map = L.map('map').setView([20.5937, 78.9629], hasData ? 12 : 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    if (hasData) {
      const lat = Number(mapEl.dataset.lat);
      const lng = Number(mapEl.dataset.lng);
      L.marker([lat, lng]).addTo(map);
      map.setView([lat, lng], 15);
    } else {
      let marker = null;
      const latInput = document.getElementById('lat-input');
      const lngInput = document.getElementById('lng-input');
      map.on('click', (e) => {
        if (marker) marker.remove();
        marker = L.marker(e.latlng).addTo(map);
        if (latInput) latInput.value = e.latlng.lat.toFixed(7);
        if (lngInput) lngInput.value = e.latlng.lng.toFixed(7);
      });
    }
  }

  const chatForm = document.getElementById('chat-form');
  const chatBox = document.getElementById('chat-box');
  if (chatForm && chatBox) {
    const messageInput = document.getElementById('chat-message');
    const reportId = chatForm.dataset.reportId;
    const receiverId = chatForm.dataset.receiverId;
    let lastMessageId = 0;
    chatBox.querySelectorAll('[data-message-id]').forEach((m) => {
      const id = Number(m.dataset.messageId || 0);
      if (id > lastMessageId) lastMessageId = id;
    });

    const appendMessage = (message, mine) => {
      const bubble = document.createElement('div');
      bubble.className = mine ? 'chat-bubble-mine' : 'chat-bubble-other';
      bubble.dataset.messageId = message.id;
      const safeText = String(message.message || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
      bubble.innerHTML = `${safeText}<div class="chat-time">${new Date(message.created_at).toLocaleString()} ${mine ? '✓' : ''}</div>`;
      chatBox.appendChild(bubble);
      lastMessageId = Math.max(lastMessageId, Number(message.id || 0));
      chatBox.scrollTop = chatBox.scrollHeight;
    };

    chatForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const message = messageInput.value.trim();
      if (!message) return;
      const fd = new FormData();
      fd.append('report_id', reportId);
      fd.append('receiver_id', receiverId);
      fd.append('message', message);
      try {
        const res = await fetch('api/send-message.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
          appendMessage({ id: data.id, message, created_at: new Date().toISOString() }, true);
          messageInput.value = '';
        }
      } catch (_err) {
        showToast('Message send failed.', 'error');
      }
    });

    messageInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        chatForm.dispatchEvent(new Event('submit'));
      }
    });

    const poll = async () => {
      try {
        const qs = new URLSearchParams({ report_id: reportId, with: receiverId, after: String(lastMessageId) });
        const res = await fetch(`api/get-messages.php?${qs.toString()}`);
        const data = await res.json();
        if (Array.isArray(data.messages)) {
          data.messages.forEach((m) => appendMessage(m, Number(m.sender_id) === Number(chatBox.dataset.myId || 0)));
        }
      } catch (_err) {
      }
    };

    setInterval(poll, 3000);
    chatBox.scrollTop = chatBox.scrollHeight;
  }

  const markReadBtn = document.getElementById('mark-notifications-read');
  if (markReadBtn) {
    markReadBtn.addEventListener('click', async () => {
      const fd = new FormData();
      const res = await fetch('api/mark-notifications.php', { method: 'POST', body: fd });
      const data = await res.json();
      if (data.success) {
        document.querySelectorAll('.notif-badge').forEach((badge) => (badge.textContent = '0'));
        showToast('Notifications marked as read.', 'success');
      }
    });
  }

  const verifyInput = document.getElementById('verify-code-input');
  const verifyBtn = document.getElementById('verify-code-btn');
  const verifyResult = document.getElementById('verify-result');
  if (verifyInput) {
    verifyInput.addEventListener('input', () => {
      verifyInput.value = verifyInput.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    });
  }
  if (verifyBtn && verifyInput) {
    verifyBtn.addEventListener('click', async () => {
      verifyBtn.disabled = true;
      verifyBtn.textContent = 'Verifying...';
      const fd = new FormData();
      fd.append('claim_id', verifyBtn.dataset.claimId || '0');
      fd.append('code', verifyInput.value.trim());
      const res = await fetch('api/verify-code.php', { method: 'POST', body: fd });
      const data = await res.json();
      verifyBtn.disabled = false;
      verifyBtn.textContent = 'Submit Code';
      if (data.success) {
        verifyResult.textContent = 'Code verified successfully.';
        verifyResult.className = 'small mt-2 text-success';
        showToast('Code accepted.', 'success');
      } else {
        verifyResult.textContent = data.message || 'Code verification failed.';
        verifyResult.className = 'small mt-2 text-danger';
      }
    });
  }

  document.querySelectorAll('.handover-confirm-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const fd = new FormData();
      fd.append('claim_id', btn.dataset.claimId || '0');
      fd.append('role', btn.dataset.role || '');
      btn.disabled = true;
      btn.textContent = 'Submitting...';
      const res = await fetch('api/confirm-handover.php', { method: 'POST', body: fd });
      const data = await res.json();
      btn.disabled = false;
      btn.textContent = btn.dataset.role === 'reporter' ? 'Reporter Confirms Receipt' : 'Claimant Confirms Handover';
      if (data.success) {
        showToast(data.both_confirmed ? 'Both confirmed. Report resolved.' : 'Confirmation saved.', 'success');
        if (data.both_confirmed) window.location.reload();
      }
    });
  });

  let pendingDeleteForm = null;
  const deleteModalEl = document.getElementById('deleteConfirmModal');
  const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
  if (deleteModalEl && confirmDeleteBtn) {
    const deleteModal = new bootstrap.Modal(deleteModalEl);
    document.querySelectorAll('.btn-delete').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        pendingDeleteForm = btn.closest('form');
        deleteModal.show();
      });
    });
    confirmDeleteBtn.addEventListener('click', () => {
      if (pendingDeleteForm) pendingDeleteForm.submit();
      deleteModal.hide();
    });
  }

  document.querySelectorAll('[data-rating-wrap]').forEach((wrap) => {
    const stars = wrap.querySelectorAll('.star');
    const hidden = document.getElementById('rating-value');
    let selected = Number(hidden ? hidden.value : 0);

    const paint = (hover) => {
      stars.forEach((star) => {
        const val = Number(star.dataset.value);
        star.classList.toggle('active', val <= selected);
        star.classList.toggle('hover', hover && val <= hover);
      });
    };

    stars.forEach((star) => {
      star.addEventListener('mouseenter', () => paint(Number(star.dataset.value)));
      star.addEventListener('mouseleave', () => paint(0));
      star.addEventListener('click', () => {
        selected = Number(star.dataset.value);
        if (hidden) hidden.value = String(selected);
        paint(0);
      });
    });
    paint(0);
  });

  document.querySelectorAll('.dispute-toggle').forEach((btn) => {
    btn.addEventListener('click', () => {
      const claim = btn.dataset.claim;
      const form = document.querySelector(`.dispute-form[data-claim="${claim}"]`);
      if (form) form.classList.toggle('d-none');
    });
  });
  document.querySelectorAll('.dispute-form').forEach((formEl) => {
    formEl.addEventListener('submit', async (e) => {
      e.preventDefault();
      const reason = formEl.querySelector('textarea[name="reason"]').value.trim();
      if (!reason) {
        showToast('Please add dispute reason.', 'warning');
        return;
      }
      const fd = new FormData();
      fd.append('claim_id', formEl.dataset.claim || '0');
      fd.append('reason', reason);
      const res = await fetch('api/raise-dispute.php', { method: 'POST', body: fd });
      const data = await res.json();
      if (data.success) {
        showToast('Dispute raised successfully.', 'success');
        formEl.classList.add('d-none');
      }
    });
  });

  const submitClaimBtn = document.getElementById('submit-claim-btn');
  if (submitClaimBtn) {
    submitClaimBtn.addEventListener('click', async () => {
      const answerInput = document.getElementById('claim-answer');
      const reportIdInput = document.getElementById('claim-report-id');
      if (!answerInput || !reportIdInput) return;
      submitClaimBtn.disabled = true;
      submitClaimBtn.textContent = 'Submitting...';
      const fd = new FormData();
      fd.append('report_id', reportIdInput.value);
      fd.append('secret_answer', answerInput.value.trim());
      const res = await fetch('api/submit-claim.php', { method: 'POST', body: fd });
      const data = await res.json();
      submitClaimBtn.disabled = false;
      submitClaimBtn.textContent = 'Submit Claim';
      if (data.success) {
        showToast('Claim submitted successfully.', 'success');
        setTimeout(() => window.location.reload(), 800);
      } else {
        showToast(data.message || 'Claim failed.', 'error');
      }
    });
  }

  const copyLinkBtn = document.getElementById('copy-link-btn');
  if (copyLinkBtn) {
    copyLinkBtn.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(copyLinkBtn.dataset.link || window.location.href);
        showToast('Link copied.', 'success');
      } catch (_err) {
        showToast('Unable to copy link.', 'warning');
      }
    });
  }

  const confettiTarget = document.querySelector('[data-confetti="1"]');
  if (confettiTarget) {
    for (let i = 0; i < 40; i += 1) {
      const conf = document.createElement('span');
      conf.style.position = 'fixed';
      conf.style.left = `${Math.random() * 100}vw`;
      conf.style.top = '-10px';
      conf.style.width = '8px';
      conf.style.height = '12px';
      conf.style.borderRadius = '2px';
      conf.style.background = ['#4f46e5', '#3ecf8e', '#f5c542', '#e8562a'][Math.floor(Math.random() * 4)];
      conf.style.zIndex = '2000';
      conf.style.transition = `transform ${1.8 + Math.random() * 1.6}s linear, opacity 2.4s`;
      document.body.appendChild(conf);
      requestAnimationFrame(() => {
        conf.style.transform = `translate(${(Math.random() - 0.5) * 160}px, ${window.innerHeight + 100}px) rotate(${Math.random() * 720}deg)`;
        conf.style.opacity = '0';
      });
      setTimeout(() => conf.remove(), 3000);
    }
  }
})();
