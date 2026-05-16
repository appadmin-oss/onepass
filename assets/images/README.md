# Images — author guide

Three image surfaces live under `/assets/images/`. This guide tells you
**what shape each one needs** so the layout doesn't drift when you drop
new art in.

```
assets/images/
├── cutouts/          ← Transparent-background subjects (people, objects, products)
├── photos/           ← Full-bleed real photos (location, behind-the-scenes, blog leads)
└── og/               ← 1200×630 cards for Open Graph / Twitter (optional)
```

---

## Cut-outs (`/assets/images/cutouts/*.webp`)

A cut-out is a **subject with a transparent background** — a founder
shot, a product on no surface, a fragment of architecture. The
`cutout` partial pairs it with one of the four Afrocentric pattern
frames (Sahel-contour, terracotta-grid, diagonal-weave, arched-light)
and centres it inside the frame.

### Specs

| Property | Value |
|---|---|
| Format | `.webp` preferred, `.png` accepted |
| Colour space | sRGB |
| Background | **Fully transparent** alpha channel (do not knock to white) |
| Resolution | At least 2× the rendered size. For a 480 px frame, ship ≥ 960 px |
| Trim | Crop tight to the subject. The partial adds its own 4% padding |
| File size | ≤ 250 KB after WebP compression |
| Naming | kebab-case, descriptive — e.g. `founder-tunde.webp`, `lantern-glow.webp` |

### How to use it

```php
partial('cutout', [
  'src'     => '/assets/images/cutouts/founder-tunde.webp',
  'alt'     => 'Tunde, founder, full-length cut-out portrait',
  'pattern' => 'sahel-contour',  // sahel-contour | terracotta-grid | diagonal-weave | arched-light
  'aspect'  => 'portrait',       // portrait | landscape | square
  'tone'    => 'bone',           // bone | peach | ink | crimson
  'caption' => 'Founder · Lagos',
]);
```

If the file doesn't exist yet, the frame renders with the pattern + a
small placeholder glyph — **no broken-image icon, no layout reflow**.
Ship the layout first, drop the art in later.

### Pattern × tone combinations that work

| Tone     | Suggested patterns          | Avoid |
|----------|-----------------------------|-------|
| bone     | sahel-contour, terracotta-grid | — |
| peach    | arched-light, diagonal-weave  | terracotta-grid (too busy) |
| ink      | sahel-contour, arched-light   | terracotta-grid |
| crimson  | diagonal-weave (sparingly)    | everything else (too loud) |

---

## Photos (`/assets/images/photos/*.webp`)

A photo is **full-bleed real imagery** — not a cut-out. Use the
`image-pattern` partial which slips a pattern shoulder along one
edge so the photo still feels designed instead of stock.

### Specs

| Property | Value |
|---|---|
| Format | `.webp` (or `.jpg` for photographic content with no transparency need) |
| Resolution | 2400 × 1800 source, served as 1600w |
| Aspect | Free, but match the partial's `aspect` prop |
| File size | ≤ 350 KB |
| Naming | `<subject>-<context>.webp` e.g. `egbeda-studio-dusk.webp` |

### How to use it

```php
partial('image-pattern', [
  'src'     => '/assets/images/photos/egbeda-studio-dusk.webp',
  'alt'     => 'CACENTRE Egbeda studio at dusk',
  'pattern' => 'arched-light',
  'overlay' => 'right',             // right | left | top | bottom | none
  'aspect'  => '4/3',
  'caption' => 'CACENTRE · Egbeda',
]);
```

---

## Open Graph cards (`/assets/images/og/*.jpg`)

Reserve for hand-authored social-card images per major page. Use
`/tools/og` to preview before you ship.

| Property | Value |
|---|---|
| Format | `.jpg` (better social-platform compat than WebP) |
| Size | **1200 × 630 exactly** |
| File size | ≤ 200 KB |
| Naming | `<page-slug>.jpg` e.g. `home.jpg`, `academy.jpg` |

Set on a per-page basis via the `og_image` meta variable in the page
controller.

---

## What the site does on its own

You **don't** need to ship art for these:

- **Student avatars** — generated on the fly by DiceBear via CDN, seeded
  by student id, palette-pinned to our four brand inks.
- **Project gallery** — operator uploads via `/admin/projects`; the
  detail page wraps every gallery image in PhotoSwipe (zoom, pan,
  caption, keyboard nav) automatically.
- **Empty states** — abstract SVG illustrations under
  `/assets/svg/illustrations/*` cover empty lists and 404s.

---

## Where the pattern files live

Each pattern is a single SVG tile that uses `currentColor` so the
host element decides its tint:

```
assets/svg/patterns/
├── sahel-contour.svg    ← Topographic contour lines (calm, asymmetric)
├── terracotta-grid.svg  ← Irregular brick courses (Djenne abstraction)
├── diagonal-weave.svg   ← Cross-hatch strip weave (no kente cliché)
└── arched-light.svg     ← Arcades + sun glyph
```

If you commission a new pattern, drop it here, give it a slug, and add
it to the `validPatterns` array in `src/views/partials/cutout.php` and
`image-pattern.php`. That's the entire integration.
