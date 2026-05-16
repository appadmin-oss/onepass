<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<?php partial('section-header', [
  'num'     => 'LD+JSON',
  'eyebrow' => 'Schema.org JSON-LD builder',
  'title'   => 'Structured data, <em>without</em> the typos.',
  'lead'    => 'Pick a schema type, fill the fields you actually need, copy the &lt;script&gt; tag. Built around the five types most marketing sites need — Article, Organization, Product, Event, BreadcrumbList. JSON validates as you type.',
]); ?>

<section data-reveal>
  <div class="ld" data-ld>
    <aside class="ld__types" role="tablist" aria-label="Schema type">
      <button role="tab" type="button" data-ld-type="Article"     class="is-active">Article</button>
      <button role="tab" type="button" data-ld-type="Organization">Organization</button>
      <button role="tab" type="button" data-ld-type="Product">Product</button>
      <button role="tab" type="button" data-ld-type="Event">Event</button>
      <button role="tab" type="button" data-ld-type="BreadcrumbList">Breadcrumbs</button>
    </aside>

    <form class="ld__form" data-ld-form data-smart>
      <!-- Form fields injected per type -->
    </form>

    <div class="ld__out">
      <div class="ld__out-head">
        <span class="ff-mono">// &lt;script type="application/ld+json"&gt;</span>
        <div class="ld__out-actions">
          <span class="ld__valid" data-ld-valid>Valid</span>
          <button type="button" class="auth-form__link" data-ld-copy>Copy</button>
        </div>
      </div>
<pre><code data-ld-output class="ff-mono"></code></pre>
    </div>
  </div>
</section>

