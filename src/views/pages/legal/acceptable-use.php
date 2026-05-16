<?php
/** Acceptable Use Policy — operative rules for everyone who uses
 *  afrostrength.com, the Afrotech Academy, and our free tools. */
$sections = [
    ['id' => 'scope', 'h' => 'Scope',
     'body' => '<p>This Acceptable Use Policy ("AUP") sets out conduct rules for anyone using <a href="' . url('/') . '">afrostrength.com</a>, including the free tools at <a href="' . url('/tools') . '">/tools</a>, the AI assistant, the short-link service, the contact and application forms, the Afrotech Academy learning platform (Moodle) and live classes (Jitsi). It complements our <a href="' . url('/legal/terms') . '">Terms of Service</a>. By using these services you agree to follow this AUP.</p>'],

    ['id' => 'prohibited-content', 'h' => 'Prohibited content',
     'body' => '<p>You must not use our services to upload, transmit, link to, store, or generate content that:</p>
              <ul>
                <li>Is unlawful, defamatory, fraudulent, threatening, harassing, or invasive of another\'s privacy.</li>
                <li>Sexually exploits, endangers, or targets a minor in any way. We report suspected child sexual abuse material to the appropriate Nigerian and international authorities and preserve evidence.</li>
                <li>Incites or glorifies violence, terrorism, genocide, or hatred against a person or group on the basis of race, ethnicity, national origin, religion, gender, sexual orientation, disability, age, or any other protected characteristic.</li>
                <li>Infringes anyone\'s intellectual property, trade-secret, or publicity rights.</li>
                <li>Contains malware, ransomware, spyware, cryptominers, or other harmful code.</li>
                <li>Promotes pyramid schemes, romance or investment scams, or other deceptive financial practices.</li>
                <li>Misrepresents your identity or your relationship to Afrostrength, the Afrotech Academy, or any third party (including impersonating staff, instructors, or partners).</li>
                <li>Violates Nigerian law, the law applicable where you reside, or international sanctions.</li>
              </ul>'],

    ['id' => 'prohibited-conduct', 'h' => 'Prohibited conduct',
     'body' => '<p>Don\'t use our services to:</p>
              <ul>
                <li>Probe, scan or test the vulnerability of our systems without our prior written consent; or breach or attempt to breach any security or authentication measure.</li>
                <li>Access, tamper with, or use a non-public area of the services, including admin endpoints, other users\' accounts, or our infrastructure.</li>
                <li>Interfere with the operation of the services — for example by sending automated traffic, scraping at unreasonable rates, denial-of-service, or load attacks.</li>
                <li>Reverse-engineer, decompile or extract source code from any part of the services, except to the extent applicable law expressly permits.</li>
                <li>Resell, sublicense, or otherwise commercialise the free tools or their output, except as permitted by their own terms.</li>
                <li>Use scrapers, bots, or other automated means to collect personal data (yours or anyone else\'s) from the services.</li>
              </ul>'],

    ['id' => 'short-links', 'h' => 'Short-link tool — specific rules',
     'body' => '<p>The free <a href="' . url('/tools/short-link') . '">short-link generator</a> is for legitimate productivity and brand use. You must not create short links that:</p>
              <ul>
                <li>Resolve to malware, phishing, fraud, or any of the prohibited content above.</li>
                <li>Disguise the destination in a way that materially deceives the visitor (e.g. claiming to be a known bank, government agency, or news site you don\'t represent).</li>
                <li>Are part of mass-emailing, SMS spam, or unsolicited bulk distribution.</li>
                <li>Bypass another platform\'s URL filter or content-moderation rules.</li>
              </ul>
              <p>We may disable, redirect to a warning page, or delete any short link at our sole discretion and without notice. We may publish anonymised abuse statistics; we will not publish your personal data.</p>'],

    ['id' => 'ai', 'h' => 'AI tools — specific rules',
     'body' => '<p>The AI assistant and AI-powered "suggest" buttons exist to help you write faster. Don\'t use them to:</p>
              <ul>
                <li>Generate prohibited content (see above).</li>
                <li>Produce content that misrepresents the assistant\'s output as Afrostrength\'s editorial or legal opinion.</li>
                <li>Submit personal data of third parties without their knowledge or a lawful basis.</li>
                <li>Attempt prompt-injection or jailbreaking attacks against the assistant. We log abusive prompts.</li>
                <li>Run automated workloads against the AI endpoints. They are session-rate-limited; misuse will result in throttling or an immediate block.</li>
              </ul>'],

    ['id' => 'academy', 'h' => 'Afrotech Academy — code of conduct',
     'body' => '<p>The Academy is a working environment. As a Student you agree to:</p>
              <ul>
                <li>Treat instructors, mentors and fellow students with respect, both in live sessions and on Moodle.</li>
                <li>Submit your own work. Collaboration is welcome and we love AI-assisted work — but it must be disclosed, and you must be able to explain it.</li>
                <li>Refrain from sharing or reselling cohort materials, recordings, or assessment content outside the cohort without our written consent.</li>
                <li>Use the live-class platform (Jitsi/8x8 JaaS) only for cohort-related sessions; no harassment, hate speech, recording-without-consent, or sharing of personal information.</li>
                <li>Report incidents (harassment, security issues, inappropriate behaviour) to <a href="mailto:afrostrength@gmail.com">afrostrength@gmail.com</a> with subject line <em>"Academy — incident report"</em>.</li>
              </ul>'],

    ['id' => 'reporting', 'h' => 'Reporting abuse',
     'body' => '<p>If you believe content or activity on our services breaches this AUP, email <a href="mailto:afrostrength@gmail.com">afrostrength@gmail.com</a> with subject line <em>"AUP — abuse report"</em>, the URL or short-code, and a short description. We aim to acknowledge reports within 2 working days.</p>
              <p>For copyright-infringement complaints, include a description of the work, the infringing URL, your contact details, and a good-faith statement that you are authorised to act on behalf of the rights-holder.</p>'],

    ['id' => 'enforcement', 'h' => 'Enforcement',
     'body' => '<p>Where we identify a breach of this AUP, we may, at our discretion and proportionate to the conduct:</p>
              <ul>
                <li>Issue a warning.</li>
                <li>Remove the content, disable the link, revoke a token, or block the account.</li>
                <li>Suspend or terminate your access to all or part of the services.</li>
                <li>Cooperate with law-enforcement authorities, including by preserving and disclosing relevant data under lawful process.</li>
              </ul>
              <p>We will, where reasonable and lawful, give you notice and an opportunity to remedy a breach first. We will act without prior notice where the breach is severe (e.g. CSAM, malware, active fraud) or where notice would compromise an investigation.</p>'],

    ['id' => 'changes', 'h' => 'Changes',
     'body' => '<p>We may amend this AUP from time to time. Material changes will be announced via a banner on the site. The effective date above will always reflect the most recent version.</p>'],
];
partial('legal-shell', [
    'heading'   => 'Acceptable Use Policy',
    'effective' => 'Effective: ' . date('j F Y'),
    'intro'     => 'Be the kind of internet citizen you would want to share a network with. Don\'t break the law, don\'t harm other users, and don\'t abuse our infrastructure. The detail below covers specific platforms and how we enforce.',
    'sections'  => $sections,
    'breadcrumbs' => $breadcrumbs ?? [],
]);
?>
