<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<header data-reveal style="margin:32px 0 24px;text-align:center;max-width:680px;margin-left:auto;margin-right:auto;">
  <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// TOOL · NG TECH SALARY CALCULATOR</div>
  <h1 class="h-display-2" style="margin:14px 0 12px;">What you'd <em class="grad-text">actually earn.</em></h1>
  <p class="body-l" style="color:var(--text-mute);max-width:54ch;margin:0 auto;">
    Realistic monthly + yearly bands for Nigerian tech roles by stack, experience, and city.
    Numbers are calibrated to 2026 market data from real hiring conversations.
  </p>
</header>

<section class="tool-stage" data-reveal>
  <div class="tool-stage__panel">
    <div class="eyebrow">ROLE</div>
    <div class="track-grid" data-salary-roles style="grid-template-columns:1fr 1fr;margin-top:12px;">
      <?php $roles = [
        'frontend'      => ['Frontend Engineer',     1.0],
        'backend'       => ['Backend Engineer',      1.15],
        'fullstack'     => ['Full-stack Engineer',   1.20],
        'mobile'        => ['Mobile Engineer',       1.15],
        'devops'        => ['DevOps / Cloud',        1.30],
        'data'          => ['Data Analyst',          0.90],
        'ml'            => ['ML / AI Engineer',      1.40],
        'security'      => ['Cybersecurity',         1.30],
        'design'        => ['Product Designer',      0.95],
        'pm'            => ['Product Manager',       1.20],
      ]; foreach ($roles as $k => $r): ?>
        <button type="button" class="track-card<?= $k === 'frontend' ? ' is-selected' : '' ?>" data-role="<?= e($k) ?>" data-mult="<?= e((string)$r[1]) ?>">
          <span class="track-card__name"><?= e($r[0]) ?></span>
        </button>
      <?php endforeach; ?>
    </div>

    <div class="eyebrow" style="margin-top:24px;">// EXPERIENCE</div>
    <div class="scale" data-salary-level>
      <?php $levels = [
        ['junior',   'Junior',     '0–2y',  0.6],
        ['mid',      'Mid',        '2–4y',  1.0],
        ['senior',   'Senior',     '4–7y',  1.6],
        ['staff',    'Staff/Lead', '7y+',   2.4],
      ]; foreach ($levels as $i => $lv): ?>
        <button type="button" class="scale__step<?= $i === 1 ? ' is-selected' : '' ?>" data-level="<?= e($lv[0]) ?>" data-mult="<?= e((string)$lv[3]) ?>">
          <strong><?= e($lv[1]) ?></strong>
          <span class="ff-mono" style="font-size:10px;opacity:0.7;"><?= e($lv[2]) ?></span>
        </button>
      <?php endforeach; ?>
    </div>

    <div class="eyebrow" style="margin-top:24px;">// CITY</div>
    <div class="track-grid" data-salary-city style="grid-template-columns:repeat(3,1fr);margin-top:12px;">
      <?php $cities = [
        'lagos'  => ['Lagos',  1.0],
        'abuja'  => ['Abuja',  0.85],
        'remote' => ['Remote · NGN', 1.05],
      ]; foreach ($cities as $k => $c): ?>
        <button type="button" class="track-card<?= $k === 'lagos' ? ' is-selected' : '' ?>" data-city="<?= e($k) ?>" data-mult="<?= e((string)$c[1]) ?>">
          <span class="track-card__name"><?= e($c[0]) ?></span>
        </button>
      <?php endforeach; ?>
    </div>

    <div class="eyebrow" style="margin-top:24px;">// EMPLOYMENT</div>
    <div class="track-grid" data-salary-employ style="grid-template-columns:1fr 1fr;margin-top:12px;">
      <button type="button" class="track-card is-selected" data-employ="local" data-mult="1.0">
        <span class="track-card__name">Local company</span>
      </button>
      <button type="button" class="track-card" data-employ="remote-intl" data-mult="3.2">
        <span class="track-card__name">Remote · International (USD)</span>
      </button>
    </div>

    <div class="eyebrow" style="margin-top:24px;">// VIEW</div>
    <div class="track-grid" data-salary-view style="grid-template-columns:1fr 1fr 1fr;margin-top:12px;">
      <button type="button" class="track-card is-selected" data-view="gross">
        <span class="track-card__name">Gross</span>
        <span class="ff-mono" style="font-size:10px;opacity:0.7;">Before deductions</span>
      </button>
      <button type="button" class="track-card" data-view="net">
        <span class="track-card__name">Take-home</span>
        <span class="ff-mono" style="font-size:10px;opacity:0.7;">Post-tax · NG PAYE</span>
      </button>
      <button type="button" class="track-card" data-view="total">
        <span class="track-card__name">Total comp</span>
        <span class="ff-mono" style="font-size:10px;opacity:0.7;">+ benefits + equity</span>
      </button>
    </div>
  </div>

  <div class="tool-stage__panel tool-stage__result">
    <div data-salary-stage>
      <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// ESTIMATED BAND</div>
      <div style="margin-top:14px;display:flex;flex-direction:column;gap:12px;align-items:center;width:100%;">
        <div class="ff-display" style="font-weight:700;font-size:clamp(28px,4vw,42px);line-height:1;letter-spacing:-0.025em;" data-salary-monthly>—</div>
        <div class="ff-mono" style="font-size:10px;letter-spacing:0.2em;text-transform:uppercase;color:var(--text-dim);">PER MONTH (NGN)</div>
        <div style="height:1px;width:80px;background:var(--hairline);margin:6px 0;"></div>
        <div class="ff-display" style="font-weight:600;font-size:18px;color:var(--text-mute);" data-salary-yearly>—</div>
        <div class="ff-mono" style="font-size:10px;letter-spacing:0.2em;text-transform:uppercase;color:var(--text-dim);">PER YEAR</div>
      </div>
      <div style="margin-top:24px;padding-top:18px;border-top:1px solid var(--hairline);width:100%;">
        <div class="caption" data-salary-note>Pick your role, experience, and city to estimate.</div>
      </div>
    </div>
  </div>
