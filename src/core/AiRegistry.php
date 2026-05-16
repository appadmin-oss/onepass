<?php

/**
 * AiRegistry — single source of truth for every AI intent on the site.
 *
 * Previously the intent → prompt mapping lived in AiController::suggest()
 * and individual surfaces (StatusController::aiSummary, etc) repeated
 * their own system prompts. That made tone-consistency, caching policy
 * and fallback discipline impossible to enforce.
 *
 * Every AI caller should now resolve through AiRegistry::resolve($intent).
 * The returned shape is everything Ai::run() needs:
 *
 *   ['system'   => string  — fully composed system prompt (incl. brand voice)
 *    'cap'      => int     — context byte cap for this intent
 *    'cacheable'=> bool    — whether the response is safe to cache
 *    'ttl'      => int     — cache TTL in seconds (when cacheable)
 *    'pii'      => bool    — whether the prompt may carry PII (NEVER cache)
 *    'fallback' => string] — text to render when AI is paused / budgeted / down
 *
 * Adding a new intent: append one entry to the INTENTS const, give it a
 * brand-voice fallback, and you're done. No other code changes required.
 */
class AiRegistry {

    public const INTENTS = [
        // ---- Slug tool ----
        'meta_title' => [
            'task' => "Write a single 50–60 character SEO title for this content. "
                    . "Title case. No emojis. No quotes around it. Just the title.",
            'cap' => 2000, 'cacheable' => true, 'ttl' => 86400, 'pii' => false,
            'fallback' => 'Write a clear, specific title in your own words.',
        ],
        'meta_desc' => [
            'task' => "Write a single SEO meta description (140–155 characters). "
                    . "Direct, useful, scannable. No emojis. No quotes around it.",
            'cap' => 2000, 'cacheable' => true, 'ttl' => 86400, 'pii' => false,
            'fallback' => 'Summarise the page in one sentence under 155 characters.',
        ],

        // ---- Contact form ----
        'brief_polish' => [
            'task' => "The user pasted a rough project brief. Rewrite it as a clear, "
                    . "professional 2–3 sentence message we can send to the studio. "
                    . "Keep their facts. Sharpen the verbs. No salutations or signoff.",
            'cap' => 2000, 'cacheable' => false, 'ttl' => 0, 'pii' => true,
            'fallback' => 'Send the brief as you wrote it — we read every message.',
        ],

        // ---- Onboarding ----
        'goal_polish' => [
            'task' => "Rewrite this learning goal in 1–2 sharp sentences. "
                    . "Specific, ambitious, no fluff.",
            'cap' => 2000, 'cacheable' => true, 'ttl' => 86400, 'pii' => false,
            'fallback' => 'Goals work best when they are one specific sentence.',
        ],

        // ---- Promotions admin ----
        'promo_title' => [
            'task' => "Write a single short, punchy promotion title (under 60 chars). "
                    . "Direct verb-led; no emojis; no quotes.",
            'cap' => 2000, 'cacheable' => true, 'ttl' => 86400, 'pii' => false,
            'fallback' => 'A short verb-led title (under 60 chars) works best.',
        ],
        'promo_sub' => [
            'task' => "Write a single subtitle for this promotion (1 sentence, "
                    . "under 130 chars). Supports the title without repeating.",
            'cap' => 2000, 'cacheable' => true, 'ttl' => 86400, 'pii' => false,
            'fallback' => 'One supporting line under 130 chars.',
        ],
        'promo_variants' => [
            'task' => "Generate cross-channel copy variants from the supplied "
                    . "promotion {title, subtitle, body}. Return ONLY a JSON "
                    . "object: {\"tweet\":<240ch>,\"linkedin\":<2 short paras>,"
                    . "\"email_subject\":<60ch>,\"email_preheader\":<90ch>}. "
                    . "No code fences, no preamble.",
            'cap' => 3000, 'cacheable' => true, 'ttl' => 86400, 'pii' => false,
            'fallback' => 'Hand-write each channel: Twitter, LinkedIn, email subject + preheader.',
        ],

        // ---- Tools — career path ----
        'career_path' => [
            'task' => "You are matching a person to one Afrotech Academy track. "
                    . "Reply with a single JSON object and NOTHING else (no code fences, "
                    . "no preamble). Schema: {\"track\":<slug>,\"why\":<2 short sentences>,"
                    . "\"next\":[3 concrete first-week actions]}. Pick the track slug from "
                    . "the list provided in the prompt. Tone: direct, warm, honest. "
                    . "If their inputs are contradictory, pick the better fit and say so in 'why'.",
            'cap' => 4000, 'cacheable' => true, 'ttl' => 86400, 'pii' => false,
            'fallback' => 'Pick the track that matches the strongest score and book a free orientation call.',
        ],

        // ---- Cmd+K search fallback ----
        'search_guess' => [
            'task' => "The user typed a search query in our command palette and "
                    . "got no fuzzy match. Pick the SINGLE best-matching route from "
                    . "the catalogue in the prompt. Reply with ONLY a JSON object "
                    . "(no code fences, no preamble) of shape "
                    . "{\"href\":<one of routes[].href verbatim>,\"why\":<<= 18 words>}. "
                    . "If nothing in the catalogue plausibly fits, pick the closest "
                    . "anyway — never invent a path.",
            'cap' => 6000, 'cacheable' => true, 'ttl' => 3600, 'pii' => false,
            'fallback' => 'No match — try a shorter keyword.',
        ],

        // ---- Tracker — weekly review ----
        'learning_review' => [
            'task' => "You are an Afrotech mentor reviewing a student's last week of "
                    . "self-study. Given the session log they paste, write a calm, honest "
                    . "review in plain prose: one paragraph (4-5 sentences) reflecting what "
                    . "they did and the pattern that's emerging, then two short bullets — "
                    . "the single thing to keep doing, and one specific suggestion for next "
                    . "week. No headings, no preamble, no praise theatre.",
            'cap' => 8000, 'cacheable' => false, 'ttl' => 0, 'pii' => false,
            'fallback' => 'Take a moment to scroll your log. Which day felt strongest? What blocked you?',
        ],

        // ---- Status page — operator-facing ----
        'status_impact_plain' => [
            'task' => "You are summarising an active outage for a public status page. "
                    . "Two sentences max. Plain, calm prose. Name the affected components "
                    . "by their human label (LMS, AI mentor, etc.). Do not speculate on "
                    . "cause if not stated. No emojis. No preamble.",
            'cap' => 2000, 'cacheable' => false, 'ttl' => 0, 'pii' => false,
            'fallback' => 'Some Academy services are slower than usual — see the incident list below.',
        ],
        'status_postmortem' => [
            'task' => "Given a resolved incident timeline JSON {started_at, resolved_at, "
                    . "severity, body, components}, write a 3-bullet postmortem skeleton: "
                    . "(1) What happened, (2) Mitigation, (3) What we changed. Plain prose, "
                    . "no markdown, one sentence per bullet. No blame language.",
            'cap' => 4000, 'cacheable' => true, 'ttl' => 86400, 'pii' => false,
            'fallback' => '', // operator writes from scratch
        ],
        'status_translate' => [
            'task' => "Translate the supplied English status verdict line into the requested "
                    . "language (one of: pidgin, yoruba, igbo, hausa). Return only the "
                    . "translated line, no preamble, no quotes. Keep it under 80 characters.",
            'cap' => 1000, 'cacheable' => true, 'ttl' => 2592000, 'pii' => false,
            'fallback' => '', // EN-only display
        ],

        // ---- Visitor assistance ----
        'services_brief_match' => [
            'task' => "Given an Afrostrength service summary + the visitor's rough brief, "
                    . "reply with exactly 3 bullets in plain prose: "
                    . "(1) one matching deliverable, (2) one matching process step, "
                    . "(3) where this service falls short for their needs (honest, may "
                    . "say 'not a fit'). One sentence per bullet. No markdown, no preamble.",
            'cap' => 3000, 'cacheable' => true, 'ttl' => 3600, 'pii' => true,
            'fallback' => 'Email the brief to afrostrength@gmail.com and we will match a service within one working day.',
        ],
        'course_fit' => [
            'task' => "Given the Afrotech course outline + a visitor's note on what they "
                    . "already know and want, reply with TWO short bullets: "
                    . "(1) a fit verdict in one sentence (yes / no / partial), "
                    . "(2) the first unit/week they should look at. "
                    . "Honest if it's not a fit. No markdown.",
            'cap' => 3000, 'cacheable' => false, 'ttl' => 0, 'pii' => true,
            'fallback' => 'Read the course outline, then book a free 15-min orientation call.',
        ],
        'faq_search' => [
            'task' => "Given the visitor's question and the supplied list of {id, q, a} "
                    . "from our FAQ, return ONLY a JSON object: {\"ids\":[1-3 best matching "
                    . "ids],\"why\":<one sentence>}. No code fences, no preamble. If nothing "
                    . "in the list plausibly fits, return {\"ids\":[],\"why\":\"no match\"}.",
            'cap' => 6000, 'cacheable' => true, 'ttl' => 3600, 'pii' => false,
            'fallback' => 'Scan the FAQ list — every question is keyword-searchable in your browser (Ctrl/Cmd+F).',
        ],
        'verify_plain' => [
            'task' => "Given a certification title + skills list, write a 2-sentence note "
                    . "in plain prose explaining what holding this credential typically means "
                    . "in the Nigerian / pan-African tech job market today. Honest about "
                    . "typical level (entry / mid / senior). No emojis, no preamble.",
            'cap' => 2000, 'cacheable' => true, 'ttl' => 2592000, 'pii' => false,
            'fallback' => '',
        ],

        // ---- Operator productivity ----
        'inquiry_route' => [
            'task' => "Given an inquiry brief + service tag + budget tier, return ONLY a "
                    . "JSON object: {\"route\":<one of brand-dev|creative|media|project-event|"
                    . "digital|training|general>,\"confidence\":<0-1>,\"why\":<one sentence>}. "
                    . "No code fences, no preamble.",
            'cap' => 3000, 'cacheable' => false, 'ttl' => 0, 'pii' => true,
            'fallback' => '',
        ],
        'content_check' => [
            'task' => "Decide if the supplied user-submitted text contains spam, harassment, "
                    . "hate speech, sexually-explicit content, or violent threats. Return "
                    . "ONLY a JSON object: {\"ok\":<bool>,\"reason\":<short label or null>}. "
                    . "No code fences. Default to 'ok' if unsure — operators will review.",
            'cap' => 2000, 'cacheable' => false, 'ttl' => 0, 'pii' => true,
            'fallback' => '',
        ],

        // ---- Generic ----
        'rewrite' => [
            'task' => "Rewrite this clearly, professionally, in our brand voice.",
            'cap' => 2000, 'cacheable' => false, 'ttl' => 0, 'pii' => true,
            'fallback' => 'Try saying it in one short sentence.',
        ],
    ];

    /**
     * Resolve an intent into a ready-to-call config.
     * Falls back to 'rewrite' for unknown intents (mirrors the prior
     * AiController behaviour).
     */
    public static function resolve(string $intent): array {
        $cfg = self::INTENTS[$intent] ?? self::INTENTS['rewrite'];
        return [
            'intent'    => isset(self::INTENTS[$intent]) ? $intent : 'rewrite',
            'system'    => Ai::brandSystem() . "\n\nTask: " . $cfg['task'],
            'cap'       => (int)$cfg['cap'],
            'cacheable' => (bool)$cfg['cacheable'],
            'ttl'       => (int)$cfg['ttl'],
            'pii'       => (bool)$cfg['pii'],
            'fallback'  => (string)$cfg['fallback'],
        ];
    }

    /** List every known intent — used by the admin AI dashboard. */
    public static function known(): array {
        return array_keys(self::INTENTS);
    }
}
