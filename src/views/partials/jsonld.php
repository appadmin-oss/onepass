<?php
/**
 * Structured-data blocks. Always renders Organization + WebSite +
 * LocalBusiness for the studio, then merges any page-specific blocks
 * passed via $jsonld and a BreadcrumbList derived from $breadcrumbs.
 *
 * @var array $jsonld
 * @var array $breadcrumbs
 */

$blocks = [];

// 1. Organization
$blocks[] = [
    '@context' => 'https://schema.org',
    '@type'    => 'Organization',
    'name'     => AFS_NAME,
    'legalName'=> 'Afrostrength Limited',
    'url'      => AFS_URL,
    'logo'     => url('/assets/images/logo/3.png'),
    'slogan'   => AFS_TAGLINE,
    'sameAs'   => [
        'https://instagram.com/afrostrength',
        'https://linkedin.com/company/afrostrength',
        'https://x.com/afrostrength',
    ],
    'contactPoint' => [
        '@type'        => 'ContactPoint',
        'contactType'  => 'customer service',
        'telephone'    => '+234-810-019-1456',
        'email'        => 'afrostrength@gmail.com',
        'areaServed'   => ['NG', 'GB', 'US', 'ZA', 'KE', 'GH'],
        'availableLanguage' => ['en'],
    ],
];

// 2. WebSite with SearchAction (Sitelinks search box)
$blocks[] = [
    '@context' => 'https://schema.org',
    '@type'    => 'WebSite',
    'url'      => AFS_URL,
    'name'     => AFS_NAME,
    'potentialAction' => [
        '@type'  => 'SearchAction',
        'target' => AFS_URL . '/blog?q={search_term_string}',
        'query-input' => 'required name=search_term_string',
    ],
];

// 3. LocalBusiness — two Lagos branches
$blocks[] = [
    '@context'  => 'https://schema.org',
    '@type'     => 'LocalBusiness',
    '@id'       => AFS_URL . '#hq',
    'name'      => 'Afrostrength Limited · Egbeda',
    'image'     => url('/assets/images/logo/3.png'),
    'telephone' => '+234-810-019-1456',
    'email'     => 'afrostrength@gmail.com',
    'priceRange'=> '₦₦',
    'address'   => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => 'CACENTRE, 2 Abolude/Oremeji Street, Bakery Bus Stop',
        'addressLocality' => 'Egbeda',
        'addressRegion'   => 'Lagos',
        'addressCountry'  => 'NG',
    ],
    'geo' => ['@type' => 'GeoCoordinates', 'latitude' => 6.5876, 'longitude' => 3.2829],
    'openingHoursSpecification' => [
        '@type' => 'OpeningHoursSpecification',
        'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday'],
        'opens' => '09:00', 'closes' => '18:00',
    ],
    'url' => AFS_URL,
];
$blocks[] = [
    '@context'  => 'https://schema.org',
    '@type'     => 'LocalBusiness',
    '@id'       => AFS_URL . '#ayobo',
    'name'      => 'Afrostrength Limited · Ayobo',
    'image'     => url('/assets/images/logo/3.png'),
    'telephone' => '+234-810-019-1456',
    'address'   => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => 'CACENTRE, Ayobo',
        'addressLocality' => 'Ayobo',
        'addressRegion'   => 'Lagos',
        'addressCountry'  => 'NG',
    ],
    'parentOrganization' => ['@type' => 'Organization', 'name' => AFS_NAME],
];

// 4. EducationalOrganization — Afrotech Academy
$blocks[] = [
    '@context' => 'https://schema.org',
    '@type'    => 'EducationalOrganization',
    'name'     => 'Afrotech Academy',
    'parentOrganization' => ['@type' => 'Organization', 'name' => AFS_NAME],
    'url'      => AFS_URL . '/academy',
    'slogan'   => 'Learn smartly. Build cleanly. Get certified.',
];

// 5. BreadcrumbList from $breadcrumbs (set by every controller)
if (!empty($breadcrumbs) && is_array($breadcrumbs)) {
    $items = [];
    foreach ($breadcrumbs as $i => $crumb) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => $crumb['label'] ?? '',
            'item'     => !empty($crumb['href']) ? $crumb['href'] : url(current_path()),
        ];
    }
    $blocks[] = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    ];
}

// 6. Page-specific blocks (Service / Course / Article / FAQ — supplied by controller)
if (!empty($jsonld) && is_array($jsonld)) {
    foreach ($jsonld as $node) $blocks[] = $node;
}

// Emit one script block per node (Google prefers individual blocks).
foreach ($blocks as $b) {
    echo '<script type="application/ld+json">' . json_encode($b, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
}
