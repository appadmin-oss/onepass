<?php
/** Cookie Policy — aligned with NDPA 2023 (Nigeria), GDPR (EU) and the
 *  EU ePrivacy Directive (Article 5(3)). Lists every cookie + storage
 *  key we set, who sets it, what it stores, and how long it lives. */
$sections = [
    ['id' => 'what-are-cookies', 'h' => 'What are cookies (and similar technologies)?',
     'body' => '<p>A "cookie" is a small text file a website asks your browser to store, so it can recognise you (or your device) on a return visit. We also use related technologies — <strong>localStorage</strong>, <strong>sessionStorage</strong>, and our own server-side session — that behave similarly. For brevity, this policy uses "cookies" to refer to all of them.</p>
              <p>Cookies are not malware. They are scoped to the site that set them and your browser controls them.</p>'],

    ['id' => 'how-we-use', 'h' => 'How we use cookies on afrostrength.com',
     'body' => '<p>We use the <strong>minimum</strong> cookies needed to operate the site, remember preferences you choose, and (where you consent) measure aggregate usage so we can improve the site. We do <strong>not</strong> use cookies for advertising, retargeting, or behavioural profiling, and we do not share cookie data with advertising networks.</p>
              <p>Every cookie below falls into one of four categories:</p>
              <ul>
                <li><strong>Strictly necessary</strong> — the site can\'t function without these. They are set by us and don\'t require consent under Article 5(3) of the ePrivacy Directive.</li>
                <li><strong>Preferences</strong> — remember a choice you made (theme, locale, dismissed banners). Stored only when you make the choice.</li>
                <li><strong>Analytics</strong> — let us count anonymous, aggregate visits. Only loaded after you accept analytics in the cookie banner.</li>
                <li><strong>Third-party</strong> — set by external tools we embed (only on the pages that use them).</li>
              </ul>'],

    ['id' => 'inventory', 'h' => 'Inventory of cookies we use',
     'body' => '<div class="table-wrap" style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                  <thead>
                    <tr style="text-align:left;border-bottom:1px solid var(--hairline);">
                      <th style="padding:8px 10px;">Name</th>
                      <th style="padding:8px 10px;">Set by</th>
                      <th style="padding:8px 10px;">Category</th>
                      <th style="padding:8px 10px;">Purpose</th>
                      <th style="padding:8px 10px;">Lifetime</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr style="border-bottom:1px solid var(--hairline);">
                      <td style="padding:8px 10px;"><code>PHPSESSID</code></td>
                      <td style="padding:8px 10px;">afrostrength.com</td>
                      <td style="padding:8px 10px;">Strictly necessary</td>
                      <td style="padding:8px 10px;">Maintains your session, CSRF token, and logged-in state.</td>
                      <td style="padding:8px 10px;">Session</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--hairline);">
                      <td style="padding:8px 10px;"><code>afs_theme</code> (localStorage)</td>
                      <td style="padding:8px 10px;">afrostrength.com</td>
                      <td style="padding:8px 10px;">Preferences</td>
                      <td style="padding:8px 10px;">Remembers your light/dark theme choice.</td>
                      <td style="padding:8px 10px;">Until cleared</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--hairline);">
                      <td style="padding:8px 10px;"><code>afs_announce_dismissed</code> (localStorage)</td>
                      <td style="padding:8px 10px;">afrostrength.com</td>
                      <td style="padding:8px 10px;">Preferences</td>
                      <td style="padding:8px 10px;">Hides the top announcement bar after you dismiss it.</td>
                      <td style="padding:8px 10px;">90 days</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--hairline);">
                      <td style="padding:8px 10px;"><code>afs_promo_dismissed</code> (localStorage)</td>
                      <td style="padding:8px 10px;">afrostrength.com</td>
                      <td style="padding:8px 10px;">Preferences</td>
                      <td style="padding:8px 10px;">Hides the cohort countdown promo ribbon for the day.</td>
                      <td style="padding:8px 10px;">24 hours</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--hairline);">
                      <td style="padding:8px 10px;"><code>afs_cookie_consent</code> (localStorage)</td>
                      <td style="padding:8px 10px;">afrostrength.com</td>
                      <td style="padding:8px 10px;">Strictly necessary</td>
                      <td style="padding:8px 10px;">Records your cookie-banner choice so we don\'t ask again.</td>
                      <td style="padding:8px 10px;">12 months</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--hairline);">
                      <td style="padding:8px 10px;"><code>afs_onboarding_v3</code> (localStorage)</td>
                      <td style="padding:8px 10px;">afrostrength.com</td>
                      <td style="padding:8px 10px;">Preferences</td>
                      <td style="padding:8px 10px;">Saves your in-progress Academy application so you can return to it.</td>
                      <td style="padding:8px 10px;">Until cleared</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--hairline);">
                      <td style="padding:8px 10px;"><code>_ga</code>, <code>_ga_*</code></td>
                      <td style="padding:8px 10px;">Google Analytics</td>
                      <td style="padding:8px 10px;">Analytics</td>
                      <td style="padding:8px 10px;">Distinguishes unique visitors; IP-anonymised. Set <em>only</em> if you accept analytics.</td>
                      <td style="padding:8px 10px;">Up to 2 years</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--hairline);">
                      <td style="padding:8px 10px;">Firebase Auth state</td>
                      <td style="padding:8px 10px;">firebaseapp.com (Google)</td>
                      <td style="padding:8px 10px;">Strictly necessary</td>
                      <td style="padding:8px 10px;">Only on the sign-in page; holds the short-lived ID token while we complete Google sign-in.</td>
                      <td style="padding:8px 10px;">Session</td>
                    </tr>
                    <tr>
                      <td style="padding:8px 10px;">Jitsi 8x8 / meet.jit.si</td>
                      <td style="padding:8px 10px;">jit.si / 8x8.vc</td>
                      <td style="padding:8px 10px;">Third-party</td>
                      <td style="padding:8px 10px;">Only on Academy live-class pages; required for the call to connect. Governed by Jitsi/8x8 terms.</td>
                      <td style="padding:8px 10px;">Session</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <p style="font-size:13px;color:var(--text-mute);margin-top:14px;">We will update this table whenever we add or remove a cookie. If you find one that isn\'t listed, email <a href="mailto:afrostrength@gmail.com">afrostrength@gmail.com</a>.</p>'],

    ['id' => 'consent', 'h' => 'How we ask for consent',
     'body' => '<p>The first time you visit the site, a cookie banner explains your choices in plain language. You can:</p>
              <ul>
                <li><strong>Accept all</strong> — analytics is turned on and we set the cookies above.</li>
                <li><strong>Accept only necessary</strong> — analytics stays off; only strictly necessary and preference cookies are stored.</li>
              </ul>
              <p>Until you make a choice, analytics is <em>denied by default</em> via Google Consent Mode. We do not pre-tick any consent box.</p>'],

    ['id' => 'change-mind', 'h' => 'How to change your choice or delete cookies',
     'body' => '<p>You can withdraw consent or change your choice at any time:</p>
              <ul>
                <li><strong>On afrostrength.com</strong> — clear the <code>afs_cookie_consent</code> value in your browser, then refresh; the banner reappears.</li>
                <li><strong>In your browser</strong> — modern browsers let you view, block and delete cookies per site. See your browser\'s help pages: <a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener">Chrome</a>, <a href="https://support.mozilla.org/kb/cookies-information-websites-store-on-your-computer" target="_blank" rel="noopener">Firefox</a>, <a href="https://support.apple.com/guide/safari/manage-cookies-sfri11471" target="_blank" rel="noopener">Safari</a>, <a href="https://support.microsoft.com/microsoft-edge" target="_blank" rel="noopener">Edge</a>.</li>
                <li><strong>Universal opt-outs</strong> — we respect the <code>Sec-GPC</code> (Global Privacy Control) signal and the <code>Do-Not-Track</code> header for analytics.</li>
              </ul>
              <p>Disabling strictly-necessary cookies will break parts of the site (sign-in, application forms, CSRF protection).</p>'],

    ['id' => 'changes', 'h' => 'Changes to this policy',
     'body' => '<p>If we add, remove, or change the purpose of a cookie, we will update this page and adjust the cookie banner so you can review your choice. The effective date will always reflect the most recent version.</p>'],
];
partial('legal-shell', [
    'heading'   => 'Cookie Policy',
    'effective' => 'Effective: ' . date('j F Y'),
    'intro'     => 'We use a small number of cookies to keep the site working, remember preferences you set, and (with your consent) measure aggregate use. No advertising. No third-party tracking. The full inventory is below.',
    'sections'  => $sections,
    'breadcrumbs' => $breadcrumbs ?? [],
]);
?>
