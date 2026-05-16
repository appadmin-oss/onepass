<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<?php partial('section-header', [
  'num'     => 'IPSUM',
  'eyebrow' => 'Afro-NG placeholder text',
  'title'   => 'Lorem ipsum, <em>but make it Lagos.</em>',
  'lead'    => 'Brand-shaped filler copy for design comps. Names, business types, cities, headlines and paragraphs sampled from a curated Nigerian and pan-African corpus. Skip the toga; keep the rhythm.',
]); ?>

<section data-reveal>
  <div class="ips" data-ips>
    <div class="ips__controls">
      <label class="field field--auth">
        <span class="field__label">Kind</span>
        <select data-ips-kind>
          <option value="paragraphs" selected>Paragraphs</option>
          <option value="headlines">Headlines</option>
          <option value="names">Person names</option>
          <option value="businesses">Business names</option>
          <option value="cities">Cities</option>
          <option value="testimonials">Testimonials</option>
        </select>
      </label>
      <label class="field field--auth">
        <span class="field__label">Count</span>
        <input type="number" data-ips-count value="3" min="1" max="20" inputmode="numeric">
      </label>
      <button type="button" class="btn btn-primary" data-ips-go>Generate</button>
      <button type="button" class="btn btn-ghost btn-sm" data-ips-copy>Copy</button>
    </div>
    <div class="ips__out" data-ips-out></div>
  </div>
</section>

