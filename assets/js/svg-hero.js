/* Afrostrength — SVG.js powered flourishes.
   Animates the hero composition (drift + breathing dots), draws the process
   connector live with stroke-dashoffset, and renders empty-state graphics. */
(function () {
  'use strict';

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  function ensureSVGjs(cb) {
    if (window.SVG) return cb();
    // Bail silently if SVG.js wasn't loaded on the page
    setTimeout(() => { if (window.SVG) cb(); }, 100);
  }

  ready(() => {
    ensureSVGjs(() => {
      initHeroComposition();
      initProcessConnector();
      initEmptyStates();
    });
  });

  // ----- Hero abstract composition -----
  function initHeroComposition() {
    const host = document.querySelector('[data-hero-art]');
    if (!host) return;
    host.innerHTML = '';

    const draw = SVG().addTo(host).viewbox(0, 0, 600, 600);

    const big   = draw.circle(280).fill('#C0392B').move(40, 180);
    const sq    = draw.rect(240, 240).fill('#FCE7DD').move(300, 80);
    const small = draw.circle(180).fill('#8B0000').move(380, 380);
    const tri   = draw.polygon('40,520 220,520 130,600').fill('#FCE7DD');
    const dot1  = draw.circle(48).fill('#C0392B').move(500, 30);
    const dot2  = draw.circle(20).fill('#FCE7DD').move(120, 60);

    if (reduceMotion) return;

    const drift = (el, dx, dy, dur) => {
      el.animate(dur, '<>').move(parseFloat(el.x()) + dx, parseFloat(el.y()) + dy)
        .loop(true, true);
    };
    drift(big,   12, -8,  6000);
    drift(sq,   -10, 14,  7000);
    drift(small,  8, -14, 8000);
    drift(dot1, -16,  8,  5000);
    drift(dot2,  18, -6,  6500);

    // Breathing scale on the small accent dot
    dot1.animate(3200, '<>').scale(1.2).loop(true, true);
  }

  // ----- Process connector line (drawn on scroll-in) -----
  function initProcessConnector() {
    const host = document.querySelector('[data-process-connector]');
    if (!host) return;

    const w = host.clientWidth || 800;
    const h = host.clientHeight || 600;
    const draw = SVG().addTo(host).viewbox(0, 0, w, h).size('100%', '100%');
    // Curving dashed connector down the middle
    const path = draw.path(`M ${w/2} 0 Q ${w/2 + 60} ${h/4} ${w/2} ${h/2} Q ${w/2 - 60} ${3*h/4} ${w/2} ${h}`)
      .stroke({ color: '#C0392B', width: 1.5, dasharray: '4,8', linecap: 'round' })
      .fill('none');

    if (reduceMotion) return;
    const len = path.length();
    path.attr({ 'stroke-dashoffset': len, 'stroke-dasharray': len });
    const io = new IntersectionObserver((entries) => {
      entries.forEach(en => {
        if (en.isIntersecting) {
          path.animate(2200).attr({ 'stroke-dashoffset': 0, 'stroke-dasharray': '4,8' });
          io.disconnect();
        }
      });
    }, { threshold: 0.2 });
    io.observe(host);
  }

  // ----- Empty state illustrations -----
  function initEmptyStates() {
    document.querySelectorAll('[data-empty-state]').forEach(host => {
      host.innerHTML = '';
      const draw = SVG().addTo(host).viewbox(0, 0, 400, 300);
      draw.rect(360, 260).move(20, 20).fill('none').stroke({ color: '#0A0A0A', width: 2 });
      draw.circle(120).fill('#C0392B').move(60, 100);
      draw.circle(120).fill('#0A0A0A').move(220, 100);
      draw.rect(40, 80).fill('#8B0000').move(180, 120);
    });
  }
})();