<script>
(function () {
  'use strict';
  const root = document.querySelector('[data-ld]');
  const form = root.querySelector('[data-ld-form]');
  const out  = root.querySelector('[data-ld-output]');
  const valid = root.querySelector('[data-ld-valid]');
  const tabs = root.querySelectorAll('[data-ld-type]');

  // Field definitions per schema type. Each field describes:
  //   name, label, kind (text|textarea|url|date|number|list), required?, hint?
  const SCHEMAS = {
    Article: {
      label: 'Article — for blog posts, news, field notes',
      fields: [
        { n: 'headline',     l: 'Headline',         k: 'text',     r: true },
        { n: 'description',  l: 'Description',      k: 'textarea' },
        { n: 'image',        l: 'Image URL',        k: 'url' },
        { n: 'url',          l: 'Canonical URL',    k: 'url' },
        { n: 'datePublished',l: 'Date published',   k: 'date',     r: true },
        { n: 'dateModified', l: 'Date modified',    k: 'date' },
        { n: 'authorName',   l: 'Author name',      k: 'text' },
        { n: 'authorUrl',    l: 'Author URL',       k: 'url' },
        { n: 'publisher',    l: 'Publisher',        k: 'text',     hint: 'e.g. Afrostrength Limited' },
      ],
      build: (v) => ({
        '@context': 'https://schema.org', '@type': 'Article',
        headline: v.headline, description: v.description || undefined,
        image: v.image || undefined, mainEntityOfPage: v.url || undefined,
        datePublished: v.datePublished || undefined, dateModified: v.dateModified || undefined,
        author: v.authorName ? { '@type': 'Person', name: v.authorName, url: v.authorUrl || undefined } : undefined,
        publisher: v.publisher ? { '@type': 'Organization', name: v.publisher } : undefined,
      }),
    },
    Organization: {
      label: 'Organization — for the brand entity',
      fields: [
        { n: 'name',  l: 'Legal name',  k: 'text', r: true },
        { n: 'url',   l: 'Website',     k: 'url',  r: true },
        { n: 'logo',  l: 'Logo URL',    k: 'url' },
        { n: 'sameAs',l: 'Profile URLs (one per line)', k: 'textarea',
          hint: 'LinkedIn, X, Instagram, etc.' },
        { n: 'email', l: 'Contact email', k: 'text' },
        { n: 'phone', l: 'Phone',       k: 'text' },
      ],
      build: (v) => ({
        '@context': 'https://schema.org', '@type': 'Organization',
        name: v.name, url: v.url,
        logo: v.logo || undefined,
        sameAs: v.sameAs ? v.sameAs.split(/\n+/).map(s => s.trim()).filter(Boolean) : undefined,
        contactPoint: (v.email || v.phone) ? {
          '@type': 'ContactPoint',
          email: v.email || undefined,
          telephone: v.phone || undefined,
          contactType: 'customer service',
        } : undefined,
      }),
    },
    Product: {
      label: 'Product — for a service offering or physical good',
      fields: [
        { n: 'name',        l: 'Name',         k: 'text', r: true },
        { n: 'description', l: 'Description',  k: 'textarea' },
        { n: 'image',       l: 'Image URL',    k: 'url' },
        { n: 'brand',       l: 'Brand name',   k: 'text' },
        { n: 'sku',         l: 'SKU',          k: 'text' },
        { n: 'priceCurrency',l:'Currency',     k: 'text', hint: 'ISO 4217, e.g. NGN' },
        { n: 'price',       l: 'Price',        k: 'number' },
        { n: 'availability',l: 'Availability', k: 'text', hint: 'InStock / OutOfStock / PreOrder' },
      ],
      build: (v) => ({
        '@context': 'https://schema.org', '@type': 'Product',
        name: v.name, description: v.description || undefined,
        image: v.image || undefined, sku: v.sku || undefined,
        brand: v.brand ? { '@type': 'Brand', name: v.brand } : undefined,
        offers: (v.price || v.priceCurrency) ? {
          '@type': 'Offer',
          priceCurrency: v.priceCurrency || undefined,
          price: v.price || undefined,
          availability: v.availability ? 'https://schema.org/' + v.availability : undefined,
        } : undefined,
      }),
    },
    Event: {
      label: 'Event — for cohort openings, workshops, launches',
      fields: [
        { n: 'name',        l: 'Name',         k: 'text', r: true },
        { n: 'startDate',   l: 'Start (ISO)',  k: 'text', r: true, hint: '2026-06-15T18:00:00+01:00' },
        { n: 'endDate',     l: 'End (ISO)',    k: 'text' },
        { n: 'description', l: 'Description',  k: 'textarea' },
        { n: 'locationName',l: 'Location name',k: 'text' },
        { n: 'locationAddr',l: 'Location address',k: 'text' },
        { n: 'image',       l: 'Image URL',    k: 'url' },
        { n: 'organizer',   l: 'Organiser',    k: 'text' },
        { n: 'mode',        l: 'Mode',         k: 'text', hint: 'Online / Offline / Mixed' },
      ],
      build: (v) => ({
        '@context': 'https://schema.org', '@type': 'Event',
        name: v.name, startDate: v.startDate, endDate: v.endDate || undefined,
        description: v.description || undefined, image: v.image || undefined,
        eventAttendanceMode: ({ Online: 'OnlineEventAttendanceMode',
          Offline: 'OfflineEventAttendanceMode', Mixed: 'MixedEventAttendanceMode'
        })[v.mode] ? 'https://schema.org/' + ({Online:'OnlineEventAttendanceMode',
          Offline:'OfflineEventAttendanceMode', Mixed:'MixedEventAttendanceMode'})[v.mode] : undefined,
        location: (v.locationName || v.locationAddr) ? {
          '@type': 'Place', name: v.locationName || undefined, address: v.locationAddr || undefined,
        } : undefined,
        organizer: v.organizer ? { '@type': 'Organization', name: v.organizer } : undefined,
      }),
    },
    BreadcrumbList: {
      label: 'BreadcrumbList — for the trail of pages a viewer is on',
      fields: [
        { n: 'crumbs', l: 'Crumbs (one per line: name|url)', k: 'textarea', r: true,
          hint: 'Home|https://example.com\nAcademy|https://example.com/academy' },
      ],
      build: (v) => ({
        '@context': 'https://schema.org', '@type': 'BreadcrumbList',
        itemListElement: (v.crumbs || '').split(/\n+/).map((line, i) => {
          const [name, url] = line.split('|').map(s => (s || '').trim());
          if (!name) return null;
          return { '@type': 'ListItem', position: i + 1, name: name, item: url || undefined };
        }).filter(Boolean),
      }),
    },
  };

  function pruneEmpty(o) {
    if (Array.isArray(o)) return o.map(pruneEmpty).filter(x => x !== undefined && x !== '');
    if (o && typeof o === 'object') {
      const r = {};
      for (const k of Object.keys(o)) {
        const v = pruneEmpty(o[k]);
        if (v !== undefined && v !== '' && !(Array.isArray(v) && v.length === 0)) r[k] = v;
      }
      return r;
    }
    return o;
  }

  function renderForm(type) {
    const def = SCHEMAS[type];
    const html = (
      '<p class="ld__hint">' + def.label + '</p>' +
      def.fields.map(f => {
        const id = 'ld-' + f.n;
        const ctrl =
          f.k === 'textarea'
            ? '<textarea id="' + id + '" name="' + f.n + '" rows="3"' + (f.hint ? ' placeholder="' + f.hint + '"' : '') + (f.r ? ' required' : '') + '></textarea>'
            : '<input id="' + id + '" name="' + f.n + '" type="' + ({text:'text',url:'url',date:'date',number:'number'})[f.k] + '"'
              + (f.k === 'url' ? ' data-auto-https' : '')
              + (f.hint ? ' placeholder="' + f.hint + '"' : '')
              + (f.r ? ' required' : '') + '>';
        return '<label class="field field--auth" for="' + id + '"><span class="field__label">' + f.l + '</span>' + ctrl + '</label>';
      }).join('')
    );
    form.innerHTML = html;
    if (window.AfsSmartForm && AfsSmartForm.boot) AfsSmartForm.boot();
    update();
  }

  function readForm() {
    const o = {};
    [...form.querySelectorAll('input,textarea,select')].forEach(el => {
      if (!el.name) return;
      o[el.name] = el.value.trim();
    });
    return o;
  }

  function activeType() {
    return root.querySelector('[data-ld-type].is-active')?.dataset.ldType || 'Article';
  }

  function update() {
    const type = activeType();
    const built = pruneEmpty(SCHEMAS[type].build(readForm()));
    let json;
    try { json = JSON.stringify(built, null, 2); }
    catch (e) { valid.dataset.state = 'bad'; valid.textContent = 'Invalid'; return; }
    out.textContent = '<script type="application/ld+json">\n' + json + '\n<\/script>';
    valid.dataset.state = 'ok'; valid.textContent = 'Valid';
  }

  tabs.forEach(t => t.addEventListener('click', () => {
    tabs.forEach(x => x.classList.toggle('is-active', x === t));
    renderForm(t.dataset.ldType);
  }));
  form.addEventListener('input', update);

  root.querySelector('[data-ld-copy]')?.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(out.textContent);
      const btn = root.querySelector('[data-ld-copy]');
      btn.textContent = 'Copied ✓';
      setTimeout(() => { btn.textContent = 'Copy'; }, 1400);
    } catch (_) {}
  });

  renderForm('Article');
})();
</script>
