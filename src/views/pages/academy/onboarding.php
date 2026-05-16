<?php
/** @var array $tracks
 *  @var string $preselected
 *
 * Trust-first onboarding. We never ask for identity until step 6.
 * Steps:
 *   1 · Intent (no commitment, builds rapport)
 *   2 · Track interest (recommended after intent)
 *   3 · Skill self-rate (visual segmented + per-skill pills)
 *   4 · Experience (practical "have you ever…" questions)
 *   5 · Portfolio (optional links, builds confidence)
 *   6 · Identity + cohort + account (the ask, after value)
 *
 * Save & continue later persists each step's data to localStorage so the
 * applicant can leave and return.
 */
require_once AFS_ROOT . '/src/views/partials/icons.php';
require_once AFS_ROOT . '/src/views/partials/divider.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
$err = flash_pop('onboarding_error');
$totalSteps = 6;

$stepNames = ['Intent', 'Track', 'Skills', 'Experience', 'Portfolio', 'You'];
?>

<header data-reveal style="text-align:center;margin:0 auto 32px;max-width:680px;">
  <span class="encourage">No login needed · 3 minutes</span>
  <h1 class="h-display-2" style="margin:24px 0 16px;">
    Let's see if Afrotech is a <em class="grad-text">fit for you.</em>
  </h1>
  <p class="body-l" style="color:var(--text-mute);max-width:54ch;margin:0 auto;">
    Six quick steps. No identity questions until the very end —
    we want to understand you first, not the other way around.
  </p>
</header>

<?php divider('rule', ['width' => 'narrow']); ?>

