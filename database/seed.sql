-- Afrostrength — seed.sql
-- Populates the visible site with brand-voice copy lifted from the
-- design system. Re-runnable via REPLACE.

-- ------- Services -----------------------------------------------------
REPLACE INTO services (name, slug, tagline, overview, who_for, process_json, deliverables_json, icon, timeline, tag, sort) VALUES
('Brand Development', 'brand-development',
 'Strategy, identity systems, naming, and verbal voice — built to scale across every surface.',
 'We build brands that hold up under pressure. Strategy first, identity second, system always. From positioning to naming, voice, and a complete identity system, every piece is designed to ship and outlast a trend cycle.',
 'For founders launching their first brand, MDs leading a rebrand, and teams refreshing for a new chapter.',
 '[{"title":"Strategy intensive","desc":"We compress the discovery into a tight, senior-led week. Positioning, audience map, voice principles."},{"title":"Identity exploration","desc":"Three considered routes. Not 30. Each one defensible from boardroom to billboard."},{"title":"System build","desc":"Logos, type, colour, motion, voice — wired together as a working system, not a deck."},{"title":"Rollout & handover","desc":"Shipped guidelines, working files, and a 30-minute team walkthrough."}]',
 '["Positioning & messaging matrix","Logo system & brand mark","Type & colour systems","Voice & tone principles","Brand guidelines (PDF + Figma)","Working files (AI/SVG/PNG)"]',
 'brand', '6 WEEKS', 'CORE', 1),

('Creative Design', 'creative-design',
 'Editorial, packaging, environmental, and digital — design that ships and outlasts the trend cycle.',
 'Design that earns its keep. We bring senior craft to editorial systems, packaging, environmental graphics, and digital surfaces — always built to ship, never just to win awards.',
 'For brands that need craft-led design execution at speed, without losing strategic intent.',
 '[{"title":"Brief & references","desc":"A working session that locks scope and removes ambiguity before pencils touch paper."},{"title":"Concept routes","desc":"Two or three considered directions, each tied back to the brief."},{"title":"Production","desc":"Final design at production fidelity — print-ready, screen-ready, env-ready."},{"title":"Handover","desc":"All assets, source files, and a short rationale doc for the team."}]',
 '["Editorial layouts","Packaging system","Environmental graphics","Digital design","Production-ready files"]',
 'editorial', '4 WEEKS', 'CORE', 2),

('Media Solutions', 'media-solutions',
 'Photography, film, podcast, and editorial — produced in-house with senior crews.',
 'In-house production. Real crews, real kit, real production discipline. Photography, film, podcast, and editorial — shot, cut, and delivered against tight production windows.',
 'For brands that need owned, on-brand media made at production-house quality without the agency overhead.',
 '[{"title":"Pre-production","desc":"Shot lists, treatment, locations, talent. Sign-off before the camera moves."},{"title":"Production","desc":"Senior crews. One day on set is one day delivered."},{"title":"Post","desc":"Edit, grade, sound, master. All in-house, all tracked daily."},{"title":"Delivery","desc":"Final masters plus the cuts your channels actually need."}]',
 '["Photography (lifestyle / product / editorial)","Film (brand / product / event recap)","Podcast production","Editorial features","Master + channel cuts"]',
 'media', '3 WEEKS', 'CORE', 3),

('Project & Event Management', 'project-event-management',
 'Strategy, build, and on-the-ground delivery — for the moments that have to land.',
 'When the date is fixed and the room is full, you need a partner who runs production like an operator, not an agency. We deliver brand experiences, summits, and product launches with the same rigour we bring to a build.',
 'For founders, marketing leads, and innovation teams running events that absolutely have to land.',
 '[{"title":"Brief & vision","desc":"Define what success looks like before we touch a venue."},{"title":"Build & rehearse","desc":"Stages, signage, decks, talent — all rehearsed before doors open."},{"title":"Execute","desc":"Senior production runs the room. You run the relationships."},{"title":"Recap","desc":"Editorial recap, recap film, and a stakeholder report inside seven days."}]',
 '["Event strategy & creative","Production & build","Stage management","Talent & vendor coordination","Recap film + editorial"]',
 'spark', '4–8 WEEKS', 'CORE', 4),