<script>
(function () {
  'use strict';
  const CORPUS = {
    // First names — Yoruba, Igbo, Hausa, Fulani, Akan, Swahili, Amharic, Wolof
    firstNames: ['Tunde','Adaeze','Chika','Yetunde','Femi','Ifeanyi','Halima','Nneoma',
      'Kemi','Olu','Aminu','Fatima','Ngozi','Bola','Sade','Dele','Chinwe','Tobi',
      'Zainab','Musa','Hauwa','Ibrahim','Ekene','Ola','Amaka','Adamu','Bisi',
      'Akin','Lola','Toluwalope','Eseosa','Ifeoma','Esther','Daniel','Oluwadara',
      'Bukola','Chinedu','Olumide','Yaa','Kwame','Akua','Kofi','Abena','Nia',
      'Wangari','Aisha','Tariku','Selam','Yemi','Bamidele'],
    lastNames: ['Adebayo','Okafor','Adeyemi','Okeke','Lawal','Eze','Ibrahim','Ojo',
      'Salawu','Bello','Mohammed','Onyekachi','Adekunle','Achebe','Soyinka','Babalola',
      'Olumide','Nwosu','Adesanya','Adamu','Daramola','Adeyemo','Igbinedion','Anyanwu',
      'Kalu','Mensah','Asante','Akufo','Mwangi','Kamau','Otieno','Ssempala','Bekele',
      'Yibeltal','Abdullahi','Daudu','Edochie','Falade','Gbadamosi','Idowu','Jamiu'],
    bizSuffix: ['Studios','Labs','Group','Collective','Ventures','House','Works',
      'Holdings','Capital','Africa','Foundry','Lab','Co.','Industries','Press'],
    bizWord: ['Mwanga','Heritable','Atunda','Sankofa','Akoma','Baobab','Lagoon','Sahara',
      'Mzizi','Iwaju','Iroko','Kente','Aso','Ujamaa','Asili','Habari','Karibu',
      'Ubuntu','Indaba','Mbembe','Adire','Egbe','Olumo','Lekki','Yaba','Bole',
      'Eko','Maitama'],
    cities: ['Lagos','Abuja','Ibadan','Port Harcourt','Kano','Enugu','Accra','Nairobi',
      'Kigali','Dakar','Dar es Salaam','Addis Ababa','Cairo','Johannesburg','Cape Town',
      'Kampala','Marrakesh','Tunis','Casablanca','Kinshasa','Mombasa','Asaba'],
    nouns: ['the studio','the cohort','the founders','the strategy','the team',
      'the product','the brief','the launch','the campaign','the brand','the platform',
      'the work','the schedule','the workshop','the system','the rollout','the audit',
      'the artwork','the type','the layout','the editorial','the identity','the operators'],
    verbs: ['shipped','wrote','reframed','refused','built','iterated on','rolled out',
      'redesigned','re-pitched','documented','simplified','open-sourced','calibrated',
      'measured','presented','briefed','workshopped','re-scoped','un-shipped','renamed'],
    adjectives: ['quiet','deliberate','direct','calibrated','clean','operator-led',
      'African','intentional','specific','calm','warm','confident','tight','unsentimental'],
    timeRefs: ['within the first sprint','before the end of week one','by the cohort kickoff',
      'inside a three-week cycle','on the same day','at studio review','during the
 brief',
      'over the weekend','within budget','within the timeline','at full margin'],
    intros: ['No agency theatre.','We don\'t pitch — we ship.','Strategy in week one.',
      'Built by operators, not academics.','Tight briefs, real outcomes.',
      'Built on the continent, for the continent.','Honest scope. Calm delivery.',
      'Not a deck. A product.','Direct, warm, confident.','Made in Lagos.'],
    bodyStems: [
      'We worked across {city} and {city2} for six weeks before {verb} the first iteration; the team {verb2} {noun} a week ahead of schedule.',
      'Inside the {adj} brief sat a single question: how do you keep {noun} {adj2} when {city} budget cycles compress to thirty days?',
      'The founders agreed to {verb} {noun} on day one. {city} responded {timeRef}. We measured everything that mattered, and nothing that didn\'t.',
      'A {adj} team. A {adj2} schedule. One {noun} per cohort. The result {verb} like the brief promised.',
      'Pricing was sharp. The brief was sharper. We {verb} {noun} {timeRef}, then went back to first principles for the rest.',
      'Working with the studio meant editing as much as making. {verb_cap} {noun} that didn\'t earn its place was the {adj} part of the engagement.',
    ],
    testimonials: [
      'Working with Afrostrength was the first time a creative team treated our timeline as a fact, not a starting point.',
      'They shipped the rebrand in six weeks. They asked for nothing extra. They left the team better than they found it.',
      'Strategy in week one was not a slide — it was an actual document we used for the rest of the year.',
      'Operators, not architects of slide decks. We knew what was happening every week.',
      'The work landed in production looking exactly like the brief promised, which is rarer than it should be.',
    ],
  };

  function pick(a) { return a[Math.floor(Math.random() * a.length)]; }
  function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

  function gen(kind, count) {
    const n = Math.max(1, Math.min(20, parseInt(count, 10) || 1));
    const out = [];
    for (let i = 0; i < n; i++) {
      switch (kind) {
        case 'names':       out.push(pick(CORPUS.firstNames) + ' ' + pick(CORPUS.lastNames)); break;
        case 'businesses':  out.push(pick(CORPUS.bizWord) + ' ' + pick(CORPUS.bizSuffix)); break;
        case 'cities':      out.push(pick(CORPUS.cities)); break;
        case 'headlines':   out.push(pick(CORPUS.intros)); break;
        case 'testimonials':out.push('"' + pick(CORPUS.testimonials) + '" — ' + pick(CORPUS.firstNames) + ' ' + pick(CORPUS.lastNames) + ', ' + pick(CORPUS.cities)); break;
        case 'paragraphs':
        default:
          out.push(renderParagraph()); break;
      }
    }
    return out;
  }

  function renderParagraph() {
    const stems = CORPUS.bodyStems.slice();
    for (let i = stems.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [stems[i], stems[j]] = [stems[j], stems[i]];
    }
    const piece = (s) => s
      .replace(/\{city2?\}/g,  () => pick(CORPUS.cities))
      .replace(/\{verb2?\}/g,  () => pick(CORPUS.verbs))
      .replace(/\{verb_cap\}/g,() => cap(pick(CORPUS.verbs)))
      .replace(/\{noun\}/g,    () => pick(CORPUS.nouns))
      .replace(/\{adj2?\}/g,   () => pick(CORPUS.adjectives))
      .replace(/\{timeRef\}/g, () => pick(CORPUS.timeRefs));
    // Two-sentence paragraph.
    return piece(stems[0]) + ' ' + piece(stems[1]);
  }

  function render() {
    const kind  = document.querySelector('[data-ips-kind]').value;
    const count = document.querySelector('[data-ips-count]').value;
    const out = document.querySelector('[data-ips-out]');
    const items = gen(kind, count);
    if (kind === 'paragraphs' || kind === 'testimonials') {
      out.innerHTML = items.map(i => '<p>' + i + '</p>').join('');
    } else {
      out.innerHTML = '<ul>' + items.map(i => '<li>' + i + '</li>').join('') + '</ul>';
    }
  }
  document.querySelector('[data-ips-go]').addEventListener('click', render);
  document.querySelector('[data-ips-copy]').addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(document.querySelector('[data-ips-out]').innerText);
    } catch (_) {}
  });
  render();
})();
</script>
