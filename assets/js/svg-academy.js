/* Afrostrength — GraphicsJS-driven flourishes.
   Service-overview hover spots that respond to the pointer, and the academy
   certification-mark reveal. Loads only on pages flagged data-uses="graphicsjs". */
(function () {
  'use strict';

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  function ensure(cb) {
    if (window.acgraph) return cb();
    setTimeout(() => { if (window.acgraph) cb(); }, 120);
  }

  ready(() => ensure(() => {
    initServiceSpots();
    initCertMark();
  }));

  // Pointer-reactive spot illustrations on service cards.
  function initServiceSpots() {
    const hosts = document.querySelectorAll('[data-service-spot]');
    if (!hosts.length) return;

    hosts.forEach(host => {
      const w = host.clientWidth || 80;
      const h = host.clientHeight || 80;
      const stage = acgraph.create(host, w, h);
      const circ  = stage.circle(w * 0.38, h * 0.55, w * 0.28).fill('#C0392B');
      const rect  = stage.rect(w * 0.50, h * 0.22, w * 0.34, h * 0.45).fill('#0A0A0A');

      if (reduceMotion) return;
      host.addEventListener('mousemove', (e) => {
        const r = host.getBoundingClientRect();
        const dx = (e.clientX - r.left - w / 2) / w;
        const dy = (e.clientY - r.top  - h / 2) / h;
        circ.center(w * 0.38 + dx * 8, h * 0.55 + dy * 8);
        rect.setX(w * 0.50 + dx * -6);
        rect.setY(h * 0.22 + dy * -4);
      });
      host.addEventListener('mouseleave', () => {
        circ.center(w * 0.38, h * 0.55);
        rect.setX(w * 0.50); rect.setY(h * 0.22);
      });
    });
  }

  // Animated cert mark — concentric circles that scale-in on viewport entry.
  function initCertMark() {
    const host = document.querySelector('[data-cert-mark]');
    if (!host) return;
    const w = host.clientWidth  || 200;
    const h = host.clientHeight || 200;
    const stage = acgraph.create(host, w, h);

    const outer = stage.circle(w / 2, h / 2, w * 0.35).fill('none').stroke('#FFFFFF', 2);
    const inner = stage.circle(w / 2, h / 2, w * 0.18).fill('#8B0000');

    if (reduceMotion) return;
    outer.scale(0.6, 0.6, w / 2, h / 2);
    inner.scale(0, 0, w / 2, h / 2);
    const io = new IntersectionObserver((entries) => {
      entries.forEach(en => {
        if (en.isIntersecting) {
          if (typeof outer.animate === 'function') {
            outer.animate({ scaleX: 1, scaleY: 1 }, 700);
            inner.animate({ scaleX: 1, scaleY: 1 }, 900);
          } else {
            outer.scale(1, 1, w / 2, h / 2);
            inner.scale(1, 1, w / 2, h / 2);
          }
          io.disconnect();
        }
      });
    }, { threshold: 0.3 });
    io.observe(host);
  }
})();
