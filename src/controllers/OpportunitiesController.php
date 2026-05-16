<?php

class OpportunitiesController extends Controller {
    public function index(): void {
        $now = time();
        $opps = [
            [
                'kind'        => 'Scholarship',
                'title'       => 'Afrotech Strength Scholarship — full-tuition',
                'eyebrow'     => '// FULLY FUNDED',
                'summary'     => 'Five fully-funded seats per cohort across our nine tracks for Nigerian applicants from under-represented backgrounds.',
                'eligibility' => ['Nigerian resident', '18+ years old', 'No prior paid tech role required', 'Commit to attending live sessions'],
                'deadline'    => date('Y-m-d', strtotime('+22 days', $now)),
                'seats'       => 5,
                'cta'         => 'Apply via the academy form',
                'cta_href'    => url('/academy/apply?track=software-development'),
            ],
            [
                'kind'        => 'Fellowship',
                'title'       => 'Women-in-Tech Fellowship · AI / ML track',
                'eyebrow'     => '// PARTNER · MERIT',
                'summary'     => '75% tuition cover for women joining the AI / ML Engineering cohort, plus a dedicated mentor and access to our growth network.',
                'eligibility' => ['Self-identifies as a woman', 'Basic Python familiarity', 'Two-page application essay', 'Available for live sessions'],
                'deadline'    => date('Y-m-d', strtotime('+30 days', $now)),
                'seats'       => 8,
                'cta'         => 'Apply for the fellowship',
                'cta_href'    => url('/academy/apply?track=ai-ml-engineering'),
            ],
            [
                'kind'        => 'Bursary',
                'title'       => 'Lagos Public-School Bursary',
                'eyebrow'     => '// FOR STUDENTS',
                'summary'     => 'A 60% tuition bursary for current public-school students or recent graduates in Lagos State. Frontend + Design tracks.',
                'eligibility' => ['Public-school student / recent graduate', 'Lagos resident', 'School verification letter', 'Parent / guardian consent if under 18'],
                'deadline'    => date('Y-m-d', strtotime('+12 days', $now)),
                'seats'       => 15,
                'cta'         => 'Apply with school letter',
                'cta_href'    => url('/contact'),
            ],
            [
                'kind'        => 'Hackathon',
                'title'       => 'Afrostrength Build Sprint · 48 hours',
                'eyebrow'     => '// EVENT · ₦1.5M PRIZE',
                'summary'     => 'A weekend build sprint hosted at CACENTRE Egbeda. Top three teams split ₦1.5M and join the next cohort tuition-free.',
                'eligibility' => ['Teams of 2–4', 'At least one Nigerian member', 'Bring your laptop', 'No prior cohort attendance needed'],
                'deadline'    => date('Y-m-d', strtotime('+7 days', $now)),
                'seats'       => 60,
                'cta'         => 'Register your team',
                'cta_href'    => url('/contact'),
            ],
            [
                'kind'        => 'Partner role',
                'title'       => 'Studio Junior Operator · 6-month placement',
                'eyebrow'     => '// HIRING · PAID',
                'summary'     => 'A six-month paid placement on a live studio project — design, dev or production. Top performers are offered full-time roles.',
                'eligibility' => ['Completed an Afrotech cohort (or equivalent)', 'Portfolio of shipped work', 'Available 4 days a week in Lagos'],
                'deadline'    => date('Y-m-d', strtotime('+45 days', $now)),
                'seats'       => 3,
                'cta'         => 'Apply for placement',
                'cta_href'    => url('/contact'),
            ],
        ];

        $this->view('pages/opportunities', [
            'title'       => 'Opportunities &amp; scholarships · Afrostrength',
            'description' => 'Active scholarships, fellowships, bursaries and partner roles. Routes into Afrotech Academy + the Afrostrength studio.',
            'opps'        => $opps,
            'breadcrumbs' => [
                ['label' => 'Home',         'href' => url('/')],
                ['label' => 'Opportunities'],
            ],
        ], 'main');
    }
}
