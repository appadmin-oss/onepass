<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

$questions = [
  [
    'q' => 'When you sit down to work, which feels most natural?',
    'a' => [
      ['t' => 'Making things look right',           's' => ['design' => 2]],
      ['t' => 'Making things work',                 's' => ['dev' => 2]],
      ['t' => 'Finding patterns in numbers',        's' => ['data' => 2]],
      ['t' => 'Telling stories that move people',   's' => ['marketing' => 2]],
    ],
  ],
  [
    'q' => 'A friend launches a side project. What do you instinctively offer?',
    'a' => [
      ['t' => 'Build the website / app',            's' => ['dev' => 2]],
      ['t' => 'Brand and design it',                's' => ['design' => 2]],
      ['t' => 'Figure out what users actually want','s' => ['data' => 1, 'marketing' => 1]],
      ['t' => 'Run a launch campaign',              's' => ['marketing' => 2]],
    ],
  ],
  [
    'q' => 'Which weekend rabbit-hole sounds most fun?',
    'a' => [
      ['t' => 'Setting up a cloud server',          's' => ['cloud' => 2, 'dev' => 1]],
      ['t' => 'Drawing a logo system',              's' => ['design' => 2]],
      ['t' => 'Training a small AI model',          's' => ['ai' => 2, 'data' => 1]],
      ['t' => 'Hacking a CTF challenge',            's' => ['security' => 2, 'dev' => 1]],
    ],
  ],
  [
    'q' => 'How comfortable are you with a terminal/CLI?',
    'a' => [
      ['t' => 'Live in it',                         's' => ['cloud' => 2, 'security' => 1, 'dev' => 1]],
      ['t' => 'Use it daily-ish',                   's' => ['dev' => 1, 'data' => 1]],
      ['t' => 'Touch it sometimes',                 's' => ['marketing' => 1]],
      ['t' => 'Avoid it',                           's' => ['design' => 1, 'marketing' => 1]],
    ],
  ],
  [
    'q' => 'In 12 months, where would you most like to be?',
    'a' => [
      ['t' => 'Shipping production software',       's' => ['dev' => 2, 'cloud' => 1]],
      ['t' => 'Leading a design team',              's' => ['design' => 2]],
      ['t' => 'Building AI products',               's' => ['ai' => 2]],
      ['t' => 'Running growth for a brand',         's' => ['marketing' => 2]],
    ],
  ],
  [
    'q' => 'Which feels more like you?',
    'a' => [
      ['t' => 'Detective who chases anomalies',     's' => ['security' => 2, 'data' => 1]],
      ['t' => 'Architect who designs systems',      's' => ['cloud' => 2, 'dev' => 1]],
      ['t' => 'Storyteller who frames the message', 's' => ['marketing' => 2, 'design' => 1]],
      ['t' => 'Engineer who optimises everything',  's' => ['data' => 2, 'ai' => 1, 'dev' => 1]],
    ],
  ],
];

$tracks = [
  'dev'       => ['title' => 'Software Development',  'slug' => 'software-development',  'note' => 'End-to-end full-stack engineering.'],
  'design'    => ['title' => 'Design (UI/UX)',        'slug' => 'design',                'note' => 'Visual hierarchy, branding, product.'],
  'data'      => ['title' => 'Data Analysis',         'slug' => 'data-analysis',         'note' => 'SQL, Python, visualisation.'],
  'ai'        => ['title' => 'AI / ML Engineering',   'slug' => 'ai-ml-engineering',     'note' => 'Build and deploy advanced models.'],
  'cloud'     => ['title' => 'Cloud Engineering',     'slug' => 'cloud-engineering',     'note' => 'AWS · Azure · GCP at production scale.'],
  'security'  => ['title' => 'Cybersecurity',         'slug' => 'cybersecurity',         'note' => 'Threat modelling, defence, incident response.'],
  'marketing' => ['title' => 'Digital Marketing',     'slug' => 'digital-marketing',     'note' => 'SEO, social, content, paid media.'],
];
?>

<header data-reveal style="margin:32px 0 24px;text-align:center;max-width:680px;margin-left:auto;margin-right:auto;">
  <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// TOOL · CAREER PATH QUIZ</div>
  <h1 class="h-display-2" style="margin:14px 0 12px;">Find your <em class="grad-text">Afrotech track.</em></h1>
  <p class="body-l" style="color:var(--text-mute);max-width:54ch;margin:0 auto;">
    Six honest questions. We weight your answers and match you to the cohort that actually fits — no signup, runs entirely in your browser.
  </p>