('Digital Solutions', 'digital-solutions',
 'Sites, products, and digital surfaces — built to perform on real-world hosting.',
 'We build the digital surfaces that carry your brand into the world: marketing sites, internal tools, product MVPs. Lean stack, production-ready code, no framework theatre.',
 'For founders shipping a first product, brands moving off SaaS templates, and teams needing a senior hand on the codebase.',
 '[{"title":"Architecture","desc":"Decisions about stack, hosting, and data — made in week one."},{"title":"Design system","desc":"Components built once, applied everywhere."},{"title":"Build","desc":"Senior engineers, working software shipped weekly."},{"title":"Launch","desc":"Domain, analytics, monitoring, handover."}]',
 '["Marketing site or product MVP","Design system","CMS or admin tooling","Analytics + monitoring setup","Handover docs"]',
 'stack', '6–10 WEEKS', 'CORE', 5),

('Client Training', 'client-training',
 'Hands-on workshops and embedded training to lift the team that runs the brand after we leave.',
 'Strategy and identity only stick when the in-house team can wield them. We run focused training programmes — 1-day intensives or 4-week embedded engagements — that turn brand systems into working muscle.',
 'For marketing leads, comms teams, and in-house creatives ready to take the brand forward.',
 '[{"title":"Audit","desc":"What does the team already know? Where are the real gaps?"},{"title":"Programme design","desc":"A curriculum tailored to the brand and the team."},{"title":"Workshops","desc":"Hands-on sessions, not slides. Outputs the team keeps."},{"title":"Coaching","desc":"Optional 30/60/90-day check-ins to keep the muscle warm."}]',
 '["Skills audit","Custom curriculum","Workshop sessions (live or remote)","Working playbooks","Optional follow-up coaching"]',
 'teams', '1–4 WEEKS', 'TRAINING', 6);

-- ------- Projects -----------------------------------------------------
REPLACE INTO projects (title, slug, client, year, category, tags, summary, problem, solution, outcome, gallery_json, is_featured, status_label, status) VALUES
('Mwanga — luminaire identity', 'mwanga-luminaire-identity', 'Mwanga Studio', 2026,
 'Brand · Identity · Packaging', 'Brand · Identity · Packaging',
 'A complete identity system for a Lagos-based luminaire studio expanding into pan-African retail.',
 'Mwanga had grown from a workshop to a regional studio without an identity system that scaled. Every shopfront looked different. Every package told a different story.',
 'We rebuilt the brand from positioning down — a verbal system rooted in light as legacy, a logo mark drawn from West African brass-work, and a full retail and packaging system that locks every surface to one voice.',
 'Twelve retail locations now operating on a single identity. Wholesale conversion up 32% in the first quarter post-launch. Packaging recognised by AGI 2026.',
 '["/assets/images/mwanga-1.webp","/assets/images/mwanga-2.webp","/assets/images/mwanga-3.webp"]',
 1, 'live', 'published'),

('Lagos Futures Summit', 'lagos-futures-summit', 'FCMB Innovation', 2025,
 'Event · Production · Editorial', 'Event · Production · Editorial',
 'Two thousand attendees, one weekend, one editorial system threaded through every surface.',
 'FCMB Innovation needed a fintech summit that read as serious as its programme — but every existing event template felt either too corporate or too festival.',
 'We designed an editorial-first event system: stage design, signage, programme book, recap film. Every surface carried the same voice, the same type, the same restraint.',
 'Two thousand attendees. Zero compromises on production. Recap film viewed 480k times in the first month. The event is back in 2026 with a 30% larger footprint.',
 '["/assets/images/lagos-1.webp","/assets/images/lagos-2.webp"]',
 1, 'case', 'published'),

