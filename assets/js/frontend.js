/**
 * WP Lead Booster — Native Vanilla JS (Zero jQuery, 100/100 Core Web Vitals)
 */
document.addEventListener('DOMContentLoaded', () => {
  const data = window.wplbData || {};

  // 1. GESTIÓN DEL BOTÓN Y MODAL DE WHATSAPP
  const triggerBtn = document.getElementById('wplb-trigger-btn');
  const chatBox = document.getElementById('wplb-chat-box');
  const closeBoxBtn = document.getElementById('wplb-box-close');
  const startChatBtn = document.getElementById('wplb-start-chat');

  if (triggerBtn && chatBox) {
    triggerBtn.addEventListener('click', () => {
      const isOpen = chatBox.classList.contains('is-open');
      if (isOpen) {
        chatBox.classList.remove('is-open');
        chatBox.setAttribute('aria-hidden', 'true');
      } else {
        chatBox.classList.add('is-open');
        chatBox.setAttribute('aria-hidden', 'false');
      }
    });

    if (closeBoxBtn) {
      closeBoxBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        chatBox.classList.remove('is-open');
        chatBox.setAttribute('aria-hidden', 'true');
      });
    }

    if (startChatBtn && data.phone) {
      startChatBtn.addEventListener('click', () => {
        sendLeadEvent('whatsapp_click', 'chat_box');
        const cleanPhone = data.phone.replace(/[^0-9]/g, '');
        const message = encodeURIComponent(data.preset || 'Hola, me gustaría más información.');
        const whatsappUrl = `https://api.whatsapp.com/send?phone=${cleanPhone}&text=${message}`;
        window.open(whatsappUrl, '_blank', 'noopener,noreferrer');
      });
    }
  }

  // 2. GESTIÓN DEL BANNER DE URGENCIA Y TEMPORIZADOR
  const bar = document.getElementById('wplb-announcement-bar');
  const barClose = document.getElementById('wplb-bar-close');
  const barCta = document.getElementById('wplb-bar-cta');

  if (bar) {
    // Si el usuario ya lo cerró en las últimas 24h, no mostrarlo
    if (localStorage.getItem('wplb_bar_dismissed')) {
      bar.style.display = 'none';
    }

    if (barClose) {
      barClose.addEventListener('click', () => {
        bar.style.display = 'none';
        localStorage.setItem('wplb_bar_dismissed', 'true');
      });
    }

    if (barCta) {
      barCta.addEventListener('click', () => {
        sendLeadEvent('banner_cta_click', 'announcement_bar');
        // Si WhatsApp está activo, abrir el modal o abrir chat directo
        if (chatBox) {
          chatBox.classList.add('is-open');
          chatBox.setAttribute('aria-hidden', 'false');
        } else if (data.phone) {
          const cleanPhone = data.phone.replace(/[^0-9]/g, '');
          const message = encodeURIComponent(data.preset || 'Hola, vengo desde la oferta web.');
          window.open(`https://api.whatsapp.com/send?phone=${cleanPhone}&text=${message}`, '_blank');
        }
      });
    }

    // Temporizador regresivo persistente
    if (data.countdown) {
      initCountdown(data.bannerHours || 24);
    }
  }

  function initCountdown(hours) {
    const hoursEl = document.getElementById('wplb-hours');
    const minsEl = document.getElementById('wplb-mins');
    const secsEl = document.getElementById('wplb-secs');

    if (!hoursEl || !minsEl || !secsEl) return;

    let targetTime = localStorage.getItem('wplb_timer_target');
    if (!targetTime) {
      targetTime = Date.now() + (hours * 3600 * 1000);
      localStorage.setItem('wplb_timer_target', targetTime);
    }

    function updateTimer() {
      const remaining = Math.max(0, targetTime - Date.now());
      if (remaining <= 0) {
        // Reiniciar ciclo de urgencia
        targetTime = Date.now() + (hours * 3600 * 1000);
        localStorage.setItem('wplb_timer_target', targetTime);
      }

      const totalSecs = Math.floor(remaining / 1000);
      const h = Math.floor(totalSecs / 3600);
      const m = Math.floor((totalSecs % 3600) / 60);
      const s = totalSecs % 60;

      hoursEl.textContent = String(h).padStart(2, '0');
      minsEl.textContent = String(m).padStart(2, '0');
      secsEl.textContent = String(s).padStart(2, '0');
    }

    updateTimer();
    setInterval(updateTimer, 1000);
  }

  // 3. TRACKING AUTOMÁTICO DE ANALÍTICA (GA4 & Meta Pixel)
  function sendLeadEvent(action, origin) {
    // Google Analytics 4 (gtag.js)
    if (data.trackGA4 && typeof window.gtag === 'function') {
      window.gtag('event', 'generate_lead', {
        event_category: 'engagement',
        event_label: action,
        lead_origin: origin
      });
    }

    // Meta Pixel (fbq)
    if (data.trackMeta && typeof window.fbq === 'function') {
      window.fbq('track', 'Lead', {
        content_name: action,
        content_category: origin
      });
    }
  }
});
