<?php

/**
 * AiController — public AI endpoints for the assistant + tool helpers.
 * All endpoints CSRF-gated and rate-limited (Ai::chat handles the latter).
 */
class AiController extends Controller {

    /** POST /api/ai/chat — site assistant (limited scope, brand-aware). */
    public function chat(): void {
        Csrf::require();
        $userMsg = trim((string)$this->input('message', ''));
        if ($userMsg === '' || strlen($userMsg) > 1200) {
            $this->json(['error' => 'bad_input'], 422); return;
        }

        $history = $_SESSION['ai_chat'] ?? [];
        $history[] = ['role' => 'user', 'content' => $userMsg];
        // Keep the rolling window short to limit upstream cost + drift.
        $history = array_slice($history, -8);

        $system = Ai::brandSystem() . "\n\n"
                . "You answer questions about Afrostrength (studio + Afrotech Academy). "
                . "Key facts you can rely on:\n"
                . "- 6 services: brand-development, creative-design, media-solutions, "
                . "project-event-management, digital-solutions, client-training.\n"
                . "- 9 academy tracks: cloud-engineering, cybersecurity, data-analysis, "
                . "software-development, ai-ml-engineering, frontend-development, "
                . "animation, design, digital-marketing.\n"
                . "- Apply form lives at /academy/apply (6 trust-first steps).\n"
                . "- Free tools live under /tools (short-link, qr, utm, career-path, "
                . "salary, focus, palette, slug).\n"
                . "- Contact: afrostrength@gmail.com, +234-810-019-1456.\n"
                . "- Two Lagos branches: CACENTRE Egbeda + CACENTRE Ayobo.\n"
                . "If the user wants to apply, recommend /academy/apply. "
                . "If they want a brief, recommend /contact. "
                . "If you don't know, say so and point at /contact.";

        $messages = array_merge([['role' => 'system', 'content' => $system]], $history);
        $res = Ai::chat($messages);
        if (!$res['ok']) {
            $this->json(['error' => $res['error'], 'message' => $res['message'] ?? 'AI unavailable.'], 503);
            return;
        }
        $history[] = ['role' => 'assistant', 'content' => $res['text']];
        $_SESSION['ai_chat'] = $history;
        $this->json(['ok' => true, 'reply' => $res['text'], 'provider' => $res['provider'] ?? '']);
    }

    /** POST /api/ai/reset — clears the assistant's session history. */
    public function resetChat(): void {
        Csrf::require();
        unset($_SESSION['ai_chat']);
        $this->json(['ok' => true]);
    }

    /** POST /api/ai/suggest — generic prompt/intent helper used by tools + forms. */
    public function suggest(): void {
        Csrf::require();
        $intent  = (string) $this->input('intent', 'rewrite');
        $context = trim((string) $this->input('context', ''));

        // Single resolution path — registry decides cap, system prompt,
        // cacheability, PII flag, and the canned fallback string. The
        // run() pipeline handles cache, budget, log + provider race.
        $res = Ai::run($intent, $context);

        if (!$res['ok']) {
            // Surface the budget / bad-input / upstream error AND the
            // canned fallback the UI can render without a second call.
            $code = ($res['error'] ?? '') === 'bad_input' ? 422 :
                    (($res['error'] ?? '') === 'budget'   ? 429 : 503);
            $this->json([
                'error'    => $res['error']     ?? 'upstream',
                'message'  => $res['message']   ?? 'AI unavailable.',
                'fallback' => $res['fallback']  ?? '',
            ], $code);
            return;
        }
        $this->json([
            'ok'       => true,
            'text'     => $res['text'],
            'provider' => $res['provider'] ?? '',
            'cached'   => !empty($res['cached']),
        ]);
    }
}