('Heritable — wealth platform', 'heritable-wealth-platform', 'Heritable Africa', 2026,
 'Digital · Product · Brand', 'Digital · Product · Brand',
 'From pitch deck to shipped wealth platform in eleven weeks.',
 'Heritable had Series-A momentum but was pitching against legacy banks with a Figma file. They needed brand and product, shipping in parallel.',
 'A two-track build: brand strategy and identity in week one, product design and engineering from week two. We compressed the discovery into a strategy intensive and ran design and engineering in lockstep.',
 'Live platform in eleven weeks. First 1,000 users onboarded inside the launch window. Brand cited in Rest of World as a category benchmark.',
 '["/assets/images/heritable-1.webp"]',
 1, 'production', 'published');

-- ------- Testimonials -----------------------------------------------
REPLACE INTO testimonials (client_name, role, company, quote, rating, is_featured) VALUES
('I. Bello',  'Founder',           'Heritable Africa',
 'Afrostrength took us from a pitch deck to a shipped wealth platform in eleven weeks. {{accent}}No agency theatre.{{/accent}}',
 5, 1),
('A. Nwosu',  'MD',                'Mwanga Studio',
 'We came for the identity. We stayed for the system — every shop floor, every package, every screen now {{accent}}speaks the same language.{{/accent}}',
 5, 1),
('O. Adeoye', 'Lead, Innovation',  'FCMB Innovation',
 'Lagos Futures Summit ran on their production. Two thousand attendees, one weekend, {{accent}}zero compromises.{{/accent}}',
 5, 1);

-- ------- Posts (Field notes) ----------------------------------------
REPLACE INTO posts (title, slug, excerpt, body, category, tags, author, status, published_at) VALUES
('Field notes 04 — what we learned shipping eight rebrands in 2025',
 'field-notes-04-eight-rebrands-2025',
 'Eight rebrands. Forty-two production weeks. A handful of things we thought we knew that turned out to be wrong.',
 '<p>Every rebrand we shipped in 2025 followed the same opening week: a strategy intensive, three identity routes, and a build window. By the eighth, we had tightened the playbook in eight specific ways. This essay is each of them, in order.</p><p>The biggest learning: your strategy intensive is too long. Cut it in half.</p>',
 'Editorial', 'rebrand · process', 'Adaeze Okeke', 'published', '2026-04-22 10:00:00'),

('How we costed the Lagos Futures Summit',
 'how-we-costed-lagos-futures-summit',
 'Two thousand attendees, one weekend, one P&L. The numbers, the line items, and what we''d do again.',
 '<p>Most event recaps publish a film. We''re publishing the spreadsheet. Production budget, vendor mix, actual vs forecast, and the three line items we got wrong.</p>',
 'Operations', 'events · finance', 'Kemi Balogun', 'published', '2026-03-08 09:30:00'),

('A short defence of editorial systems',
 'short-defence-of-editorial-systems',
 'When every brand feels indistinguishable, an editorial system is the cheapest moat you can build.',
 '<p>An editorial system is a small set of rules — about voice, type, hierarchy, and rhythm — that makes a brand legible across every surface. Most brands skip them. Most brands look the same. Here is the version we use.</p>',
 'Editorial', 'systems · voice', 'Tope Adeyemi', 'published', '2026-02-14 12:00:00');

-- ------- Content blocks (homepage hero etc.) -----------------------
REPLACE INTO content_blocks (key_name, value_text) VALUES
('hero.eyebrow',   '// AFROSTRENGTH STUDIO — LAGOS · NAIROBI · ACCRA'),
('hero.title',     'Building Brands, {{accent}}Strengthening Legacies.{{/accent}}'),
('hero.sub',       'Strategy, identity, media, and project execution for the brands shaping the next decade of African business. We deploy small, senior teams against tight briefs — strategy in week one, deliverables shipping by week six. No agency theatre.'),
('hero.cta_primary',   'Start a brief'),
('hero.cta_secondary', 'See the work'),
('final.cta_title',    'Ready to build something {{accent}}powerful?{{/accent}}'),
('final.trust',        '50+ projects delivered · trusted by brands across Africa');

