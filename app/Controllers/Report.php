<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ReportModel;
use App\Models\ReportSectionModel;
use App\Services\ReportSectionService;
use App\Services\ReportTemplateService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Report extends BaseController
{
    protected ReportModel $reports;
    protected ReportSectionService $sectionsService;
    protected ReportSectionModel $sections;

    // Si ton front a besoin d’être connecté
    protected $require_auth = true;

    public function __construct()
    {
        $this->reports         = new ReportModel();
        $this->sectionsService = new ReportSectionService();
        $this->sections        = new ReportSectionModel();
    }


    private function currentUserFullName(): string
    {
        $user = session('user');

        $first = trim((string)($user->firstname ?? ''));
        $last  = trim((string)($user->lastname ?? ''));

        $full = trim($first . ' ' . $last);

        // fallback si jamais firstname/lastname pas dispo
        if ($full === '') {
            $full = trim((string)($user->name ?? ''));
        }
        if ($full === '') {
            $full = 'Utilisateur';
        }

        return $full;
    }

    /**
     * Petit helper : récupère l’id user connecté (à adapter à ton auth)
     */
    private function userId(): int
    {
        $user = session()->get('user');
        return (int) ($user->id ?? 0);
    }

// à adapter selon ton système (id_permission, role, etc.)
    private function isAdmin(): bool
    {
        $user = session('user');
        return (int) ($user->id_permission ?? 0) === 1; // exemple
    }

    private function findReportOr404(int $reportId): array
    {
        $report = $this->reports->find($reportId);
        if (! $report) {
            throw PageNotFoundException::forPageNotFound("Report {$reportId} not found");
        }
        return $report;
    }

    private function isOwner(array $report): bool
    {
        return (int)($report['user_id'] ?? 0) === $this->userId();
    }

    private function requireOwnerOrAdmin(array $report): void
    {
        if (! $this->isOwner($report) && ! $this->isAdmin()) {
            // 403 propre (pas 404) : l’objet existe mais tu n’as pas le droit
            throw new \CodeIgniter\Exceptions\PageForbiddenException("Forbidden");
        }
    }


    /**
     * Helper : récupère un report du user, sinon 404
     */
    private function findOwnedReportOr404(int $reportId): array
    {
        $report = $this->reports->find($reportId);

        if (! $report) {
            throw PageNotFoundException::forPageNotFound("Report {$reportId} not found");
        }

        if ((int) ($report['user_id'] ?? 0) !== $this->userId()) {
            // On évite de révéler l’existence d’un report qui n’appartient pas au user
            throw PageNotFoundException::forPageNotFound("Report {$reportId} not found");
        }

        return $report;
    }

    /**
     * Helper : récupère une section du report, sinon 404
     */
    private function findOwnedSectionOr404(int $reportId, int $sectionId): array
    {
        $section = $this->sections->find($sectionId);

        if (! $section || (int) $section['report_id'] !== $reportId) {
            throw PageNotFoundException::forPageNotFound("Section {$sectionId} not found");
        }

        // Vérifie aussi que le report appartient au user
        $this->findOwnedReportOr404($reportId);

        return $section;
    }

    /**
     * GET /reports
     */
    public function getIndex()
    {
        $uid = $this->userId();

        $data = [
            'myReports' => $this->reports
                ->where('user_id', $uid)
                ->orderBy('created_at', 'DESC')
                ->findAll(),

            'otherReports' => $this->reports
                ->where('user_id !=', $uid)
                ->orderBy('created_at', 'DESC')
                ->findAll(),
        ];

        return $this->view('front/reports/index', $data, false);
    }

    /**
     * GET /reports/new
     */
    public function getNew()
    {
        $data = [
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ];

        return $this->view('front/reports/new', $data, false);
    }

    public function getShow(int $id)
    {
        $report = $this->findReportOr404($id);

        // lecture autorisée pour tous
        $canEdit = $this->isOwner($report) || $this->isAdmin(); // si tu veux l’afficher

        // ✅ Tree trié (parents -> enfants) pour l’ordre parfait
        $sectionsTree = $this->sectionsService->getTreeForReport($id);

        return $this->view('front/reports/show', [
            'report'       => $report,
            'sectionsTree' => $sectionsTree,
            'canEdit'      => $canEdit,
        ], false);
    }

    public function getEdit(int $id)
    {
        $report = $this->findReportOr404($id);
        $this->requireOwnerOrAdmin($report); // en front, ça revient à owner (admin n’est pas censé passer ici)

        return $this->view('front/reports/edit', [
            'report'  => $report,
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ], false);
    }

    /**
     * POST /reports
     */
    public function postCreate()
    {
        $post = $this->request->getPost();

        // ... validation title/application/version

        $id = $this->reports->insert([
            'user_id'          => $this->userId(),
            'title'            => $post['title'],
            'application_name' => $post['application_name'],
            'version'          => $post['version'] ?? null,
            'author_name'      => $this->currentUserFullName(),
            'status'           => 'brouillon',
        ], true);

        // config template
        $config = [
            'tests' => [
                'target' => [
                    'enabled' => !empty($post['tpl_target_enabled']),
                    'target'  => (string)($post['tpl_target_value'] ?? ''),
                ],
                'endurance' => ['enabled' => !empty($post['tpl_endurance_enabled'])],
                'limits'    => ['enabled' => !empty($post['tpl_limits_enabled'])],
                'overload'  => ['enabled' => !empty($post['tpl_overload_enabled'])],
            ],
        ];

        $tpl = new ReportTemplateService(
            new ReportSectionModel(),
            $this->sectionsService
        );
        $tpl->buildReportSkeleton((int)$id, $config);

        $this->reports->update((int)$id, [
            'version_date'       => date('Y-m-d H:i:s'),
            'author_updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $history = new \App\Services\ReportHistoryService(new \App\Models\ReportVersionModel());
        $history->add((int)$id, 'draft', $this->userId(), 'Version initiale', $post['version'] ?? null);

        return redirect()->to(site_url('report/' . $id . '/sections'))
            ->with('success', 'Bilan créé avec son squelette. Vous pouvez commencer la rédaction.');
    }

    public function postUpdate(int $id)
    {
        $report = $this->findReportOr404($id);
        $this->requireOwnerOrAdmin($report);

        $post = $this->request->getPost();

        $rules = [
            'title'            => 'required|min_length[3]',
            'application_name' => 'required|min_length[2]',
            'version'          => 'permit_empty|max_length[50]',
            'status'           => 'permit_empty|in_list[brouillon,en_relecture,final]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->reports->update($id, [
            'title'             => $post['title'],
            'application_name'  => $post['application_name'],
            'version'           => $post['version'] ?? null,
            'status'            => $post['status'] ?? ($report['status'] ?? 'brouillon'),
            'author_updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('report/' . $id))->with('success', 'Bilan mis à jour.');
    }

    /**
     * GET /reports/{id}/sections
     */
    public function getSections(int $id)
    {
        $report   = $this->findReportOr404($id);

        $canEdit = $this->isOwner($report);

        $tree  = $this->sectionsService->getTreeForReport($id);
        $roots = $tree;

        return $this->view('front/reports/sections', [
            'report'       => $report,
            'sectionsTree' => $tree,
            'roots'        => $roots,
            'canEdit'      => $canEdit,
            'errors'       => session('errors') ?? [],
            'success'      => session('success'),
        ], false);
    }

    /**
     * POST /reports/{id}/sections/root
     */
    public function postSectionsRoot(int $reportId)
    {
        $report = $this->findReportOr404($reportId);
        $this->requireOwnerOrAdmin($report);

        $post = $this->request->getPost();
        $title = trim($post['title'] ?? '');
        $content = $post['content'] ?? '';

        if ($title === '') {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['title_root' => 'Le titre de la partie est obligatoire.']);
        }

        $this->sectionsService->createRootSection($reportId, [
            'title'   => $title,
            'content' => $content,
        ]);

        return redirect()->to(site_url('report/' . $reportId . '/sections'))
            ->with('success', 'Partie ajoutée.');
    }


    private function findSectionOr404(int $reportId, int $sectionId): array
    {
        $section = $this->sections->find($sectionId);

        if (! $section || (int)$section['report_id'] !== $reportId) {
            throw PageNotFoundException::forPageNotFound("Section {$sectionId} not found");
        }
        return $section;
    }

    /**
     * POST /reports/{id}/sections/{parentId}/child
     */
    public function postSectionsChild(int $reportId, int $parentId)
    {
        $report = $this->findReportOr404($reportId);
        $this->requireOwnerOrAdmin($report);

        $post    = $this->request->getPost();
        $title   = trim($post['title'] ?? '');
        $content = $post['content'] ?? '';

        if ($title === '') {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['title_child_' . $parentId => 'Le titre de la sous-partie est obligatoire.']);
        }

        $parent = $this->findSectionOr404($reportId, $parentId);

        $this->sectionsService->createChildSection($parentId, [
            'title'   => $title,
            'content' => $content,
        ]);

        // ✅ renumérote tout
        $this->sectionsService->recomputeCodes($reportId);

        return redirect()->to(site_url('report/' . $reportId . '/sections'))
            ->with('success', 'Sous-partie ajoutée.');
    }

    /**
     * GET /reports/{reportId}/sections/{sectionId}/edit
     */
    public function getEditSection(int $reportId, int $sectionId)
    {
        $report  = $this->findOwnedReportOr404($reportId);
        $section = $this->findOwnedSectionOr404($reportId, $sectionId);

        $data = [
            'report'  => $report,
            'section' => $section,
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ];

        return $this->view('front/reports/section_edit', $data, false);
    }

    /**
     * POST /reports/{reportId}/sections/{sectionId}/update
     */
    public function postUpdateSection(int $reportId, int $sectionId)
    {
        $this->findOwnedReportOr404($reportId);
        $section = $this->findOwnedSectionOr404($reportId, $sectionId);

        $post = $this->request->getPost();

        $rules = [
            'title'             => 'required|min_length[2]',
            'content'           => 'permit_empty',
            'period_label'      => 'permit_empty|max_length[100]',
            'period_number'     => 'permit_empty|integer',
            'debit_value'       => 'permit_empty|decimal',
            'start_date'        => 'permit_empty|valid_date[Y-m-d]',
            'end_date'          => 'permit_empty|valid_date[Y-m-d]',
            'compliance_status' => 'permit_empty|in_list[conforme,non_conforme,partiel,non_applicable]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->sections->update($sectionId, [
            'title'             => $post['title'],
            'content'           => $post['content'] ?? null,
            'period_label'      => $post['period_label'] ?? null,
            'period_number'     => $post['period_number'] ?: null,
            'debit_value'       => $post['debit_value'] ?: null,
            'start_date'        => $post['start_date'] ?: null,
            'end_date'          => $post['end_date'] ?: null,
            'compliance_status' => $post['compliance_status'] ?? 'non_applicable',
        ]);

        $this->reports->update($reportId, [
            'author_updated_at' => date('Y-m-d H:i:s'),
        ]);

        $history = new \App\Services\ReportHistoryService(new \App\Models\ReportVersionModel());
        $history->add($reportId, 'edit', $this->userId(), 'Modification section (auteur)', $report['version'] ?? null);

        return redirect()->to(site_url('report/' . $reportId . '/sections'))
            ->with('success', 'Section mise à jour.');
    }

    /**
     * POST /reports/{reportId}/sections/{sectionId}/delete
     */
    public function postDeleteSection(int $reportId, int $sectionId)
    {
        $report = $this->findReportOr404($reportId);
        $this->requireOwnerOrAdmin($report);

        $this->findSectionOr404($reportId, $sectionId);

        $this->sections->delete($sectionId);

        $this->sectionsService->recomputeCodes($reportId);

        return redirect()->to(site_url('report/' . $reportId . '/sections'))
            ->with('success', 'Section supprimée (ainsi que ses sous-sections).');
    }

    public function postUploadSectionImage()
    {
        // user doit être connecté (ton BaseController le gère)
        $file = $this->request->getFile('image');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['error' => 'Fichier invalide'])->setStatusCode(400);
        }

        // sécurité : types + taille
        $allowed = ['image/png','image/jpeg','image/webp','image/gif'];
        if (!in_array($file->getMimeType(), $allowed, true)) {
            return $this->response->setJSON(['error' => 'Type non autorisé'])->setStatusCode(400);
        }
        if ($file->getSizeByUnit('mb') > 5) {
            return $this->response->setJSON(['error' => 'Trop volumineux (max 5MB)'])->setStatusCode(400);
        }

        // stockage
        $newName = $file->getRandomName();
        $path = FCPATH . 'uploads/report_sections/';
        if (!is_dir($path)) mkdir($path, 0755, true);

        $file->move($path, $newName);

        $url = base_url('uploads/report_sections/' . $newName);

        return $this->response->setJSON(['url' => $url]);
    }

    public function postDelete(int $id)
    {
        $report = $this->findReportOr404($id);
        $this->requireOwnerOrAdmin($report);

        $this->sections->where('report_id', $id)->delete();
        $this->reports->delete($id);

        return redirect()->to(site_url('report'))->with('success', 'Bilan supprimé.');
    }
}
