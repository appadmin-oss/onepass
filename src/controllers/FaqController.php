<?php

class FaqController extends Controller {
    public function index(): void {
        $faqs = [
            ['q' => 'How long does a typical engagement take?',
             'a' => 'Most briefs ship inside 6–12 weeks. Brand strategy + identity in 6, media production in 3, full digital MVPs in 8–10. We tell you on the call if a brief can\'t be done in twelve weeks.'],
            ['q' => 'Do you work outside Africa?',
             'a' => 'We are based in Lagos with two studios (CACENTRE Ayobo and CACENTRE Egbeda) and work with clients across Africa, Europe, the US, and the Gulf — especially when the brief has any African business or audience dimension.'],
            ['q' => 'Are you a one-person studio or a full team?',
             'a' => 'A full senior team. Strategy leads, designers, producers, engineers — all senior, all in-house. We don\'t subcontract delivery work.'],
            ['q' => 'Can we keep working with you after the brief?',
             'a' => 'Yes. Most clients move to a monthly retainer after the initial engagement so the brand keeps shipping without losing momentum.'],
            ['q' => 'What does the Academy do, and is it separate?',
             'a' => 'Afrostrength Academy runs cohort-based courses and certifications for marketing leads, in-house creatives, and operators. Same studio, same instructors — just with structured curriculum.'],
            ['q' => 'How do you price?',
             'a' => 'Fixed-scope, fixed-fee for most briefs. We send a clear scope and a clear number after the discovery call. No hourly billing, no surprise change orders.'],
            ['q' => 'Can you sign an NDA before we share details?',
             'a' => 'Of course. We sign a mutual NDA before any discovery call when the brief is sensitive.'],
        ];
        $this->view('pages/faq', [
            'title'        => 'FAQ · Afrostrength',
            'description'  => 'How we work, who we work with, and what to expect.',
            'faqs'         => $faqs,
            'breadcrumbs'  => [
                ['label' => 'Home', 'href' => url('/')],
                ['label' => 'FAQ'],
            ],
            'jsonld'       => [[
                '@context' => 'https://schema.org',
                '@type'    => 'FAQPage',
                'mainEntity' => array_map(fn($f) => [
                    '@type' => 'Question',
                    'name'  => $f['q'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
                ], $faqs),
            ]],
        ], 'main');
    }
}