</header>

<section class="onboarding" data-reveal data-quiz>

  <div class="onboarding__progress">
    <span class="onboarding__progress-label" data-quiz-label>Question 01 of 0<?= count($questions) ?></span>
    <div class="onboarding__progress-bar"><div class="onboarding__progress-fill" data-quiz-fill style="width:<?= e((string)round(100/count($questions))) ?>%;"></div></div>
    <span class="onboarding__progress-label" data-quiz-name>Q1</span>
  </div>

  <?php foreach ($questions as $i => $qq): ?>
    <fieldset class="onboarding__step" data-step="<?= $i+1 ?>" <?= $i > 0 ? 'style="display:none;"' : '' ?>>
      <h2>Q<?= $i+1 ?>. <em><?= e($qq['q']) ?></em></h2>
      <div class="intent-grid" data-quiz-options>
        <?php foreach ($qq['a'] as $j => $opt): ?>
          <button type="button" class="intent-card" data-scores='<?= json_attr($opt['s']) ?>'>
            <span class="intent-card__ic"><?= icon('compass', 18) ?></span>
            <span class="intent-card__title"><?= e($opt['t']) ?></span>
          </button>
        <?php endforeach; ?>
      </div>
      <div class="onboarding__nav">
        <?php if ($i > 0): ?>
          <button type="button" class="btn btn-ghost" data-quiz-prev>← Back</button>
        <?php else: ?><span></span><?php endif; ?>
        <button type="button" class="btn btn-primary" data-quiz-next><?= $i === count($questions)-1 ? 'See result' : 'Next' ?> <?= icon_chev() ?></button>
      </div>
    </fieldset>
  <?php endforeach; ?>

  <!-- Result panel -->
  <div class="onboarding__step onboarding__success" data-quiz-result style="display:none;">
    <div class="onboarding__success-icon">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
        <path d="M4 12l5 5L20 6" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
    <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// YOUR BEST-FIT TRACK</div>
    <h2 class="ff-display" style="font-weight:700;font-size:clamp(28px,4vw,40px);line-height:1.1;letter-spacing:-0.025em;margin:14px 0 8px;" data-quiz-title>—</h2>
    <p class="body-l" style="color:var(--text-mute);max-width:50ch;margin:0 auto 18px;" data-quiz-note>—</p>

    <!-- AI-generated 3-step plan. Hidden until the AI answers; if AI is
         disabled or down the rule-based summary above carries the page. -->
    <div class="career-ai" data-quiz-ai hidden>
      <div class="career-ai__head">
        <span class="ff-mono">// MENTOR NOTE</span>
        <span class="career-ai__provider" data-quiz-ai-provider></span>
      </div>
      <p data-quiz-ai-why>—</p>
      <ol class="career-ai__next" data-quiz-ai-next></ol>
    </div>

    <div data-quiz-scores style="display:grid;grid-template-columns:repeat(7,1fr);gap:6px;max-width:480px;margin:24px auto;"></div>

    <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center;margin-top:24px;">
      <button type="button" class="btn btn-primary" data-quiz-apply>Apply to this track <?= icon_chev() ?></button>
      <button type="button" class="btn btn-ghost" data-quiz-restart>Take the quiz again</button>
    </div>
    <p class="caption" style="margin-top:24px;">Sharing this result with a friend?</p>
    <button type="button" class="nav-link" style="background:transparent;border:0;color:var(--crimson);font-size:13px;cursor:pointer;" data-quiz-copy>Copy a personalised link →</button>
    <div class="caption" data-quiz-copied style="display:none;color:var(--success);margin-top:8px;">// Copied — paste anywhere</div>
  </div>
</section>

<?php partial('promo-academy'); ?>

