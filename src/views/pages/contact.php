<?php
/** @var array $services */
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

partial('subpage-hero', ['hero' => [
    'eyebrow'      => '// CONTACT',
    'title'        => 'Tell us about <em>the brief.</em>',
    'sub'          => 'We read every brief within a working day. Use the fast form for a quick hello, or step through the consultation flow if you have a clearer idea.',
    'cta_label'    => 'Skip to the form',
    'cta_href'     => '#form',
    'illustration' => 'figure-phone',
]]);
?>

<section id="form" class="grid-split" id="form" style="margin-top:24px;">

  <!-- Fast contact form -->
  <div class="hairline" style="padding:32px;border-radius:18px;background:var(--bone);" data-reveal>
    <div class="eyebrow" style="margin-bottom:24px;">// FAST CONTACT</div>

    <form data-form data-smart data-smart-key="contact-fast"
          action="<?= e(url('/api/inquiries')) ?>" method="post" novalidate>
      <input type="hidden" name="kind" value="contact">
      <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
      <?= Security::honeypotField('website') ?>

      <div class="field">
        <input type="text" name="name" placeholder=" " data-rules="required|min:2" autocomplete="name">
        <label>Full name</label>
        <div class="check"><svg width="11" height="11" viewBox="0 0 11 11" fill="none" stroke="#fff" stroke-width="1.8"><path d="M2 5.5l2.5 2.5L9 3" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
      </div>
      <div class="field">
        <input type="email" name="email" placeholder=" " data-rules="required|email" autocomplete="email" data-smart-email>
        <label>Work email</label>
        <div class="check"><svg width="11" height="11" viewBox="0 0 11 11" fill="none" stroke="#fff" stroke-width="1.8"><path d="M2 5.5l2.5 2.5L9 3" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
      </div>
      <div class="field">
        <input type="text" name="company" placeholder=" " autocomplete="organization">
        <label>Company / Organisation</label>
      </div>
      <div class="field">
        <textarea rows="4" name="message" placeholder=" "
                  data-rules="required|min:8"
                  data-smart-counter data-smart-min="40" data-smart-max="600"></textarea>
        <label>One line on the goal</label>
      </div>

      <div class="contact-form__polish">
        <button type="button"
                data-smart-suggest="message"
                data-smart-intent="brief_polish"
                aria-label="Rewrite my brief in our brand voice">
          ✨ AI polish my brief
        </button>
        <span class="contact-form__polish-note">Sends only the text above — never your name or email.</span>
      </div>

      <div data-failure class="helper" style="display:none;color:var(--crimson);"></div>
      <div data-success style="display:none;padding:18px;background:rgba(31,138,91,0.08);border:1px solid rgba(31,138,91,0.25);border-radius:12px;color:#1F8A5B;font-size:14px;">
        Thanks — we read every brief within a working day.
      </div>

      <button type="submit" class="btn btn-primary" style="margin-top:8px;">Send brief <?= icon_chev() ?></button>
    </form>
  </div>

  <!-- Multi-step consultation -->
  <div data-reveal>
    <div class="hairline" style="padding:24px;border-radius:18px;background:var(--bone);margin-bottom:16px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        <div class="eyebrow">CONSULTATION FLOW</div>
        <div class="ff-mono" style="font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:rgba(10,10,10,0.55);" data-step-label>01 of 03</div>
      </div>
      <div class="step-bar">
        <div class="seg active"></div>
        <div class="seg"></div>
        <div class="seg"></div>
      </div>
      <div style="display:flex;justify-content:space-between;margin-top:10px;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:rgba(10,10,10,0.55);">
        <span>Service + Goal</span><span>Business Info</span><span>Contact + Time</span>
      </div>
    </div>

    <form data-form action="<?= e(url('/api/inquiries')) ?>" method="post" novalidate>
      <input type="hidden" name="kind" value="consultation">
      <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
      <?= Security::honeypotField('website') ?>

      <!-- Step 1: Service + Goal -->
      <div data-step="1">
        <div class="hairline" style="padding:24px;border-radius:18px;background:var(--bone);">
          <div class="eyebrow" style="margin-bottom:14px;">// SERVICE</div>
          <div data-choice-group="service" style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;">
            <?php foreach ($services as $svc): ?>
              <button type="button" class="choice-card" data-value="<?= e($svc['slug']) ?>">
                <span><?= e($svc['name']) ?></span>
                <span class="choice-card__dot"></span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="hairline cond-block" data-cond="service:brand-development">
          <div class="eyebrow" style="margin-bottom:14px;">// BRAND STAGE</div>
          <div data-choice-group="brand_stage" class="choice-row">
            <button type="button" class="choice-card" data-value="pre-launch">Pre-launch</button>
            <button type="button" class="choice-card" data-value="rebrand">Rebrand</button>
            <button type="button" class="choice-card" data-value="refresh">Refresh</button>
          </div>
          <div class="field" style="margin-top:14px;margin-bottom:0;">
            <input type="url" name="existing_assets_url" placeholder=" " data-auto-https>
            <label>Link to existing brand assets (optional)</label>
          </div>
        </div>

        <div class="hairline cond-block" data-cond="service:creative-design">
          <div class="eyebrow" style="margin-bottom:14px;">// DELIVERABLE TYPE</div>
          <div data-choice-group="design_kind" class="choice-row">
            <button type="button" class="choice-card" data-value="packaging">Packaging</button>
            <button type="button" class="choice-card" data-value="editorial">Editorial</button>
            <button type="button" class="choice-card" data-value="environmental">Environmental</button>
            <button type="button" class="choice-card" data-value="digital">Digital product</button>
          </div>
        </div>

        <div class="hairline cond-block" data-cond="service:media-solutions">
          <div class="eyebrow" style="margin-bottom:14px;">// MEDIA FORMAT</div>
          <div data-choice-group="media_format" class="choice-row">
            <button type="button" class="choice-card" data-value="photo">Photography</button>
            <button type="button" class="choice-card" data-value="film">Film / video</button>
            <button type="button" class="choice-card" data-value="podcast">Podcast</button>
            <button type="button" class="choice-card" data-value="editorial">Editorial / docs</button>
          </div>
        </div>

        <div class="hairline cond-block" data-cond="service:project-event-management">
          <div class="eyebrow" style="margin-bottom:14px;">// EVENT DETAILS</div>
          <div class="field">
            <input type="text" name="event_size" placeholder=" " inputmode="numeric">
            <label>Expected attendees</label>
          </div>
          <div class="field" style="margin-bottom:0;">
            <input type="date" name="event_date" placeholder=" ">
            <label>Target date</label>
          </div>
        </div>

        <div class="hairline cond-block" data-cond="service:digital-solutions">
          <div class="eyebrow" style="margin-bottom:14px;">// PRODUCT TYPE</div>
          <div data-choice-group="digital_kind" class="choice-row">
            <button type="button" class="choice-card" data-value="website">Marketing site</button>
            <button type="button" class="choice-card" data-value="webapp">Web app</button>
            <button type="button" class="choice-card" data-value="mobile">Mobile app</button>
            <button type="button" class="choice-card" data-value="platform">Platform / SaaS</button>
            <button type="button" class="choice-card" data-value="integration">Integration / API</button>
          </div>
          <div class="field" style="margin-top:14px;margin-bottom:0;">
            <input type="text" name="existing_stack" placeholder=" ">
            <label>Current stack, if any (optional)</label>
          </div>
        </div>

        <div class="hairline cond-block" data-cond="service:client-training">
          <div class="eyebrow" style="margin-bottom:14px;">// TRAINING SCOPE</div>
          <div class="field">
            <input type="text" name="team_size" placeholder=" " inputmode="numeric">
            <label>Team size to train</label>
          </div>
          <div data-choice-group="training_topic" class="choice-row" style="margin-top:14px;">
            <button type="button" class="choice-card" data-value="design">Design / Figma</button>
            <button type="button" class="choice-card" data-value="brand">Brand strategy</button>
            <button type="button" class="choice-card" data-value="content">Content + editorial</button>
            <button type="button" class="choice-card" data-value="ops">Studio ops</button>
          </div>
        </div>

        <!-- Universal reveal once any service is chosen: budget tier + timeline urgency. -->
        <div class="hairline cond-block" data-cond="service:brand-development,creative-design,media-solutions,project-event-management,digital-solutions,client-training">
          <div class="eyebrow" style="margin-bottom:14px;">// BUDGET &amp; TIMELINE</div>
          <div data-choice-group="budget_tier" class="choice-row" aria-label="Budget tier (NGN)">
            <button type="button" class="choice-card" data-value="under-1m">&lt; ₦1M</button>
            <button type="button" class="choice-card" data-value="1-3m">₦1–3M</button>
            <button type="button" class="choice-card" data-value="3-8m">₦3–8M</button>
            <button type="button" class="choice-card" data-value="8m-plus">₦8M+</button>
            <button type="button" class="choice-card" data-value="unsure">Not sure yet</button>
          </div>
          <div data-choice-group="urgency" class="choice-row" style="margin-top:12px;" aria-label="Timeline urgency">
            <button type="button" class="choice-card" data-value="now">Now (≤ 4 weeks)</button>
            <button type="button" class="choice-card" data-value="quarter">This quarter</button>
            <button type="button" class="choice-card" data-value="exploring">Exploring</button>
          </div>
        </div>

        <div class="cond-block" data-cond="urgency:now">
          <div class="cond-callout" role="status" aria-live="polite">
            ⏱ Tight timelines run on our priority lane. Expect a same-day reply.
          </div>
        </div>

        <div class="field" style="margin-top:24px;">
          <textarea rows="3" name="message" placeholder=" "
                    data-rules="required|min:8"
                    data-smart-counter data-smart-min="40" data-smart-max="700"></textarea>
          <label>One line on the goal</label>
        </div>

        <div style="margin-top:16px;display:flex;justify-content:flex-end;">
          <button type="button" class="btn btn-primary" data-step-next>Continue <?= icon_chev() ?></button>
        </div>
      </div>

      <!-- Step 2: Business Info -->
      <div data-step="2" style="display:none;">
        <div class="hairline" style="padding:24px;border-radius:18px;background:var(--bone);">
          <div class="eyebrow" style="margin-bottom:14px;">// BUSINESS INFO</div>
          <div class="field">
            <input type="text" name="company" placeholder=" ">
            <label>Company / Organisation</label>
          </div>
          <div class="field" style="margin-bottom:0;">
            <input type="text" name="inquiry_type" placeholder=" ">
            <label>What stage is your business?</label>
          </div>
        </div>
        <div style="margin-top:16px;display:flex;justify-content:space-between;">
          <button type="button" class="btn btn-ghost" data-step-prev>← Back</button>
          <button type="button" class="btn btn-primary" data-step-next>Continue <?= icon_chev() ?></button>
        </div>
      </div>

      <!-- Step 3: Contact + Time -->
      <div data-step="3" style="display:none;">
        <div class="hairline" style="padding:24px;border-radius:18px;background:var(--bone);">
          <div class="eyebrow" style="margin-bottom:14px;">// CONTACT + TIME</div>
          <div class="field">
            <input type="text" name="name" placeholder=" " data-rules="required|min:2" autocomplete="name">
            <label>Your name</label>
          </div>
          <div class="field">
            <input type="email" name="email" placeholder=" " data-rules="required|email" autocomplete="email">
            <label>Work email</label>
          </div>
          <div class="field">
            <input type="tel" name="phone" placeholder=" " autocomplete="tel">
            <label>Phone (optional)</label>
          </div>
          <div class="field" style="margin-bottom:0;">
            <input type="text" name="preferred_time" placeholder=" ">
            <label>Preferred time for a call</label>
          </div>
        </div>

        <div data-failure class="helper" style="display:none;color:var(--crimson);margin-top:16px;"></div>
        <div data-success style="display:none;padding:18px;background:rgba(31,138,91,0.08);border:1px solid rgba(31,138,91,0.25);border-radius:12px;color:#1F8A5B;font-size:14px;margin-top:16px;">
          Thanks — we read every brief within a working day.
        </div>

        <div style="margin-top:16px;display:flex;justify-content:space-between;align-items:center;">
          <button type="button" class="btn btn-ghost" data-step-prev>← Back</button>
          <button type="submit" class="btn btn-primary">Send brief <?= icon_chev() ?></button>
        </div>
      </div>
    </form>
  </div>
