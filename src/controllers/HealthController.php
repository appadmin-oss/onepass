<?php

/**
 * Health checks for the third-party integrations. Returns JSON so the
 * admin dashboard, monitoring tools, or curl smoke tests can verify
 * connectivity without leaking the tokens themselves.
 */
class HealthController extends Controller {
    public function lms(): void {
        $this->json(Lms::selfTest());
    }

    public function jaas(): void {
        $mode = Jaas::mode();
        $this->json([
            'ok'     => true,
            'mode'   => $mode,
            'domain' => $mode === 'jaas' ? JAAS_DOMAIN : 'meet.jit.si',
            'detail' => $mode === 'jaas'
                ? 'JaaS tenant active (commercial Jitsi).'
                : 'Public open-source Jitsi at meet.jit.si — sessions work without a paid tenant.',
        ]);
    }

    public function ai(): void {
        if (!Ai::enabled()) {
            $this->json(['ok' => false, 'mode' => 'disabled', 'detail' => 'AI is disabled via AI_ENABLED=0.']);
            return;
        }
        $s = Ai::status();
        $payload = [
            'ok'        => true,
            'mode'      => $s['racing'] ? 'race' : 'fallback',
            'primary'   => $s['primary'],
            'providers' => $s['providers'],
            'detail'    => $s['racing']
                ? 'Two free providers race in parallel; first usable answer wins.'
                : 'Primary provider runs first; the other is the fallback.',
        ];
        // ?deep=1 → actually round-trip both providers. Slower (~1–3s) but
        // proves the integration works end-to-end. Used by monitoring + admin.
        if (!empty($_GET['deep'])) {
            $payload['liveCheck'] = Ai::selfTest();
            $payload['ok'] = $payload['liveCheck']['ok'];
        }
        $this->json($payload);
    }
}