<script>
  (function(){
    var TRACKS = <?= json_encode($tracks, JSON_UNESCAPED_SLASHES) ?>;
    var quiz   = document.querySelector('[data-quiz]');
    if (!quiz) return;
    var steps  = quiz.querySelectorAll('[data-step]');
    var fill   = quiz.querySelector('[data-quiz-fill]');
    var lbl    = quiz.querySelector('[data-quiz-label]');
    var nm     = quiz.querySelector('[data-quiz-name]');
    var resultEl = quiz.querySelector('[data-quiz-result]');
    var scores = {};
    var picked = []; // chosen-button per step

    function render(idx) {
      steps.forEach(function(s, i) { s.style.display = i === idx ? '' : 'none'; });
      var pct = Math.round(((idx + 1) / steps.length) * 100);
      fill.style.width = pct + '%';
      lbl.textContent = 'Question 0' + (idx + 1) + ' of 0' + steps.length;
      nm.textContent  = 'Q' + (idx + 1);
    }

    quiz.querySelectorAll('[data-quiz-options]').forEach(function(grp){
      grp.querySelectorAll('button').forEach(function(b){
        b.addEventListener('click', function(){
          grp.querySelectorAll('button').forEach(function(x){ x.classList.remove('is-selected'); });
          b.classList.add('is-selected');
        });
      });
    });

    function collectStep(idx) {
      var grp = steps[idx].querySelector('[data-quiz-options]');
      var sel = grp.querySelector('.is-selected');
      if (!sel) return false;
      try {
        var s = JSON.parse(sel.dataset.scores || '{}');
        // Roll back any previous pick on this step
        if (picked[idx]) {
          Object.keys(picked[idx]).forEach(function(k){ scores[k] = (scores[k]||0) - picked[idx][k]; });
        }
        picked[idx] = s;
        Object.keys(s).forEach(function(k){ scores[k] = (scores[k]||0) + s[k]; });
        return true;
      } catch(e){ return false; }
    }

    var current = 0;
    quiz.querySelectorAll('[data-quiz-next]').forEach(function(btn, idx){
      btn.addEventListener('click', function(){
        if (!collectStep(current)) {
          var grp = steps[current].querySelector('[data-quiz-options]');
          grp.classList.add('shake'); setTimeout(function(){ grp.classList.remove('shake'); }, 360);
          return;
        }
        if (current < steps.length - 1) { current++; render(current); }
        else { reveal(); }
      });
    });
    quiz.querySelectorAll('[data-quiz-prev]').forEach(function(btn){
      btn.addEventListener('click', function(){ if (current > 0) { current--; render(current); } });
    });

    function reveal() {
      // Pick the highest-scoring key
      var best = null, bestVal = -1;
      Object.keys(scores).forEach(function(k){ if (scores[k] > bestVal) { best = k; bestVal = scores[k]; } });
      var track = TRACKS[best] || TRACKS.dev;
      steps.forEach(function(s){ s.style.display = 'none'; });
      resultEl.style.display = 'block';
      fill.style.width = '100%';
      lbl.textContent = 'Done · 0' + steps.length + ' of 0' + steps.length;
      nm.textContent  = 'Result';
      resultEl.querySelector('[data-quiz-title]').textContent = track.title;
      resultEl.querySelector('[data-quiz-note]').textContent  = track.note;

      // Score bars
      var box = resultEl.querySelector('[data-quiz-scores]');
      box.innerHTML = '';
      var max = Math.max.apply(null, Object.values(scores).concat([1]));
      var ORDER = ['dev','design','data','ai','cloud','security','marketing'];
      ORDER.forEach(function(k){
        var v = scores[k] || 0;
        var pct = Math.round((v / max) * 100);
        var cell = document.createElement('div');
        cell.style.cssText = 'display:flex;flex-direction:column;align-items:center;gap:6px;';
        cell.innerHTML =
          '<div style="height:60px;width:8px;background:var(--hairline);border-radius:4px;position:relative;overflow:hidden;">' +
          '  <div style="position:absolute;bottom:0;left:0;right:0;height:' + pct + '%;background:linear-gradient(180deg,#C0392B,#8B0000);transition:height .6s var(--ease-out);"></div>' +
          '</div>' +
          '<span class="ff-mono" style="font-size:9px;letter-spacing:0.12em;text-transform:uppercase;color:var(--text-dim);">' + k + '</span>';
        box.appendChild(cell);
      });

      // ----- AI mentor note --------------------------------------------------
      // Fire-and-forget: ask the AI for a personalised rationale + 3-step plan.
      // If it's disabled or the upstream is down, the rule-based summary above
      // is still complete — the AI note is additive, not load-bearing.
      askAi(track, scores, picked);

      // Apply with preselected track
      resultEl.querySelector('[data-quiz-apply]').addEventListener('click', function(){
        var btn = document.createElement('a');
        btn.setAttribute('data-open-apply','1');
        btn.setAttribute('data-track', track.slug);
        btn.href = '/academy/apply?track=' + track.slug;
        btn.click();
        setTimeout(function(){ if (!document.querySelector('.apply-modal.is-open')) location.href = btn.href; }, 80);
      });
      resultEl.querySelector('[data-quiz-restart]').addEventListener('click', function(){
        scores = {}; picked = []; current = 0;
        quiz.querySelectorAll('.is-selected').forEach(function(x){ x.classList.remove('is-selected'); });
        resultEl.style.display = 'none';
        render(0);
      });
      resultEl.querySelector('[data-quiz-copy]').addEventListener('click', async function(){
        var url = location.origin + '/academy/apply?track=' + track.slug;
        try { await navigator.clipboard.writeText(url); }
        catch(e){}
        resultEl.querySelector('[data-quiz-copied]').style.display = 'block';
      });
    }

    // -------- AI mentor note ------------------------------------------------
    async function askAi(track, scores, picked) {
      var aiBox = resultEl.querySelector('[data-quiz-ai]');
      if (!aiBox) return;
      var whyEl  = aiBox.querySelector('[data-quiz-ai-why]');
      var nextEl = aiBox.querySelector('[data-quiz-ai-next]');
      var provEl = aiBox.querySelector('[data-quiz-ai-provider]');

      // Render a calm loading state immediately. If AI never responds we'll
      // just remove the box at the end — no broken UI.
      aiBox.hidden = false;
      aiBox.dataset.state = 'loading';
      whyEl.textContent  = 'Reading your answers…';
      nextEl.innerHTML   = '';

      var trackList = <?= json_encode(array_map(fn($t) => ['slug'=>$t['slug'],'title'=>$t['title']], $tracks), JSON_UNESCAPED_SLASHES) ?>;
      var context = JSON.stringify({
        tracks:  trackList,
        scores:  scores,
        answers: picked,
        leaning: track.slug,
      });

      var csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
      try {
        var r = await fetch('/api/ai/suggest', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: new URLSearchParams({ intent: 'career_path', context: context, _csrf: csrf }),
        });
        var data = await r.json().catch(function(){ return {}; });
        if (!r.ok || !data.text) throw new Error('no_text');

        // The AI is asked to return strict JSON. Be liberal in what we accept:
        // strip code fences + trim, then JSON.parse with a fallback to a
        // best-effort regex extraction.
        var raw  = String(data.text).trim().replace(/^```(?:json)?\s*/i, '').replace(/```\s*$/i, '').trim();
        var parsed = null;
        try { parsed = JSON.parse(raw); }
        catch (_) {
          var m = raw.match(/\{[\s\S]*\}/);
          if (m) { try { parsed = JSON.parse(m[0]); } catch (e) { parsed = null; } }
        }
        if (!parsed || typeof parsed !== 'object') throw new Error('bad_json');

        aiBox.dataset.state = 'ready';
        whyEl.textContent  = String(parsed.why || '').trim() || 'You\'re a good fit — keep going.';
        nextEl.innerHTML   = '';
        var steps = Array.isArray(parsed.next) ? parsed.next.slice(0, 5) : [];
        steps.forEach(function(s){
          var li = document.createElement('li');
          li.textContent = String(s).trim();
          nextEl.appendChild(li);
        });
        if (data.provider && provEl) provEl.textContent = '· via ' + data.provider;

        // If the AI picked a different track than our scoring, surface that
        // honestly — don't silently swap. We keep our track as the headline,
        // and let the AI rationale stand alongside.
        if (parsed.track && parsed.track !== track.slug) {
          var note = document.createElement('p');
          note.className = 'career-ai__diff';
          note.textContent = 'Your scores lean ' + track.slug + '. The AI mentor leans ' + parsed.track + ' — your choice.';
          whyEl.parentNode.insertBefore(note, whyEl.nextSibling);
        }
      } catch (e) {
        // Graceful retreat: hide the box. The page is still complete.
        aiBox.hidden = true;
      }
    }
  })();
</script>
