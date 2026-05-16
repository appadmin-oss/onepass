<?php

class ContactController extends Controller {
    public function index(): void {
        $this->view('pages/contact', [
            'title'        => 'Contact · Afrostrength',
            'description'  => 'Tell us about the brief. We respond within one working day.',
            'services'     => Service::all(),
            'breadcrumbs'  => [
                ['label' => 'Home', 'href' => url('/')],
                ['label' => 'Contact'],
            ],
        ], 'main');
    }

    public function submit(): void {
        Csrf::require();

        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        // Silent honeypot — bots that fill every field get a fake-success.
        if (!Security::honeypotOk('website')) {
            if ($isAjax) { $this->json(['ok' => true]); return; }
            $this->redirect('/contact?ok=1'); return;
        }
        // Per-IP rate limit: 5 inquiries / 10 minutes is generous for humans,
        // brutal for scrapers.
        if (!Security::rateLimit('contact_' . Security::clientIp(), 5, 600)) {
            $msg = 'You\'ve sent several messages in a short time. Try again shortly.';
            if ($isAjax) { $this->json(['error' => 'rate_limited', 'message' => $msg], 429); return; }
            flash_set('contact_error', $msg);
            $this->redirect('/contact'); return;
        }

        $data = [
            'kind'           => $this->input('kind', 'contact'),
            'name'           => $this->input('name'),
            'email'          => $this->input('email'),
            'phone'          => $this->input('phone'),
            'company'        => $this->input('company'),
            'inquiry_type'   => $this->input('inquiry_type'),
            'service'        => $this->input('service'),
            'brand_stage'    => $this->input('brand_stage'),
            'event_size'     => $this->input('event_size'),
            'event_date'     => $this->input('event_date'),
            'preferred_time' => $this->input('preferred_time'),
            'message'        => $this->input('message'),
        ];

        $v = (new Validator($data))
            ->check('name',  ['required', 'min:2'])
            ->check('email', ['required', 'email']);
        if (!empty($data['phone'])) $v->check('phone', ['phone']);

        if (!$v->passes()) {
            if ($isAjax) $this->json(['error' => 'validation', 'fields' => $v->errors()], 422);
            flash_set('contact_errors', json_encode($v->errors()));
            $this->redirect('/contact#form');
            return;
        }

        $id = Inquiry::create($data);

        $body  = '<h2>New inquiry from afrostrength.com</h2>';
        $body .= '<table cellpadding="6" style="font-family:Arial,sans-serif;font-size:14px;">';
        foreach ($data as $k => $v2) {
            if ($v2 === null || $v2 === '') continue;
            $body .= '<tr><td style="vertical-align:top;color:#888;text-transform:uppercase;font-size:11px;letter-spacing:0.12em;">' .
                     htmlspecialchars($k) . '</td><td>' . nl2br(htmlspecialchars((string)$v2)) . '</td></tr>';
        }
        $body .= '</table>';
        Mailer::send('Afrostrength · New ' . $data['kind'] . ' inquiry', $body, $data['email'] ?: null);

        if ($isAjax) {
            $this->json(['ok' => true, 'id' => $id, 'message' => 'Thanks — we read every brief within a working day.']);
        } else {
            flash_set('contact_success', 'Thanks — we read every brief within a working day.');
            $this->redirect('/contact?ok=1#form');
        }

        // Background-ish: hand the response back to the visitor first,
        // then call the mentor to suggest a route. The studio reads
        // every inquiry by hand regardless — this only helps triage.
        if ($id > 0) {
            if (function_exists('fastcgi_finish_request')) {
                @fastcgi_finish_request();
            }
            try { Inquiry::routeWithAi($id); } catch (\Throwable $e) { /* silent */ }
        }
    }

    /**
     * Newsletter subscribe — stores as a kind="newsletter" inquiry so the
     * studio inbox sees every signup. Lightweight: only requires email.
     */
    public function newsletter(): void {
        Csrf::require();
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
        $email  = $this->input('email');
        $v = (new Validator(['email' => $email]))->check('email', ['required', 'email']);
        if (!$v->passes()) {
            if ($isAjax) $this->json(['error' => 'validation', 'fields' => $v->errors()], 422);
            else $this->redirect('/');
            return;
        }
        Inquiry::create([
            'kind'  => 'newsletter',
            'name'  => 'Newsletter subscriber',
            'email' => $email,
        ]);
        if ($isAjax) $this->json(['ok' => true, 'message' => 'Subscribed. Next letter ships in a week or two.']);
        else $this->redirect('/?subscribed=1');
    }
}