</section>

<!-- Studio details -->
<section data-reveal data-stagger="80" class="grid-cols-4 section-block">
  <?php $cards = [
      ['label' => 'Phone',   'value' => '+234 810 019 1456', 'href' => 'tel:+2348100191456'],
      ['label' => 'Email',   'value' => 'afrostrength@gmail.com', 'href' => 'mailto:afrostrength@gmail.com'],
      ['label' => 'Social',  'value' => '@afrostrength', 'href' => 'https://instagram.com/afrostrength'],
      ['label' => 'Address', 'value' => 'Egbeda, Alimosho, Lagos', 'href' => null],
  ]; foreach ($cards as $c): ?>
    <?php if ($c['href']): ?>
      <a class="hairline" href="<?= e($c['href']) ?>" style="padding:24px;border-radius:14px;display:block;text-decoration:none;color:inherit;transition:border-color .2s ease;" data-reveal onmouseover="this.style.borderColor='var(--ink)'" onmouseout="this.style.borderColor='var(--hairline)'">
        <div class="eyebrow"><?= e('// ' . strtoupper($c['label'])) ?></div>
        <div class="ff-display" style="font-weight:600;font-size:17px;margin-top:8px;"><?= e($c['value']) ?></div>
      </a>
    <?php else: ?>
      <div class="hairline" style="padding:24px;border-radius:14px;" data-reveal>
        <div class="eyebrow"><?= e('// ' . strtoupper($c['label'])) ?></div>
        <div class="ff-display" style="font-weight:600;font-size:17px;margin-top:8px;"><?= e($c['value']) ?></div>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>
</section>

<p class="caption" style="text-align:center;margin:-24px 0 64px;">
  CACENTRE, 2 Abolude/Oremeji Street, Bakery Bus Stop, Egbeda, Alimosho, Lagos
</p>
