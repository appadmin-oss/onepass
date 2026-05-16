/* auth.js — runs on /login and friends.
 * Wires the Embla carousel (when the library has loaded), the OTP
 * input UX, and small affordances like respecting Enter to submit.
 *
 * Embla is loaded from CDN with `defer` — it may execute *after* this
 * script. We wait for it via a small ready-poll bounded to ~3s.
 */
(function () {
  'use strict';

  function waitFor(test, ms) {
    return new Promise(function (resolve) {
      var start = Date.now();
      (function tick() {
        if (test()) return resolve(true);
        if (Date.now() - start > ms) return resolve(false);
        requestAnimationFrame(tick);
      })();
    });
  }

  function initCarousel() {
    var root = document.querySelector('[data-embla]');
    if (!root) return Promise.resolve();
    var viewport = root.querySelector('.embla__viewport');
    if (!viewport) return Promise.resolve();
    return waitFor(function () { return typeof window.EmblaCarousel === 'function'; }, 3000).then(function (ok) {
      if (!ok) return; // Library failed to load; static slides still render.
      var embla = window.EmblaCarousel(viewport, {
        loop: true,
        align: 'start',
        skipSnaps: false,
      });
      var prev = root.querySelector('.embla__prev');
      var next = root.querySelector('.embla__next');
      var dots = root.querySelector('[data-embla-dots]');

      function renderDots() {
        if (!dots) return;
        var snaps = embla.scrollSnapList();
        dots.innerHTML = snaps.map(function (_, i) {
          return '<button type="button" class="embla__dot" data-i="' + i + '" aria-label="Slide ' + (i + 1) + '"></button>';
        }).join('');
        dots.querySelectorAll('.embla__dot').forEach(function (b) {
          b.addEventListener('click', function () { embla.scrollTo(parseInt(b.dataset.i, 10)); });
        });
        update();
      }

      function update() {
        var selected = embla.selectedScrollSnap();
        if (dots) {
          dots.querySelectorAll('.embla__dot').forEach(function (b, i) {
            b.classList.toggle('is-active', i === selected);
            b.setAttribute('aria-current', i === selected ? 'true' : 'false');
          });
        }
        if (prev) prev.toggleAttribute('disabled', !embla.canScrollPrev());
        if (next) next.toggleAttribute('disabled', !embla.canScrollNext());
      }

      prev && prev.addEventListener('click', function () { embla.scrollPrev(); });
      next && next.addEventListener('click', function () { embla.scrollNext(); });
      embla.on('select', update);
      embla.on('reInit', renderDots);
      renderDots();

      // Auto-advance, paused on hover or when reduced motion is preferred.
      var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      if (!reduce) {
        var timer = null;
        function play() { timer = setInterval(function () { embla.scrollNext(); }, 6000); }
        function pause() { if (timer) { clearInterval(timer); timer = null; } }
        root.addEventListener('mouseenter', pause);
        root.addEventListener('mouseleave', play);
        root.addEventListener('focusin', pause);
        root.addEventListener('focusout', play);
        play();
      }
    });
  }

  function initOtp() {
    var input = document.querySelector('.otp-input');
    if (!input) return;
    // Strip non-digits as the user types; auto-submit when 6 digits are present.
    input.addEventListener('input', function () {
      var v = input.value.replace(/\D/g, '').slice(0, 6);
      if (v !== input.value) input.value = v;
      if (v.length === 6) {
        var form = input.closest('form');
        if (form) {
          // Microtask to let the value commit before submit.
          setTimeout(function () { form.requestSubmit ? form.requestSubmit() : form.submit(); }, 50);
        }
      }
    });
    // WebOTP API (Android Chrome) — silently fills the code if the SMS
    // matches a `@<host> #<code>` format. We never send SMS; this is
    // harmless when not supported and never throws.
    if ('OTPCredential' in window && navigator.credentials) {
      try {
        var ac = new AbortController();
        navigator.credentials.get({
          otp: { transport: ['sms'] },
          signal: ac.signal,
        }).then(function (cred) {
          if (cred && cred.code) {
            input.value = cred.code.replace(/\D/g, '').slice(0, 6);
            input.dispatchEvent(new Event('input'));
          }
        }).catch(function () { /* timed out or cancelled */ });
        setTimeout(function () { ac.abort(); }, 60000);
      } catch (e) { /* ignore */ }
    }
  }

  function init() {
    initOtp();
    initCarousel();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
