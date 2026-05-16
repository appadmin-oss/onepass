<?php

/**
 * CertificateController — public certificate verification surface.
 *
 *   /verify           → search page (paste a code OR a recipient-supplied URL)
 *   /verify/{code}    → public detail page; returns 200 with the certificate's
 *                       public-safe fields, or 404 with a clear message.
 *
 * The page is intentionally cache-friendly: the same code always renders
 * the same content (until revoked), and there's no PII beyond the
 * student's full name and the certification details (which are the
 * whole point of public verification).
 */
class CertificateController extends Controller {

    public function index(): void {
        $code = (string) $this->input('code', '');
        if ($code !== '') {
            $canonical = IssuedCertificate::normaliseCode($code);
            $this->redirect('/verify/' . rawurlencode($canonical));
            return;
        }
        $this->view('pages/verify/index', [
            'title'       => 'Verify a certificate · Afrostrength',
            'description' => 'Check whether an Afrostrength / Afrotech Academy certificate is authentic.',
            'breadcrumbs' => [
                ['label' => 'Home', 'href' => url('/')],
                ['label' => 'Verify a certificate'],
            ],
        ], 'main');
    }

    public function show(string $code = ''): void {
        $code = trim($code);
        if ($code === '') { $this->notFound(); return; }
        $row = IssuedCertificate::findByCode($code);

        $data = [
            'breadcrumbs' => [
                ['label' => 'Home',   'href' => url('/')],
                ['label' => 'Verify', 'href' => url('/verify')],
                ['label' => $code],
            ],
        ];
        if (!$row) {
            $data['title']       = 'Certificate not found · Afrostrength';
            $data['description'] = 'We can\'t find a certificate with that code. Check the code and try again.';
            $data['code']        = IssuedCertificate::normaliseCode($code);
            $this->view('pages/verify/not-found', $data, 'main');
            return;
        }
        $data['title']       = 'Certificate ' . $row['code'] . ' · Verified · Afrostrength';
        $data['description'] = 'Verified Afrostrength certificate awarded to ' . $row['student_name'] . '.';
        $data['cert']        = $row;
        $data['skills']      = json_decode((string)($row['skills_json'] ?? '[]'), true) ?: [];
        $this->view('pages/verify/show', $data, 'main');
    }
}
