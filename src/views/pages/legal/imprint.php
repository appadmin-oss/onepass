<?php
/** Imprint — corporate disclosure required under §16 Companies and Allied
 *  Matters Act 2020 (CAMA) and §5 of the EU e-Commerce Directive (relevant
 *  to EU visitors). */
$sections = [
    ['id' => 'who', 'h' => 'Provider of this website',
     'body' => '<p><strong>Afrostrength Limited</strong><br>
                A private company limited by shares, incorporated in the Federal Republic of Nigeria under the Companies and Allied Matters Act 2020 (CAMA).</p>'],

    ['id' => 'registered-office', 'h' => 'Registered office',
     'body' => '<p>CACENTRE, 2 Abolude/Oremeji Street,<br>
                Bakery Bus Stop, Egbeda, Alimosho,<br>
                Lagos State, Nigeria.</p>
              <p>A second studio operates from CACENTRE, Ayobo (Lagos State).</p>'],

    ['id' => 'contact', 'h' => 'Contact',
     'body' => '<ul>
                <li><strong>Email:</strong> <a href="mailto:afrostrength@gmail.com">afrostrength@gmail.com</a></li>
                <li><strong>Phone:</strong> +234-810-019-1456 (Mon–Fri, 09:00–18:00 WAT).</li>
                <li><strong>For legal &amp; data-protection requests:</strong> email with subject line <em>"Legal — &lt;your topic&gt;"</em>. We respond within 7 working days.</li>
              </ul>'],

    ['id' => 'responsible', 'h' => 'Person responsible for content',
     'body' => '<p>Editorial and operational responsibility for afrostrength.com (per §55 CAMA, and for EU visitors per §5 e-Commerce Directive transposed into national law):</p>
              <p>The Managing Director, Afrostrength Limited, at the registered office above.</p>'],

    ['id' => 'regulatory', 'h' => 'Regulatory information',
     'body' => '<ul>
                <li><strong>Place of registration:</strong> Corporate Affairs Commission (CAC), Federal Republic of Nigeria.</li>
                <li><strong>Tax authority:</strong> Federal Inland Revenue Service (FIRS) — Lagos.</li>
                <li><strong>Data-protection supervisory authority:</strong> Nigeria Data Protection Commission (NDPC), <a href="https://ndpc.gov.ng" target="_blank" rel="noopener">ndpc.gov.ng</a>.</li>
                <li><strong>Consumer-protection authority:</strong> Federal Competition &amp; Consumer Protection Commission (FCCPC), <a href="https://fccpc.gov.ng" target="_blank" rel="noopener">fccpc.gov.ng</a>.</li>
              </ul>'],

    ['id' => 'dispute', 'h' => 'Online dispute resolution',
     'body' => '<p>Disputes arising from these services are governed by Nigerian law and resolved as set out in our <a href="' . url('/legal/terms') . '">Terms of Service</a>. For EU consumers, the European Commission provides an Online Dispute Resolution platform at <a href="https://ec.europa.eu/consumers/odr/" target="_blank" rel="noopener">ec.europa.eu/consumers/odr</a>. We are not, however, obliged to participate in any consumer-arbitration scheme.</p>'],

    ['id' => 'liability-links', 'h' => 'Liability for external links',
     'body' => '<p>Our website contains links to third-party sites we do not control. We are not responsible for the content, accuracy or privacy practices of any linked site. Links are checked at the time of publication; if a link later becomes harmful or inappropriate, email us and we will remove it.</p>'],

    ['id' => 'copyright', 'h' => 'Copyright',
     'body' => '<p>Unless otherwise stated, the design, code, illustrations, photographs and editorial content on afrostrength.com are © Afrostrength Limited. The Afrostrength and Afrotech Academy word-marks and the Africa-in-circle logo are trade marks of Afrostrength Limited. Other names, logos, and trade marks are the property of their respective owners and are used by reference.</p>
              <p>Open-source components are used under their respective licences (see <a href="' . url('/legal/terms') . '">Terms §8 Third-party services</a>).</p>'],

    ['id' => 'security', 'h' => 'Vulnerability disclosure',
     'body' => '<p>If you discover a security vulnerability, please report it privately to <a href="mailto:afrostrength@gmail.com">afrostrength@gmail.com</a> with subject line <em>"Security — disclosure"</em>. We will acknowledge within 3 working days, work in good faith on a fix, and credit you in our changelog if you wish. Do not exploit the vulnerability or disclose it publicly before we have a fix in place.</p>'],
];
partial('legal-shell', [
    'heading'   => 'Imprint & company information',
    'effective' => 'Effective: ' . date('j F Y'),
    'intro'     => 'Corporate disclosure required under §16 CAMA (Nigeria) and §5 of the EU e-Commerce Directive. If you need a copy of our incorporation certificate or VAT details, email <a href="mailto:afrostrength@gmail.com">afrostrength@gmail.com</a>.',
    'sections'  => $sections,
    'breadcrumbs' => $breadcrumbs ?? [],
]);
?>