-- ------- Academy: instructors --------------------------------------
REPLACE INTO instructors (name, slug, role, bio, status_pill, status_label, skills, sort) VALUES
('Adaeze Okeke',  'adaeze-okeke',  'Lead Brand Strategist',
 'Adaeze leads our brand strategy and naming work. Twelve years across studios in Lagos, London, and Accra. Quiet authority, strong opinions, all earned.',
 'live', 'Available', 'Strategy, Naming, Voice', 1),
('Tope Adeyemi',  'tope-adeyemi',  'Creative Director',
 'Tope runs our editorial and design systems work. Type-led. Print-rooted. Every grid we ship has his fingerprints on it.',
 'production', 'Booked through Q2', 'Editorial, Type, Print', 2),
('Kemi Balogun',  'kemi-balogun',  'Head of Production',
 'Kemi turns brand strategy into shipped events and shipped film. Zero-drama production, every time.',
 'live', 'Available', 'Film, Events, Logistics', 3);

-- ------- Academy: courses ------------------------------------------
REPLACE INTO courses (title, slug, level, weeks, instructor, price_naira, summary, body, status) VALUES
('Brand Strategy for Founders', 'brand-strategy-for-founders',
 'Foundations', 6, 'Adaeze Okeke', 180000,
 'Build the strategic foundation a real brand needs — positioning, audience, voice, and the identity decisions only you can make.',
 '<p>Six weeks. Live cohort. Built for founders entering a market with intent.</p>',
 'published'),

('Editorial Systems for Teams', 'editorial-systems-for-teams',
 'Intermediate', 4, 'Tope Adeyemi', 240000,
 'Turn a brand voice into a working editorial system your team can run on, surface to surface.',
 '<p>Four weeks. Built for marketing and comms teams running multi-channel programmes.</p>',
 'published'),

('Production at Scale', 'production-at-scale',
 'Advanced', 8, 'Kemi Balogun', 320000,
 'How to run brand production — film, events, editorial — at studio quality without the studio overhead.',
 '<p>Eight weeks. Built for in-house production leads and studio operators.</p>',
 'published');

-- ------- Academy: certifications -----------------------------------
REPLACE INTO certifications (title, slug, summary) VALUES
('Brand Strategist Certificate',     'brand-strategist',     'Demonstrate working command of brand positioning, naming, and verbal systems.'),
('Editorial Systems Certificate',    'editorial-systems',    'Prove fluency in editorial design, type systems, and voice frameworks.'),
('Media Production Skill Badge',     'media-production',     'Show readiness to lead in-house photo, film, and podcast production.'),
('Event Management Foundations',     'event-management',     'Foundational competence in event strategy, logistics, and on-the-day production.');

-- ------- Academy: live sessions ------------------------------------
REPLACE INTO live_sessions (title, summary, starts_at, duration_minutes, instructor, capacity, status) VALUES
('Naming Workshop — From shortlist to lockup', 'A two-hour live working session with Adaeze.',
 DATE_ADD(NOW(), INTERVAL 14 DAY), 120, 'Adaeze Okeke', 25, 'upcoming'),
('Editorial Type Clinic',                       'Bring your layouts. We will review them live.',
 DATE_ADD(NOW(), INTERVAL 28 DAY), 90,  'Tope Adeyemi', 20, 'upcoming'),
('Production Q&A — Anything goes',              'Open Q&A for production leads.',
 DATE_ADD(NOW(), INTERVAL 7 DAY),  60,  'Kemi Balogun', 40, 'upcoming');
