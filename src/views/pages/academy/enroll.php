<?php
/** @var array $courses
 *  @var string $preselected
 */
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

partial('subpage-hero', ['hero' => [
    'eyebrow'      => '// ENROLL',
    'title'        => 'Three steps. <em>A seat in the next cohort.</em>',
    'sub'          => 'Confirm the course, share a few details, and we\'ll route you into the LMS for the seat-purchase flow.',
    'cta_label'    => 'Start enrolling',
    'cta_href'     => '#enrolment',
    'illustration' => 'figure-document',
    'variant'      => 'maroon',
]]);
?>
<a id="enrolment" aria-hidden="true"></a>

<section data-reveal>
  <div class="hairline" style="padding:24px;border-radius:18px;background:var(--bone);margin-bottom:16px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
      <div class="eyebrow">ENROLMENT FLOW</div>
      <div class="ff-mono" style="font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:rgba(10,10,10,0.55);" data-step-label>01 of 03</div>
    </div>
    <div class="step-bar">
      <div class="seg active"></div>
      <div class="seg"></div>
      <div class="seg"></div>
    </div>
    <div style="display:flex;justify-content:space-between;margin-top:10px;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:rgba(10,10,10,0.55);">
      <span>Course</span><span>Your details</span><span>Confirm</span>
    </div>
  </div>

  <form data-form action="<?= e(url('/api/enrollments')) ?>" method="post" novalidate style="max-width:680px;">
    <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
    <input type="hidden" name="course_title" id="course_title" value="">

    <!-- Step 1 -->
    <div data-step="1">
      <div class="hairline" style="padding:24px;border-radius:18px;background:var(--bone);">
        <div class="eyebrow" style="margin-bottom:14px;">// COURSE</div>
        <div data-choice-group="course_id" style="display:grid;grid-template-columns:1fr;gap:8px;">
          <?php foreach ($courses as $c):
            $sel = $preselected !== '' && $c['slug'] === $preselected;
          ?>
            <button type="button" class="choice-card <?= $sel ? 'is-selected' : '' ?>"
                    data-value="<?= e((string)($c['id'] ?? $c['lms_id'] ?? $c['slug'])) ?>"
                    data-title="<?= e($c['title']) ?>"
                    onclick="document.getElementById('course_title').value=this.dataset.title">
              <span style="display:flex;flex-direction:column;align-items:flex-start;gap:4px;">
                <span><?= e($c['title']) ?></span>
                <span class="ff-mono" style="font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:rgba(10,10,10,0.55);"><?= e(strtoupper($c['level'] ?? 'FOUNDATIONS')) ?> · <?= e((string)$c['weeks']) ?> WEEKS · ₦ <?= number_format((int)($c['price_naira'] ?? 0)) ?></span>
              </span>
              <span class="choice-card__dot"></span>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
      <div style="margin-top:16px;display:flex;justify-content:flex-end;">
        <button type="button" class="btn btn-primary" data-step-next>Continue <?= icon_chev() ?></button>
      </div>
    </div>

    <!-- Step 2 -->
    <div data-step="2" style="display:none;">
      <div class="hairline" style="padding:24px;border-radius:18px;background:var(--bone);">
        <div class="eyebrow" style="margin-bottom:14px;">// YOUR DETAILS</div>
        <div class="field">
          <input type="text" name="name" placeholder=" " data-rules="required|min:2" autocomplete="name">
          <label>Full name</label>
        </div>
        <div class="field">
          <input type="email" name="email" placeholder=" " data-rules="required|email" autocomplete="email">
          <label>Email for enrolment</label>
        </div>
        <div class="field" style="margin-bottom:0;">
          <input type="tel" name="phone" placeholder=" " autocomplete="tel">
          <label>Phone (optional)</label>
        </div>
      </div>
      <div style="margin-top:16px;display:flex;justify-content:space-between;">
        <button type="button" class="btn btn-ghost" data-step-prev>← Back</button>
        <button type="button" class="btn btn-primary" data-step-next>Continue <?= icon_chev() ?></button>
      </div>
    </div>

    <!-- Step 3 -->
    <div data-step="3" style="display:none;">
      <div class="hairline" style="padding:32px;border-radius:18px;background:var(--bone);">
        <div class="eyebrow" style="margin-bottom:14px;">// CONFIRM</div>
        <p class="body-l" style="margin:0 0 16px;color:rgba(10,10,10,0.85);">
          Submit your details and we'll route you to the LMS for seat purchase. You'll receive a confirmation email with payment instructions and your cohort start date.
        </p>
        <div data-failure class="helper" style="display:none;color:var(--crimson);"></div>
        <div data-success style="display:none;padding:18px;background:rgba(31,138,91,0.08);border:1px solid rgba(31,138,91,0.25);border-radius:12px;color:#1F8A5B;font-size:14px;">
          Reserved. Check your inbox — we've sent next steps.
        </div>
      </div>
      <div style="margin-top:16px;display:flex;justify-content:space-between;">
        <button type="button" class="btn btn-ghost" data-step-prev>← Back</button>
        <button type="submit" class="btn btn-primary">Submit enrolment <?= icon_chev() ?></button>
      </div>
    </div>
  </form>
</section>
