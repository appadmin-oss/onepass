<?php
/** Privacy Policy — drafted to align with the Nigeria Data Protection Act 2023
 *  (NDPA) + the EU General Data Protection Regulation (GDPR). */
$sections = [
    ['id' => 'who-we-are', 'h' => 'Who we are',
     'body' => '<p>This Privacy Policy explains how <strong>Afrostrength Limited</strong>, a private company incorporated in the Federal Republic of Nigeria, collects and processes personal data when you visit <a href="' . url('/') . '">afrostrength.com</a>, use any service we provide, or engage with the Afrotech Academy. References to "Afrostrength", "we", "us" or "our" refer to Afrostrength Limited.</p>
              <p><strong>Registered office:</strong> CACENTRE, 2 Abolude/Oremeji Street, Bakery Bus Stop, Egbeda, Alimosho, Lagos State, Nigeria.</p>
              <p><strong>Contact:</strong> <a href="mailto:afrostrength@gmail.com">afrostrength@gmail.com</a> · +234-810-019-1456.</p>
              <p><strong>Our role:</strong> for personal data you submit through this site, Afrostrength acts as the data controller. We engage a small number of vetted processors (listed in §6) under written instructions.</p>'],

    ['id' => 'data-we-collect', 'h' => 'What personal data we collect',
     'body' => '<p>We collect only what we need to provide our services, communicate with you, comply with law, and operate our website securely.</p>
              <ul>
                <li><strong>Data you give us directly</strong> — when you fill in a form (contact, application, enrolment, newsletter, short-link, account sign-up): name, email, phone, country/city, age range, current role, password (one-way hashed with bcrypt), free-text answers, portfolio links, and the files you choose to upload.</li>
                <li><strong>Data we collect automatically</strong> — IP address, browser user-agent, the page you visited, the page that referred you, and timestamps. Stored only as long as needed for security, abuse prevention, and accurate analytics aggregates.</li>
                <li><strong>Data from third parties</strong> — if you submit a brief that names another party, we will treat that party\'s details with the same care we treat yours.</li>
                <li><strong>Special categories of personal data</strong> — we do not knowingly collect health data, biometric data, sexual orientation, religious belief or political opinion. Do not submit these to us through our forms.</li>
              </ul>'],

    ['id' => 'lawful-bases', 'h' => 'Why we process your data (lawful basis)',
     'body' => '<p>Under the NDPA and (where applicable) GDPR, we rely on the following lawful bases:</p>
              <ul>
                <li><strong>Contract</strong> — to deliver services you ask for (e.g. confirm an application, run a cohort, ship a project, send your short-link link).</li>
                <li><strong>Legitimate interests</strong> — to keep our website secure, prevent abuse, understand how our site is used in aggregate, and reply to enquiries you initiate. We balance these against your rights and do not use this basis where it would override them.</li>
                <li><strong>Consent</strong> — for our editorial newsletter (Field notes), non-essential cookies, and marketing communications. You can withdraw consent at any time.</li>
                <li><strong>Legal obligation</strong> — to comply with tax, accounting, anti-money-laundering and other obligations under Nigerian and applicable foreign law.</li>
              </ul>'],

    ['id' => 'how-we-use', 'h' => 'How we use your data',
     'body' => '<ul>
                <li>To respond to your enquiries and brief submissions, including routing them to the right operator.</li>
                <li>To process your Afrotech Academy application, match you to a cohort, run admissions, and (if accepted) provide course access.</li>
                <li>To operate the free tools — short links, QR generator, focus timer, palette extractor, salary calculator and career-path quiz — and improve them based on aggregate usage.</li>
                <li>To send you transactional messages (e.g. seat confirmation, cohort orientation) which are necessary to deliver our services.</li>
                <li>To send our newsletter <em>only</em> if you have opted in. Each issue includes a one-click unsubscribe.</li>
                <li>To run security, abuse-prevention and fraud-detection on our infrastructure.</li>
                <li>To comply with our legal obligations and defend our legal rights.</li>
              </ul>'],

    ['id' => 'cookies-sdks', 'h' => 'Cookies, local storage & similar technologies',
     'body' => '<p>We use the minimum cookies needed to operate the site. See our <a href="' . url('/legal/cookies') . '">Cookie Policy</a> for the full list, retention, and opt-out instructions. Where consent is required we ask for it explicitly via the cookie banner and do not set non-essential cookies before you accept.</p>'],

    ['id' => 'sharing', 'h' => 'Who we share your data with',
     'body' => '<p>We do not sell personal data. We share it only with the following categories of recipients, under written agreements that limit them to processing on our instructions:</p>
              <ul>
                <li><strong>Infrastructure providers</strong> — hosting, content-delivery, email-delivery (PHPMailer over SMTP). Currently this includes our hosting provider and an SMTP service we configure per project.</li>
                <li><strong>Education platform</strong> — Moodle (self-hosted by Afrostrength) for cohort coursework. Operates under the same controls as the rest of our infrastructure.</li>
                <li><strong>Live-class platform</strong> — Jitsi, including the open-source meet.jit.si or our private 8x8 JaaS tenant. Subject to Jitsi/8x8 privacy terms.</li>
                <li><strong>QR generation</strong> — api.qrserver.com, used only when you choose to generate a QR. Sends only the data you encode.</li>
                <li><strong>AI assistant</strong> — Pollinations.ai, used only when you click a "suggest" button or chat with the assistant. We send only the prompt text you provide. We do not send identifying personal data.</li>
                <li><strong>Professional advisers, auditors and regulators</strong> — where legally compelled or where strictly necessary to defend our rights.</li>
              </ul>
              <p>We never share your data with advertising networks.</p>'],

    ['id' => 'international', 'h' => 'International transfers',
     'body' => '<p>Some of our processors operate servers outside Nigeria. Where we transfer personal data abroad, we rely on the data-recipient being either (a) located in a country recognised as providing an adequate level of protection, or (b) bound by appropriate contractual safeguards (standard contractual clauses or equivalent). Where neither is available, we ask for your explicit consent before transferring.</p>'],

    ['id' => 'retention', 'h' => 'How long we keep your data',
     'body' => '<ul>
                <li><strong>Contact / brief enquiries</strong> — 24 months from the last interaction, then deleted unless we are required to retain longer.</li>
                <li><strong>Academy applications</strong> — 36 months from the last cohort decision, so we can re-route you to the next cohort if appropriate.</li>
                <li><strong>Newsletter subscribers</strong> — until you unsubscribe.</li>
                <li><strong>Short-link records</strong> — kept while the short link is active; we delete a short link 24 months after the last redirect.</li>
                <li><strong>Web logs</strong> — 30 days for security, then aggregated.</li>
                <li><strong>Accounting records</strong> — six years, as required by Nigerian tax law.</li>
              </ul>'],

    ['id' => 'your-rights', 'h' => 'Your rights',
     'body' => '<p>You have the right to:</p>
              <ul>
                <li>Access the personal data we hold about you.</li>
                <li>Have inaccurate data corrected.</li>
                <li>Have your data erased ("right to be forgotten"), subject to lawful retention.</li>
                <li>Restrict or object to processing, including profiling.</li>
                <li>Receive your data in a structured, commonly used, machine-readable format ("portability").</li>
                <li>Withdraw consent where we rely on consent.</li>
                <li>Lodge a complaint with the Nigeria Data Protection Commission (NDPC) at <a href="https://ndpc.gov.ng" target="_blank" rel="noopener">ndpc.gov.ng</a>, or your local supervisory authority in the EU/UK.</li>
              </ul>
              <p>To exercise any right, email <a href="mailto:afrostrength@gmail.com">afrostrength@gmail.com</a> with subject line <em>"Data Request"</em>. We respond within 30 days at no charge for reasonable requests.</p>'],

    ['id' => 'children', 'h' => 'Children',
     'body' => '<p>Our services are intended for adults aged 16 and over. We do not knowingly collect personal data from children under 16. If you believe a minor has submitted data, contact us and we will delete it.</p>'],

    ['id' => 'security', 'h' => 'Security',
     'body' => '<p>We apply technical and organisational measures appropriate to the risk:</p>
              <ul>
                <li>HTTPS / TLS on every public surface.</li>
                <li>Passwords stored as bcrypt hashes, never in plain text.</li>
                <li>CSRF tokens on every mutating endpoint.</li>
                <li>Parameterised database access (PDO prepared statements only).</li>
                <li>Strict file-upload validation (size, MIME, extension) for the admin promotions upload.</li>
                <li>Least-privilege access for staff. Admin endpoints sit behind authentication and are never linked from public pages.</li>
              </ul>
              <p>No system is ever fully secure. If you suspect a vulnerability please email us at <a href="mailto:afrostrength@gmail.com">afrostrength@gmail.com</a>.</p>'],

    ['id' => 'changes', 'h' => 'Changes to this policy',
     'body' => '<p>We update this Policy when our practices change or the law requires it. Material changes will be communicated via banner on the site and, where appropriate, by email. The effective date above will always reflect the most recent version.</p>'],
];
partial('legal-shell', [
    'heading'   => 'Privacy Policy',
    'effective' => 'Effective: ' . date('j F Y'),
    'intro'     => '<strong>The short version:</strong> we collect the minimum personal data we need to run our services, never sell it, never share it with advertising networks, and give you full control over your data. Read the detail below or email <a href="mailto:afrostrength@gmail.com">afrostrength@gmail.com</a> with questions.',
    'sections'  => $sections,
    'breadcrumbs' => $breadcrumbs ?? [],
]);
?>
