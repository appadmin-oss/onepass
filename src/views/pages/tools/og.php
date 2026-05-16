<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<?php partial('section-header', [
  'num'     => 'OG',
  'eyebrow' => 'Open Graph card preview',
  'title'   => 'How will it look <em>before</em> you ship?',
  'lead'    => 'Paste your title, description, image and URL. The right column previews the exact card Twitter/X, LinkedIn, Facebook, Slack and Discord render from your tags. Catch truncation, missing images, wrong domain — all without publishing a thing.',
]); ?>

<section data-reveal>
  <div class="og">
    <form class="og__form" data-og-form>
      <label class="field field--auth">
        <span class="field__label">Page URL</span>
        <input type="url" data-og-url placeholder="https://afrostrength.com/projects/mwanga"
               value="https://afrostrength.com" data-auto-https autocomplete="off" spellcheck="false">
      </label>

      <label class="field field--auth">
        <span class="field__label">Title (og:title)
          <span class="og__count" data-og-count-title>0 / 70</span>
        </span>
        <input type="text" data-og-title maxlength="200"
               placeholder="Mwanga — Luminaires for Lagos interiors"
               value="Mwanga — Luminaires for Lagos interiors">
      </label>

      <label class="field field--auth">
        <span class="field__label">Description (og:description)
          <span class="og__count" data-og-count-desc>0 / 200</span>
        </span>
        <textarea data-og-desc rows="3" maxlength="400"
                  placeholder="A complete identity system for a Lagos lighting studio — wordmark, packaging, retail."
        >A complete identity system for a Lagos lighting studio — wordmark, packaging, retail.</textarea>
      </label>

      <label class="field field--auth">
        <span class="field__label">Image URL (og:image · 1200 × 630 recommended)</span>
        <input type="url" data-og-image placeholder="https://afrostrength.com/assets/images/mwanga-og.jpg"
               value="https://images.unsplash.com/photo-1505691938895-1758d7feb511?w=1200&q=80&auto=format&fit=crop"
               data-auto-https autocomplete="off" spellcheck="false">
      </label>

      <label class="field field--auth">
        <span class="field__label">Site name</span>
        <input type="text" data-og-site value="Afrostrength" maxlength="60">
      </label>

      <fieldset class="og__platforms" aria-label="Which platforms to preview">
        <legend class="sr-only">Platforms</legend>
        <label><input type="checkbox" data-og-plat value="twitter" checked> Twitter / X</label>
        <label><input type="checkbox" data-og-plat value="linkedin" checked> LinkedIn</label>
        <label><input type="checkbox" data-og-plat value="facebook" checked> Facebook</label>
        <label><input type="checkbox" data-og-plat value="slack" checked> Slack</label>
        <label><input type="checkbox" data-og-plat value="discord"> Discord</label>
      </fieldset>

      <details class="og__snippet">
        <summary>Copy the &lt;meta&gt; tags</summary>
<pre><code data-og-snippet class="ff-mono"></code></pre>
        <button type="button" class="auth-form__link" data-og-copy>Copy to clipboard</button>
      </details>
    </form>

    <div class="og__previews" data-og-previews>
      <!-- previews rendered client-side -->
    </div>
  </div>
</section>

