/* Afrostrength — minimal image lightbox for project galleries. */
(function () {
  'use strict';
  document.addEventListener('DOMContentLoaded', () => {
    const triggers = document.querySelectorAll('[data-lightbox]');
    if (!triggers.length) return;
    const items = Array.from(triggers).map(t => ({
      src: t.dataset.lightbox || t.getAttribute('href') || (t.querySelector('img') && t.querySelector('img').src),
      alt: t.dataset.alt || ''
    }));

    const lb = document.createElement('div');
    lb.className = 'lightbox';
    lb.setAttribute('role', 'dialog');
    lb.setAttribute('aria-modal', 'true');
    lb.innerHTML = `
      <button class="lightbox__close" aria-label="Close">×</button>
      <button class="lightbox__nav lightbox__nav--prev" aria-label="Previous">‹</button>
      <img alt="">
      <button class="lightbox__nav lightbox__nav--next" aria-label="Next">›</button>
    `;
    document.body.appendChild(lb);
    const img   = lb.querySelector('img');
    const close = lb.querySelector('.lightbox__close');
    const prev  = lb.querySelector('.lightbox__nav--prev');
    const next  = lb.querySelector('.lightbox__nav--next');

    let index = 0;
    function show(i) {
      if (i < 0) i = items.length - 1;
      if (i >= items.length) i = 0;
      index = i;
      img.src = items[i].src;
      img.alt = items[i].alt;
      lb.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    }
    function hide() {
      lb.classList.remove('is-open');
      document.body.style.overflow = '';
    }
    triggers.forEach((t, i) => t.addEventListener('click', (e) => { e.preventDefault(); show(i); }));
    close.addEventListener('click', hide);
    next.addEventListener('click',  () => show(index + 1));
    prev.addEventListener('click',  () => show(index - 1));
    lb.addEventListener('click', (e) => { if (e.target === lb) hide(); });
    document.addEventListener('keydown', (e) => {
      if (!lb.classList.contains('is-open')) return;
      if (e.key === 'Escape')      hide();
      if (e.key === 'ArrowRight')  show(index + 1);
      if (e.key === 'ArrowLeft')   show(index - 1);
    });
  });
})();