</section>

<aside class="grad-crimson" data-reveal style="border-radius:18px;padding:32px;margin:48px 0;color:#fff;">
  <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:rgba(255,255,255,0.78);text-transform:uppercase;">// WANT TO HIT THE SENIOR BAND?</div>
  <h3 class="ff-display" style="font-weight:700;font-size:clamp(22px,3vw,28px);line-height:1.15;margin:12px 0 8px;color:#fff;">
    Afrotech tracks are calibrated to <em style="font-style:italic;color:#FCB7AB;">₦500K – ₦2M+/month</em> roles.
  </h3>
  <p style="margin:0 0 16px;font-size:14px;color:rgba(255,255,255,0.82);max-width:60ch;">
    Live cohorts, real mentor reviews, and globally-recognised certifications. Trust-first 3-minute application.
  </p>
  <a href="<?= e(url('/academy/apply')) ?>" class="btn btn-on-dark">Apply free <?= icon_chev() ?></a>
</aside>

<script>
  (function(){
    var monthly = document.querySelector('[data-salary-monthly]');
    var yearly  = document.querySelector('[data-salary-yearly]');
    var note    = document.querySelector('[data-salary-note]');

    // Calibrated base bands (mid-level, Lagos, local, frontend baseline)
    // ₦600K mid-level monthly baseline. Multipliers chain.
    var BASE = 600000;

    function pick(grp, key) {
      var el = grp.querySelector('.is-selected');
      return el ? parseFloat(el.dataset[key] || el.dataset.mult || '1') : 1;
    }
    function role()  { return pick(document.querySelector('[data-salary-roles]'),  'mult'); }
    function level() { return pick(document.querySelector('[data-salary-level]'),  'mult'); }
    function city()  { return pick(document.querySelector('[data-salary-city]'),   'mult'); }
    function emp()   { return pick(document.querySelector('[data-salary-employ]'), 'mult'); }
    function empKey(){
      var el = document.querySelector('[data-salary-employ] .is-selected');
      return el ? el.dataset.employ : 'local';
    }
    function fmt(n) {
      n = Math.round(n);
      if (n >= 1000000) return '₦' + (n/1000000).toFixed(1).replace('.0','') + 'M';
      if (n >= 1000)    return '₦' + Math.round(n/1000) + 'K';
      return '₦' + n;
    }

    function update() {
      var mo = BASE * role() * level() * city() * emp();
      var yr = mo * 12;
      var lo = Math.round(mo * 0.85), hi = Math.round(mo * 1.20);
      monthly.textContent = fmt(lo) + ' – ' + fmt(hi);
      yearly.textContent  = fmt(yr * 0.85) + ' – ' + fmt(yr * 1.20);
      var bonus = empKey() === 'remote-intl'
        ? '// Remote international roles pay in USD/EUR. Multiplier here approximates NGN-equivalent at 2026 rates.'
        : '// Equity, allowances and bonuses can add 10–30% on top.';
      note.textContent = bonus;
    }

    function setupGroup(sel, attr) {
      document.querySelectorAll(sel + ' button').forEach(function(b){
        b.addEventListener('click', function(){
          b.parentElement.querySelectorAll('button').forEach(function(x){ x.classList.remove('is-selected'); });
          b.classList.add('is-selected');
          update();
        });
      });
    }
    setupGroup('[data-salary-roles]');
    setupGroup('[data-salary-level]');
    setupGroup('[data-salary-city]');
    setupGroup('[data-salary-employ]');
    setupGroup('[data-salary-view]');

    // Nigerian PAYE (progressive bands, monthly equivalents — close to 2026 figures)
    function nigerianPaye(monthly) {
      var annual = monthly * 12;
      // Consolidated relief allowance: ₦200K + 20% of gross
      var cra = 200000 + annual * 0.20;
      var taxable = Math.max(0, annual - cra);
      var bands = [[300000,0.07],[300000,0.11],[500000,0.15],[500000,0.19],[1600000,0.21],[Infinity,0.24]];
      var tax = 0, remaining = taxable;
      for (var b of bands) {
        if (remaining <= 0) break;
        var slice = Math.min(remaining, b[0]);
        tax += slice * b[1];
        remaining -= slice;
      }
      // Pension 8% + NHF 2.5% (employee side)
      var pension = annual * 0.08;
      var nhf     = annual * 0.025;
      return (annual - tax - pension - nhf) / 12;
    }

    function viewMode() {
      var el = document.querySelector('[data-salary-view] .is-selected');
      return el ? el.dataset.view : 'gross';
    }

    var BASE_UPDATE = update;
    update = function(){
      var grossLo = BASE * role() * level() * city() * emp() * 0.85;
      var grossHi = BASE * role() * level() * city() * emp() * 1.20;
      var view = viewMode();
      var lo = grossLo, hi = grossHi, label = 'PER MONTH (NGN)', sub = 'PER YEAR';
      if (view === 'net') {
        lo = nigerianPaye(grossLo); hi = nigerianPaye(grossHi);
        label = 'TAKE-HOME / MONTH';
        sub   = 'AFTER PAYE · PENSION · NHF';
      } else if (view === 'total') {
        // + 18% benefits/equity uplift on the gross band
        lo = grossLo * 1.18; hi = grossHi * 1.18;
        label = 'TOTAL COMP / MONTH';
        sub   = 'GROSS + BENEFITS + EQUITY';
      }
      monthly.textContent = fmt(lo) + ' – ' + fmt(hi);
      yearly.textContent  = fmt(lo * 12) + ' – ' + fmt(hi * 12);
      document.querySelectorAll('[data-salary-stage] .ff-mono').forEach(function(el, i){
        if (i === 0) el.textContent = label;
        if (i === 1) el.textContent = sub;
      });
      var bonus = empKey() === 'remote-intl'
        ? '// Remote international roles pay in USD/EUR. Multiplier here approximates NGN-equivalent at 2026 rates.'
        : (view === 'net'
          ? '// PAYE estimate: CRA (₦200K + 20%), progressive bands, pension 8%, NHF 2.5%. Real tax may vary.'
          : '// Equity, allowances and bonuses can add 10–30% on top.');
      note.textContent = bonus;
    };
    update();
  })();
</script>