<script>
(function () {
  'use strict';
  const $ = (s) => document.querySelector(s);
  const els = {
    url:    $('[data-og-url]'),
    title:  $('[data-og-title]'),
    desc:   $('[data-og-desc]'),
    image:  $('[data-og-image]'),
    site:   $('[data-og-site]'),
    ctTitle:$('[data-og-count-title]'),
    ctDesc: $('[data-og-count-desc]'),
    previews:$('[data-og-previews]'),
    snippet:$('[data-og-snippet]'),
    copy:   $('[data-og-copy]'),
    plats:  document.querySelectorAll('[data-og-plat]'),
  };

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;',
    }[c]));
  }
  function host(u) {
    try { return new URL(u).host.replace(/^www\./, ''); }
    catch (_) { return ''; }
  }
  function truncate(s, n) {
    s = String(s || '');
    if (s.length <= n) return s;
    return s.slice(0, n - 1).trimEnd() + '…';
  }

  // Visual budgets per platform (approximate, public knowledge):
  //   Twitter/X "summary_large_image": title ~70, desc ~125, host shown.
  //   LinkedIn:                          title ~110, desc ~200.
  //   Facebook large:                    title ~90,  desc ~110 above the fold.
  //   Slack unfurl:                      title ~80,  desc 4 lines (~250).
  //   Discord embed:                     title ~85,  desc ~325.
  function previewTwitter(d) {
    return tile('twitter', 'Twitter / X', d, {
      title: truncate(d.title, 70),
      desc:  truncate(d.desc, 125),
      hostLine: d.host,
      ratio: '1.91 / 1',
    });
  }
  function previewLinkedIn(d) {
    return tile('linkedin', 'LinkedIn', d, {
      title: truncate(d.title, 110),
      desc:  truncate(d.desc, 200),
      hostLine: d.host,
      ratio: '1.91 / 1',
    });
  }
  function previewFacebook(d) {
    return tile('facebook', 'Facebook', d, {
      title: truncate(d.title, 90),
      desc:  truncate(d.desc, 110),
      hostLine: d.host.toUpperCase(),
      ratio: '1.91 / 1',
    });
  }
  function previewSlack(d) {
    return tile('slack', 'Slack', d, {
      title: truncate(d.title, 80),
      desc:  truncate(d.desc, 250),
      hostLine: d.site || d.host,
      ratio: '2 / 1',
      style: 'slack',
    });
  }
  function previewDiscord(d) {
    return tile('discord', 'Discord', d, {
      title: truncate(d.title, 85),
      desc:  truncate(d.desc, 325),
      hostLine: d.site || d.host,
      ratio: '2 / 1',
      style: 'discord',
    });
  }
  function tile(kind, label, d, c) {
    return (
      '<article class="og-card og-card--' + kind + '" aria-label="' + label + ' preview">' +
        '<header class="og-card__bar"><span>' + escapeHtml(label) + '</span></header>' +
        (d.image ? '<div class="og-card__img" style="aspect-ratio:' + c.ratio + ';background-image:url(' + JSON.stringify(d.image) + ');"></div>' : '<div class="og-card__img og-card__img--blank" style="aspect-ratio:' + c.ratio + '"><span>Missing og:image</span></div>') +
        '<div class="og-card__body">' +
          (c.hostLine ? '<div class="og-card__host">' + escapeHtml(c.hostLine) + '</div>' : '') +
          '<div class="og-card__title">' + escapeHtml(c.title || '(no title)') + '</div>' +
          (c.desc ? '<div class="og-card__desc">' + escapeHtml(c.desc) + '</div>' : '') +
        '</div>' +
      '</article>'
    );
  }

  function snippet(d) {
    const lines = [
      '<meta property="og:title" content="' + escapeHtml(d.title) + '">',
      '<meta property="og:description" content="' + escapeHtml(d.desc) + '">',
      '<meta property="og:image" content="' + escapeHtml(d.image) + '">',
      '<meta property="og:url" content="' + escapeHtml(d.url) + '">',
      '<meta property="og:site_name" content="' + escapeHtml(d.site) + '">',
      '<meta property="og:type" content="website">',
      '<meta name="twitter:card" content="summary_large_image">',
      '<meta name="twitter:title" content="' + escapeHtml(d.title) + '">',
      '<meta name="twitter:description" content="' + escapeHtml(d.desc) + '">',
      '<meta name="twitter:image" content="' + escapeHtml(d.image) + '">',
    ];
    return lines.join('\n');
  }

  function update() {
    const d = {
      url:   els.url.value.trim(),
      title: els.title.value.trim(),
      desc:  els.desc.value.trim(),
      image: els.image.value.trim(),
      site:  els.site.value.trim() || 'Afrostrength',
    };
    d.host = host(d.url) || 'example.com';
    if (els.ctTitle) {
      els.ctTitle.textContent = d.title.length + ' / 70';
      els.ctTitle.dataset.state = d.title.length > 70 ? 'warn' : 'ok';
    }
    if (els.ctDesc) {
      els.ctDesc.textContent  = d.desc.length + ' / 200';
      els.ctDesc.dataset.state = d.desc.length > 200 ? 'warn' : 'ok';
    }
    const active = new Set(
      [...els.plats].filter(p => p.checked).map(p => p.value)
    );
    const builders = {
      twitter: previewTwitter, linkedin: previewLinkedIn, facebook: previewFacebook,
      slack: previewSlack, discord: previewDiscord,
    };
    els.previews.innerHTML =
      ['twitter','linkedin','facebook','slack','discord']
        .filter(k => active.has(k))
        .map(k => builders[k](d))
        .join('') || '<p class="caption">Pick at least one platform.</p>';
    els.snippet.textContent = snippet(d);
  }

  document.querySelector('[data-og-form]').addEventListener('input', update);
  els.plats.forEach(p => p.addEventListener('change', update));

  els.copy?.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(els.snippet.textContent);
      els.copy.textContent = 'Copied ✓';
      setTimeout(() => { els.copy.textContent = 'Copy to clipboard'; }, 1400);
    } catch (_) {
      els.copy.textContent = 'Copy failed — select manually';
    }
  });

  update();
})();
</script>
