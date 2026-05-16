<?php
/**
 * Cmd/Ctrl+K palette. Behaviours live in assets/js/app.js, hooked via
 * the data-cmdk* attributes below. The index is emitted as a JSON
 * island so the script can read it without an extra request.
 */
$cmdkIndex = [
    ['label' => 'Home',                    'href' => url('/'),                       'kind' => 'Page'],
    ['label' => 'Services',                'href' => url('/services'),               'kind' => 'Page'],
    ['label' => 'Projects',                'href' => url('/projects'),               'kind' => 'Page'],
    ['label' => 'About',                   'href' => url('/about'),                  'kind' => 'Page'],
    ['label' => 'Field notes',             'href' => url('/blog'),                   'kind' => 'Page'],
    ['label' => 'Testimonials',            'href' => url('/testimonials'),           'kind' => 'Page'],
    ['label' => 'Contact',                 'href' => url('/contact'),                'kind' => 'Page'],
    ['label' => 'FAQ',                     'href' => url('/faq'),                    'kind' => 'Page'],
    ['label' => 'Academy',                 'href' => url('/academy'),                'kind' => 'Academy'],
    ['label' => 'Academy · Courses',       'href' => url('/academy/courses'),        'kind' => 'Academy'],
    ['label' => 'Academy · Live sessions', 'href' => url('/academy/live-sessions'),  'kind' => 'Academy'],
    ['label' => 'Academy · Certifications','href' => url('/academy/certifications'), 'kind' => 'Academy'],
    ['label' => 'Academy · Instructors',   'href' => url('/academy/instructors'),    'kind' => 'Academy'],
    ['label' => 'Academy · Apply',         'href' => url('/academy/apply'),          'kind' => 'Academy'],
    ['label' => 'Verify a certificate',    'href' => url('/verify'),                 'kind' => 'Academy'],
    ['label' => 'Tools · Short link',      'href' => url('/tools/short-link'),       'kind' => 'Tool'],
    ['label' => 'Tools · QR generator',    'href' => url('/tools/qr'),               'kind' => 'Tool'],
    ['label' => 'Tools · UTM builder',     'href' => url('/tools/utm'),              'kind' => 'Tool'],
    ['label' => 'Tools · Palette',         'href' => url('/tools/palette'),          'kind' => 'Tool'],
    ['label' => 'Tools · Contrast checker','href' => url('/tools/contrast'),         'kind' => 'Tool'],
    ['label' => 'Tools · Favicon',         'href' => url('/tools/favicon'),          'kind' => 'Tool'],
    ['label' => 'Tools · OG image',        'href' => url('/tools/og'),               'kind' => 'Tool'],
    ['label' => 'Tools · Regex tester',    'href' => url('/tools/regex'),            'kind' => 'Tool'],
    ['label' => 'Tools · JSON-LD',         'href' => url('/tools/jsonld'),           'kind' => 'Tool'],
    ['label' => 'Tools · YAML / JSON',     'href' => url('/tools/yaml'),             'kind' => 'Tool'],
    ['label' => 'Tools · Diff',            'href' => url('/tools/diff'),             'kind' => 'Tool'],
    ['label' => 'Tools · Hash',            'href' => url('/tools/hash'),             'kind' => 'Tool'],
    ['label' => 'Tools · Lorem ipsum',     'href' => url('/tools/ipsum'),            'kind' => 'Tool'],
    ['label' => 'Tools · Slugify',         'href' => url('/tools/slug'),             'kind' => 'Tool'],
    ['label' => 'Tools · Career path',     'href' => url('/tools/career-path'),      'kind' => 'Tool'],
    ['label' => 'Tools · Salary explorer', 'href' => url('/tools/salary'),           'kind' => 'Tool'],
    ['label' => 'Opportunities',           'href' => url('/opportunities'),          'kind' => 'Page'],
    ['label' => 'System status',           'href' => url('/status'),                 'kind' => 'Page'],
    ['label' => 'Privacy',                 'href' => url('/legal/privacy'),          'kind' => 'Legal'],
    ['label' => 'Terms',                   'href' => url('/legal/terms'),            'kind' => 'Legal'],
    ['label' => 'Cookies',                 'href' => url('/legal/cookies'),          'kind' => 'Legal'],
    ['label' => 'Acceptable use',          'href' => url('/legal/acceptable-use'),   'kind' => 'Legal'],
    ['label' => 'Sign in',                 'href' => url('/login'),                  'kind' => 'Account'],
    ['label' => 'Dashboard',               'href' => url('/dashboard'),              'kind' => 'Account'],
];
?>
<div class="cmdk" data-cmdk aria-hidden="true" role="dialog" aria-modal="true" aria-label="Search the site">
  <div class="cmdk__panel" role="document">
    <input type="search"
           class="cmdk__input"
           data-cmdk-input
           placeholder="Search pages, tools, courses…"
           autocomplete="off"
           spellcheck="false"
           aria-label="Search">
    <div class="cmdk__results" data-cmdk-results role="listbox"></div>
    <div class="cmdk__hint">
      <span>Navigate with <kbd>↑</kbd> <kbd>↓</kbd> · open with <kbd>Enter</kbd> · close with <kbd>Esc</kbd></span>
      <span><kbd>Cmd</kbd>/<kbd>Ctrl</kbd> + <kbd>K</kbd></span>
    </div>
  </div>
  <script type="application/json" data-cmdk-index><?= json_encode($cmdkIndex, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
</div>
