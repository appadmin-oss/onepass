<?php

namespace Admin;

use Auth, Csrf, Ai, Incident, Database;

/**
 * StatusIncidentsController — operator CRUD for the public status page.
 *
 *   GET  /admin/status/incidents             → table of all incidents
 *   GET  /admin/status/incidents/new         → empty editor
 *   POST /admin/status/incidents             → create
 *   GET  /admin/status/incidents/{id}/edit   → editor for one row
 *   POST /admin/status/incidents/{id}        → update
 *   POST /admin/status/incidents/{id}/resolve     → mark resolved
 *   POST /admin/status/incidents/{id}/postmortem  → AI draft postmortem
 *   POST /admin/status/incidents/{id}/delete      → destroy (operator-confirmed)
 *
 * Every route is Auth::require() + Csrf::require() (where mutating).
 * Public-facing /status reads the same table; no separate publish step
 * — operator writes, page reflects on next 30s poll.
 */
class StatusIncidentsController extends \Controller {

    public function index(): void {
        Auth::require();
        $this->view('admin/status/incidents/index', [
            'title'       => 'Status incidents · Admin',
            'incidents'   => Incident::all(100),
            'breadcrumbs' => [
                ['label' => 'Admin',  'href' => url('/admin')],
                ['label' => 'Status incidents'],
            ],
        ], 'admin');
    }

    /** GET /admin/status/incidents/new — empty editor shell. */
    public function newForm(): void {
        Auth::require();
        $this->renderEditor([
            'id'             => 0,
            'public_id'      => '',
            'title'          => '',
            'body'           => '',
            'postmortem'     => '',
            'kind'           => 'incident',
            'severity'       => 'minor',
            'components_csv' => '',
            'started_at'     => date('Y-m-d\TH:i'),
            'resolved_at'    => null,
        ]);
    }

    /** GET /admin/status/incidents/{id}/edit */
    public function edit(string $id = ''): void {
        Auth::require();
        $row = Incident::find((int)$id);
        if (!$row) { $this->notFound(); return; }
        // Convert DB datetime → datetime-local input format.
        if (!empty($row['started_at'])) {
            $row['started_at'] = date('Y-m-d\TH:i', strtotime((string)$row['started_at']));
        }
        if (!empty($row['resolved_at'])) {
            $row['resolved_at'] = date('Y-m-d\TH:i', strtotime((string)$row['resolved_at']));
        }
        $this->renderEditor($row);
    }

    private function renderEditor(array $row): void {
        $this->view('admin/status/incidents/edit', [
            'title'       => ($row['id'] ? 'Edit incident' : 'New incident') . ' · Admin',
            'row'         => $row,
            'components'  => Incident::COMPONENTS,
            'severities'  => Incident::SEVERITIES,
            'kinds'       => Incident::KINDS,
            'breadcrumbs' => [
                ['label' => 'Admin',             'href' => url('/admin')],
                ['label' => 'Status incidents',  'href' => url('/admin/status/incidents')],
                ['label' => $row['id'] ? 'Edit' : 'New'],
            ],
        ], 'admin');
    }

    /** POST /admin/status/incidents — create. */
    public function create(): void {
        Auth::require(); Csrf::require();
        $data = $this->collect();
        if ($data['title'] === '') {
            flash_set('admin_status_error', 'Title is required.');
            $this->redirect('/admin/status/incidents');
            return;
        }
        $data['created_by'] = $_SESSION['admin']['username'] ?? null;
        $id = Incident::create($data);
        flash_set('admin_status_ok', $id ? 'Incident created.' : 'Could not create (DB unavailable).');
        $this->redirect($id ? '/admin/status/incidents/' . $id . '/edit' : '/admin/status/incidents');
    }

    /** POST /admin/status/incidents/{id} — update. */
    public function update(string $id = ''): void {
        Auth::require(); Csrf::require();
        $data = $this->collect();
        $ok = Incident::update((int)$id, $data);
        flash_set($ok ? 'admin_status_ok' : 'admin_status_error',
                  $ok ? 'Saved.' : 'Could not save.');
        $this->redirect('/admin/status/incidents/' . (int)$id . '/edit');
    }

    /** POST /admin/status/incidents/{id}/resolve. */
    public function resolve(string $id = ''): void {
        Auth::require(); Csrf::require();
        $ok = Incident::resolve((int)$id);
        flash_set($ok ? 'admin_status_ok' : 'admin_status_error',
                  $ok ? 'Resolved.' : 'Already resolved, or not found.');
        $this->redirect('/admin/status/incidents/' . (int)$id . '/edit');
    }

    /**
     * POST /admin/status/incidents/{id}/postmortem — call the AI to
     * draft a postmortem. Cached per resolved incident (24h via the
     * AiRegistry), so re-clicking after a tweak doesn't burn budget.
     * Operator edits the draft before saving — edit-then-publish.
     */
    public function postmortemDraft(string $id = ''): void {
        Auth::require(); Csrf::require();
        $row = Incident::find((int)$id);
        if (!$row) { $this->notFound(); return; }

        if (empty($row['resolved_at'])) {
            flash_set('admin_status_error', 'Resolve the incident before drafting a postmortem.');
            $this->redirect('/admin/status/incidents/' . (int)$id . '/edit');
            return;
        }

        $context = json_encode([
            'title'       => $row['title'],
            'body'        => mb_substr((string)($row['body'] ?? ''), 0, 1200),
            'severity'    => $row['severity'],
            'components'  => $row['components_csv'],
            'started_at'  => $row['started_at'],
            'resolved_at' => $row['resolved_at'],
        ], JSON_UNESCAPED_UNICODE);

        $res = Ai::run('status_postmortem', $context);
        if ($res['ok'] && !empty($res['text'])) {
            // Don't overwrite an existing postmortem — append a clearly
            // labelled AI block instead, so the operator can compare and
            // pick what to keep.
            $existing = (string)($row['postmortem'] ?? '');
            $draft = '## AI draft (' . date('j M H:i') . ')' . "\n\n" . trim($res['text']);
            $combined = $existing === '' ? $draft : $existing . "\n\n---\n\n" . $draft;
            Incident::setPostmortem((int)$id, $combined);
            flash_set('admin_status_ok', 'AI draft appended below your existing notes.');
        } else {
            $reason = $res['error'] ?? 'unknown';
            flash_set('admin_status_error', 'AI unavailable (' . $reason . '). Try again or write from scratch.');
        }
        $this->redirect('/admin/status/incidents/' . (int)$id . '/edit');
    }

    public function delete(string $id = ''): void {
        Auth::require(); Csrf::require();
        if (Database::available()) {
            Database::exec('DELETE FROM incidents WHERE id = ?', [(int)$id]);
        }
        flash_set('admin_status_ok', 'Deleted.');
        $this->redirect('/admin/status/incidents');
    }

    /** Pull + sanitise the editor form into the model's expected shape. */
    private function collect(): array {
        return [
            'title'       => (string) $this->input('title', ''),
            'body'        => (string) $this->input('body', ''),
            'postmortem'  => (string) $this->input('postmortem', ''),
            'kind'        => (string) $this->input('kind', 'incident'),
            'severity'    => (string) $this->input('severity', 'minor'),
            'components'  => $_POST['components'] ?? [],   // checkboxes
            'started_at'  => (string) $this->input('started_at', ''),
            'resolved_at' => (string) $this->input('resolved_at', ''),
        ];
    }
}