<div class="onboarding">
  <div class="onboarding__progress">
    <span class="onboarding__progress-label" data-onboarding-step-label>Step 01 of 06</span>
    <div class="onboarding__progress-bar">
      <div class="onboarding__progress-fill" data-onboarding-fill style="width:<?= e((string)round(100/$totalSteps)) ?>%;"></div>
    </div>
    <span class="onboarding__progress-label" data-onboarding-step-name>Intent</span>
  </div>

  <!-- Dynamic encouragement (filled by JS) -->
  <div data-onboarding-encourage style="display:none;margin:-12px 0 24px;text-align:center;">
    <span class="encourage" data-onboarding-encourage-text>You're 0% in.</span>
  </div>

  <?php if ($err): ?>
    <div role="alert" style="padding:14px 18px;border-radius:12px;background:rgba(192,57,43,0.08);border:1px solid rgba(192,57,43,0.25);color:var(--crimson);margin-bottom:24px;font-size:13px;">
      // <?= e($err) ?>
    </div>
  <?php endif; ?>

  <form data-form data-onboarding action="<?= e(url('/api/onboarding')) ?>" method="post" novalidate>
    <input type="hidden" name="_csrf"      value="<?= e(Csrf::token()) ?>">
    <input type="hidden" name="track_slug" value="<?= e($preselected) ?>">
    <input type="hidden" name="intent"     value="">
    <input type="hidden" name="skill_level" value="">
    <!-- Skill scores + experience answers are bundled into one JSON field for compactness -->
    <input type="hidden" name="skill_scores"     value="{}">
    <input type="hidden" name="experience_scores" value="{}">

    <!-- ==================== STEP 1 · INTENT ==================== -->
    <fieldset class="onboarding__step" data-step="1" data-step-name="Intent">
      <h2>Why are you here?</h2>
      <p class="intro">No wrong answers. Helps us point you at the right cohort.</p>

      <div class="intent-grid" data-intent-group>
        <?php $intents = [
          ['key' => 'career-switch', 'title' => 'Career switch', 'hint' => 'Move into tech',     'ic' => 'method'],
          ['key' => 'freelance',     'title' => 'Freelancing',   'hint' => 'Earn on my terms',   'ic' => 'spark'],
          ['key' => 'startup',       'title' => 'Building a startup', 'hint' => 'Founder skills', 'ic' => 'compass'],
          ['key' => 'employment',    'title' => 'Better job',    'hint' => 'Senior roles',       'ic' => 'outcomes'],
          ['key' => 'curiosity',     'title' => 'Just curious',  'hint' => 'Exploring',          'ic' => 'pulse'],
          ['key' => 'school',        'title' => 'School / requirement', 'hint' => 'Coursework',  'ic' => 'editorial'],
        ]; foreach ($intents as $it): ?>
          <button type="button" class="intent-card" data-intent="<?= e($it['key']) ?>">
            <span class="intent-card__ic"><?= icon($it['ic'], 20) ?></span>
            <span class="intent-card__title"><?= e($it['title']) ?></span>
            <span class="intent-card__hint"><?= e($it['hint']) ?></span>
          </button>
        <?php endforeach; ?>
      </div>

      <div class="onboarding__nav">
        <span></span>
        <button type="button" class="btn btn-primary" data-step-next>Continue <?= icon_chev() ?></button>
      </div>
    </fieldset>

    <!-- ==================== STEP 2 · TRACK ==================== -->
    <fieldset class="onboarding__step" data-step="2" data-step-name="Track" style="display:none;">
      <h2>Which track <em>interests</em> you most?</h2>
      <p class="intro">You can change this later — we're just learning where your head is at.</p>

      <div class="track-reco" data-track-reco hidden aria-live="polite">
        <span class="track-reco__h">Based on your intent</span>
        <div class="track-reco__chips" data-track-reco-chips></div>
      </div>

      <div class="track-grid" data-onboarding-tracks>
        <?php $i = 1; foreach ($tracks as $t): ?>
          <button type="button" class="track-card<?= ($t['slug'] === $preselected) ? ' is-selected' : '' ?>" data-track="<?= e($t['slug']) ?>" data-track-name="<?= e($t['title']) ?>">
            <span class="track-card__num"><?= str_pad((string)$i, 2, '0', STR_PAD_LEFT) ?> · <?= e(strtoupper($t['level'] ?? 'FOUNDATIONS')) ?></span>
            <span class="track-card__name"><?= e($t['title']) ?></span>
            <span class="track-card__meta">
              <?= e((string)($t['weeks'] ?? 8)) ?> weeks · ₦<?= number_format((int)($t['price_naira'] ?? 0)) ?>
              <?php if (!empty($t['live'])): ?> · <span class="live">● LIVE</span><?php endif; ?>
            </span>
          </button>
        <?php $i++; endforeach; ?>
      </div>

      <div class="helper" data-track-helper style="display:none;color:var(--crimson);">// Pick a track to continue.</div>

      <div class="onboarding__nav">
        <button type="button" class="btn btn-ghost" data-step-prev>← Back</button>
        <button type="button" class="btn btn-primary" data-step-next>Continue <?= icon_chev() ?></button>
      </div>
    </fieldset>

    <!-- ==================== STEP 3 · SKILLS ==================== -->
    <fieldset class="onboarding__step" data-step="3" data-step-name="Skills" style="display:none;">
      <h2>How would you <em>rate</em> yourself?</h2>
      <p class="intro">Pick a baseline, then fine-tune the specific skills that apply. Your skill-radar shapes the curriculum we recommend.</p>

      <div class="eyebrow" style="margin-top:8px;">// Baseline</div>
      <div class="scale" data-scale-group>
        <?php $levels = [
          ['key' => 'beginner',     'label' => 'Beginner',     'hint' => 'New to this'],
          ['key' => 'basic',        'label' => 'Basic',        'hint' => 'Some exposure'],
          ['key' => 'intermediate', 'label' => 'Intermediate', 'hint' => 'Built things'],
          ['key' => 'advanced',     'label' => 'Advanced',     'hint' => 'Lead level'],
        ]; foreach ($levels as $i => $lv): ?>
          <button type="button" class="scale__step" data-level="<?= e((string)$i) ?>" data-key="<?= e($lv['key']) ?>">
            <strong><?= e($lv['label']) ?></strong>
            <span class="ff-mono" style="font-size:10px;letter-spacing:0.14em;text-transform:uppercase;opacity:0.7;"><?= e($lv['hint']) ?></span>
            <span class="scale__step__bars" aria-hidden="true"><span></span><span></span><span></span><span></span></span>
          </button>
        <?php endforeach; ?>
      </div>

      <?php
      $skillGroups = [
        'Development'        => ['HTML / CSS','JavaScript','React','Backend','APIs','Git / GitHub'],
        'Design'             => ['Figma','UI Design','UX Research','Design Systems'],
        'Data / AI'          => ['Python','Data Analysis','Machine Learning','Prompt Engineering'],
        'General'            => ['Problem Solving','Communication','Team Collaboration','Internet Research'],
      ];
      $options = ['None','Some','Solid','Strong'];
      foreach ($skillGroups as $group => $skills): ?>
        <div class="skills-group" data-skill-group="<?= e($group) ?>">
          <div class="skills-group__head">// <?= e(strtoupper($group)) ?></div>
          <?php foreach ($skills as $skill): ?>
            <div class="skill-row" data-skill="<?= e($skill) ?>">
              <div class="skill-row__label">
                <span class="ic"><?= icon('method', 14) ?></span>
                <span><?= e($skill) ?></span>
              </div>
              <div class="skill-row__pills" data-skill-pills>
                <?php foreach ($options as $oi => $opt): ?>
                  <button type="button" class="skill-pill" data-value="<?= e((string)$oi) ?>"><?= e($opt) ?></button>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>

      <div class="onboarding__nav">
        <button type="button" class="btn btn-ghost" data-step-prev>← Back</button>
        <button type="button" class="btn btn-primary" data-step-next>Continue <?= icon_chev() ?></button>
      </div>
    </fieldset>

    <!-- ==================== STEP 4 · EXPERIENCE ==================== -->
    <fieldset class="onboarding__step" data-step="4" data-step-name="Experience" style="display:none;">
      <h2>The <em>practical</em> stuff.</h2>
      <p class="intro">More telling than self-rating. Pick the closest answer — we'll never grade you on this.</p>

      <div class="skills-group">
        <div class="skills-group__head">// HAVE YOU EVER…</div>
        <div style="padding: 16px 18px;">
          <?php $questions = [
            'deployed' => 'Deployed a project online (live URL)?',
            'team'     => 'Worked in a team on a tech project?',
            'github'   => 'Contributed to a GitHub repository?',
            'earned'   => 'Earned money from a tech skill?',
            'shipped'  => 'Built a complete, finished project?',
            'taught'   => 'Taught a tech skill to someone else?',
          ];
          $expOpts = ['Never', 'Once', 'A few times', 'Frequently'];
          foreach ($questions as $key => $q): ?>
            <div class="experience-q" data-experience="<?= e($key) ?>">
              <div class="experience-q__q"><?= e($q) ?></div>
              <div class="experience-q__opts" data-experience-opts>
                <?php foreach ($expOpts as $oi => $opt): ?>
                  <button type="button" class="experience-q__opt" data-value="<?= e((string)$oi) ?>"><?= e($opt) ?></button>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="onboarding__nav">
        <button type="button" class="btn btn-ghost" data-step-prev>← Back</button>
        <button type="button" class="btn btn-primary" data-step-next>Continue <?= icon_chev() ?></button>
      </div>
    </fieldset>

    <!-- ==================== STEP 5 · PORTFOLIO ==================== -->
    <fieldset class="onboarding__step" data-step="5" data-step-name="Portfolio" style="display:none;">
      <h2>Show us <em>something.</em></h2>
      <p class="intro">All optional — but even one link makes our admissions team much faster to respond. Skip whatever doesn't apply.</p>

      <div class="field">
        <input type="url" name="github_url" placeholder=" " inputmode="url">
        <label>GitHub profile</label>
      </div>
      <div class="field">
        <input type="url" name="portfolio_url" placeholder=" " inputmode="url">
        <label>Portfolio website</label>
      </div>
      <div class="field">
        <input type="url" name="linkedin_url" placeholder=" " inputmode="url">
        <label>LinkedIn</label>
      </div>
      <div class="field">
        <input type="url" name="behance_url" placeholder=" " inputmode="url">
        <label>Behance / Dribbble (designers)</label>
      </div>
      <div class="field">
        <textarea rows="3" name="showcase" placeholder=" "></textarea>
        <label>One thing you've built (a sentence + link)</label>
      </div>

      <div class="onboarding__nav">
        <button type="button" class="btn btn-ghost" data-step-prev>← Back</button>
        <button type="button" class="btn btn-primary" data-step-next>Continue <?= icon_chev() ?></button>
      </div>
    </fieldset>

    <!-- ==================== STEP 6 · IDENTITY + ACCOUNT ==================== -->
    <fieldset class="onboarding__step" data-step="6" data-step-name="You" style="display:none;">
      <h2>One last thing — <em>your details.</em></h2>
      <p class="intro">We use these to confirm your seat and route you to the right cohort lead. We never share your data and you can unsubscribe anytime.</p>

      <div class="field">
        <input type="text" name="name" placeholder=" " data-rules="required|min:2" autocomplete="name" data-derive-from="email-local">
        <label>Full name</label>
      </div>
      <div class="field">
        <input type="email" name="email" placeholder=" " data-rules="required|email" autocomplete="email" data-smart-email>
        <label>Email address</label>
      </div>
      <div class="field">
        <input type="tel" name="phone" placeholder=" " autocomplete="tel" data-rules="phone" data-prefix-when="country:Nigeria=>+234 ">
        <label>Phone</label>
      </div>

      <div class="grid-cols-2" style="gap:18px;margin-top:4px;">
        <div class="field">
          <input type="text" name="country" placeholder=" " value="Nigeria" autocomplete="country-name">
          <label>Country / City</label>
        </div>
        <div class="field">
          <select name="age_range" style="width:100%;padding:14px 0 10px;border:0;border-bottom:1px solid var(--hairline);background:transparent;font-size:15px;">
            <option value="">Age range (optional)</option>
            <option value="under-18">Under 18</option>
            <option value="18-24">18–24</option>
            <option value="25-34">25–34</option>
            <option value="35-44">35–44</option>
            <option value="45-plus">45+</option>
          </select>
        </div>
      </div>

      <div class="field" style="margin-top:18px;">
        <select name="current_status" style="width:100%;padding:14px 0 10px;border:0;border-bottom:1px solid var(--hairline);background:transparent;font-size:15px;">
          <option value="">Current status</option>
          <option value="student">Student</option>
          <option value="graduate">Recent graduate</option>
          <option value="employed">Employed</option>
          <option value="freelancer">Freelancer</option>
          <option value="job-seeker">Job-seeker</option>
          <option value="other">Other</option>
        </select>
      </div>

      <div data-choice-group="cohort_pref" style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:24px;">
        <button type="button" class="choice-card is-selected" data-value="next">
          <span style="display:flex;flex-direction:column;align-items:flex-start;gap:4px;">
            <span>Next cohort</span>
            <span class="ff-mono" style="font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:var(--text-dim);">Opens in 2 weeks</span>
          </span>
          <span class="choice-card__dot"></span>
        </button>
        <button type="button" class="choice-card" data-value="next-month">
          <span style="display:flex;flex-direction:column;align-items:flex-start;gap:4px;">
            <span>Next month</span>
            <span class="ff-mono" style="font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:var(--text-dim);">More prep time</span>
          </span>
          <span class="choice-card__dot"></span>
        </button>
        <button type="button" class="choice-card" data-value="specific">
          <span style="display:flex;flex-direction:column;align-items:flex-start;gap:4px;">
            <span>Specific date</span>
            <span class="ff-mono" style="font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:var(--text-dim);">I have a deadline</span>
          </span>
          <span class="choice-card__dot"></span>
        </button>
        <button type="button" class="choice-card" data-value="flexible">
          <span style="display:flex;flex-direction:column;align-items:flex-start;gap:4px;">
            <span>Flexible</span>
            <span class="ff-mono" style="font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:var(--text-dim);">We'll suggest one</span>
          </span>
          <span class="choice-card__dot"></span>
        </button>
      </div>

      <!-- LOGIC: reveal a date picker only when "Specific date" is selected. -->
      <div class="cond-block" data-cond="cohort_pref:specific" style="margin-top:14px;">
        <div class="eyebrow" style="margin-bottom:14px;">// YOUR DEADLINE</div>
        <div class="field" style="margin-bottom:0;">
          <input type="date" name="cohort_target_date" placeholder=" " min="<?= e(date('Y-m-d', strtotime('+1 week'))) ?>">
          <label>Target start date</label>
        </div>
        <p class="caption" style="margin:10px 0 0;">We'll fit you to the cohort closest to that date.</p>
      </div>

      <!-- LOGIC: a gentle reassurance for "flexible" pickers. -->
      <div class="cond-block cond-block--quiet" data-cond="cohort_pref:flexible" style="margin-top:14px;">
        <div class="cond-callout" role="status" aria-live="polite">
          We'll propose a cohort based on your track and time zone after we review your application.
        </div>
      </div>

      <div class="field" style="margin-top:24px;">
        <input type="password" name="password" placeholder=" " data-rules="required|min:8" autocomplete="new-password" data-pw-meter>
        <label>Set a password (for your future cohort dashboard)</label>
        <div class="pw-strength" data-pw-strength><span></span><span></span><span></span></div>
        <div class="pw-strength__label" data-pw-strength-label>// Strength · awaiting input</div>
      </div>
      <div class="field">
        <input type="password" name="password_confirm" placeholder=" " data-rules="required|min:8" autocomplete="new-password">
        <label>Confirm password</label>
      </div>

      <label style="display:flex;align-items:flex-start;gap:12px;font-size:13.5px;line-height:1.55;color:var(--text-mute);margin-top:16px;cursor:pointer;">
        <input type="checkbox" name="marketing_opt_in" value="1" checked style="margin-top:3px;flex-shrink:0;">
        <span>Send me Field notes — one editorial letter a month, no spam.</span>
      </label>

      <p class="caption" style="margin:18px 0 0;">
        By submitting, you agree to our <a class="nav-link" href="#" style="font-size:13px;">terms</a>
        and <a class="nav-link" href="#" style="font-size:13px;">privacy notice</a>.
      </p>

      <div data-failure class="helper" style="display:none;color:var(--crimson);margin-top:12px;"></div>

      <div class="onboarding__nav">
        <button type="button" class="btn btn-ghost" data-step-prev>← Back</button>
        <button type="submit" class="btn btn-primary">Submit application <?= icon_chev() ?></button>
      </div>
    </fieldset>

    <!-- Save & continue — sits below the wizard at all times -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:24px;padding:14px 18px;border:1px dashed var(--hairline);border-radius:14px;color:var(--text-mute);font-size:13px;">
      <span data-save-status>// Auto-saved locally as you go. Resume anytime on this device.</span>
      <button type="button" class="nav-link" style="font-size:13px;background:transparent;border:0;color:var(--crimson);cursor:pointer;" data-save-clear>Clear &amp; start over</button>
    </div>
  </form>
</div>
