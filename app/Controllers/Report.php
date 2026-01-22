<?php

namespace App\Controllers;
use CodeIgniter\HTTP\ResponseInterface;

use App\Controllers\BaseController;
use App\Models\ReportModel;
use App\Models\ReportSectionModel;
use App\Models\UserModel;
use App\Services\ReportSectionService;
use App\Services\ReportTemplateService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Report extends BaseController
{
    protected ReportModel $reports;
    protected ReportSectionService $sectionsService;
    protected ReportSectionModel $sections;

    protected $require_auth = true;

    public function __construct()
    {
        $this->reports         = new ReportModel();
        $this->sectionsService = new ReportSectionService();
        $this->sections        = new ReportSectionModel();
    }

    private function userId(): int
    {
        $user = session()->get('user');
        return (int)($user->id ?? 0);
    }

    private function currentUserFullName(): string
    {
        $user = session('user');

        $first = trim((string)($user->firstname ?? ''));
        $last  = trim((string)($user->lastname ?? ''));
        $full  = trim($first . ' ' . $last);

        if ($full === '') $full = trim((string)($user->name ?? ''));
        if ($full === '') $full = 'Utilisateur';

        return $full;
    }

    private function isAdmin(): bool
    {
        $user = session('user');
        return (int)($user->id_permission ?? 0) === 1;
    }

    /**
     * ✅ Récupère un report + noms users
     * - validated_by (string) = "Prénom Nom"
     * - validated_by_id (int|null) = id original
     * - corrected_by (string) = "Prénom Nom" (optionnel)
     * - corrected_by_id (int|null)
     */
    private function findReportWithUsersOr404(int $reportId): array
    {
        $row = $this->reports
            ->select('
            reports.*,

            reports.validated_by AS validated_by_id,
            CONCAT(vu.firstname, " ", vu.lastname) AS validated_by,

            reports.corrected_by AS corrected_by_id,
            CONCAT(cu.firstname, " ", cu.lastname) AS corrected_by,

            m.id        AS file_media_id_join,
            m.file_name AS file_name,
            m.file_path AS file_path
        ')
            ->join('user vu', 'vu.id = reports.validated_by', 'left')
            ->join('user cu', 'cu.id = reports.corrected_by', 'left')
            ->join('media m', 'm.id = reports.file_media_id', 'left')
            ->where('reports.id', $reportId)
            ->first();

        if (!$row) {
            throw PageNotFoundException::forPageNotFound("Report {$reportId} not found");
        }

        return $row;
    }

    private function requireOwnerOrAdmin(array $report): void
    {
        $ownerId = (int)($report['user_id'] ?? 0);
        if ($ownerId !== $this->userId() && !$this->isAdmin()) {
            throw new PageForbiddenException('Accès refusé');
        }
    }

    private function requireOwner(array $report): void
    {
        $ownerId = (int)($report['user_id'] ?? 0);
        if ($ownerId !== $this->userId()) {
            // en front: on peut choisir 404 pour ne pas révéler, mais tu utilises parfois 403
            throw new PageForbiddenException('Accès refusé');
        }
    }

    private function findSectionOr404(int $reportId, int $sectionId): array
    {
        $section = $this->sections->find($sectionId);
        if (!$section || (int)$section['report_id'] !== $reportId) {
            throw PageNotFoundException::forPageNotFound("Section {$sectionId} not found");
        }
        return $section;
    }

    /**
     * GET /report
     */
    public function getIndex()
    {
        $uid = $this->userId();

        return $this->view('front/reports/index', [
            'myReports' => $this->reports->where('user_id', $uid)->orderBy('created_at', 'DESC')->findAll(),
            'otherReports' => $this->reports->where('user_id !=', $uid)->orderBy('created_at', 'DESC')->findAll(),
        ], ['saveData' => false]);
    }

    /**
     * GET /report/new
     */
    public function getNew()
    {
        return $this->view('front/reports/new', [
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ], ['saveData' => false]);
    }

    /**
     * GET /report/{id}
     */
    public function getShow(int $id)
    {
        $report = $this->findReportWithUsersOr404($id);

        // lecture autorisée pour tous (comme tu l’avais)
        $canEdit = ((int)($report['user_id'] ?? 0) === $this->userId()) || $this->isAdmin();

        $sectionsTree = $this->sectionsService->getTreeForReport($id);

        return $this->view('front/reports/show', [
            'report'       => $report,
            'sectionsTree' => $sectionsTree,
            'canEdit'      => $canEdit,
        ], ['saveData' => false]);
    }

    /**
     * GET /report/{id}/edit
     */
    public function getEdit(int $id)
    {
        $report = $this->findReportWithUsersOr404($id);
        $this->requireOwnerOrAdmin($report);

        return $this->view('front/reports/edit', [
            'report'  => $report,
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ], ['saveData' => false]);
    }

    /**
     * POST /report
     */
    public function postCreate()
    {
        $post = $this->request->getPost();

        $rules = [
            'title'            => 'required|min_length[3]',
            'application_name' => 'required|min_length[2]',
            'version'          => 'permit_empty|max_length[50]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $id = $this->reports->insert([
            'user_id'          => $this->userId(),
            'title'            => $post['title'],
            'application_name' => $post['application_name'],
            'version'          => $post['version'] ?? null,
            'author_name'      => $this->currentUserFullName(),
            'status'           => 'brouillon',
        ], true);

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

        $tpl = new ReportTemplateService(new ReportSectionModel(), $this->sectionsService);
        $tpl->buildReportSkeleton((int)$id, $config);

        $now = date('Y-m-d H:i:s');

        $this->reports->update((int)$id, [
            'version_date'      => $now,
            'author_updated_at' => $now,
        ]);

        $history = new \App\Services\ReportHistoryService(new \App\Models\ReportVersionModel());
        $history->add((int)$id, 'draft', $this->userId(), 'Version initiale', $post['version'] ?? null);

        return redirect()->to(site_url('report/' . $id . '/sections'))
            ->with('success', 'Bilan créé avec son squelette. Vous pouvez commencer la rédaction.');
    }

    /**
     * POST /report/{id}/update
     */
    public function postUpdate(int $id)
    {
        $report = $this->findReportWithUsersOr404($id);
        $this->requireOwnerOrAdmin($report);

        $post = $this->request->getPost();

        $rules = [
            'title'            => 'required|min_length[3]',
            'application_name' => 'required|min_length[2]',
            'version'          => 'permit_empty|max_length[50]',
            'status'           => 'permit_empty|in_list[brouillon,en_relecture,final]',
        ];

        if (!$this->validate($rules)) {
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
     * GET /report/{id}/sections
     */
    public function getSections(int $id)
    {
        $report = $this->findReportWithUsersOr404($id);

        $canEdit = ((int)($report['user_id'] ?? 0) === $this->userId()); // front : seulement owner
        $tree    = $this->sectionsService->getTreeForReport($id);

        // (si tu n’en as plus besoin, tu peux enlever)
        $users = (new UserModel())
            ->select('id, firstname, lastname, email')
            ->orderBy('firstname', 'ASC')
            ->findAll();

        return $this->view('front/reports/sections', [
            'report'       => $report,
            'users'        => $users,
            'sectionsTree' => $tree,
            'roots'        => $tree,
            'canEdit'      => $canEdit,
            'errors'       => session('errors') ?? [],
            'success'      => session('success'),
        ], ['saveData' => false]);
    }

    /**
     * POST /report/{id}/sections/root
     */
    public function postSectionsRoot(int $reportId)
    {
        $report = $this->findReportWithUsersOr404($reportId);
        $this->requireOwnerOrAdmin($report);

        $post    = $this->request->getPost();
        $title   = trim($post['title'] ?? '');
        $content = $post['content'] ?? '';

        if ($title === '') {
            return redirect()->back()->withInput()->with('errors', [
                'title_root' => 'Le titre de la partie est obligatoire.',
            ]);
        }

        $this->sectionsService->createRootSection($reportId, [
            'title'   => $title,
            'content' => $content,
        ]);

        $this->sectionsService->recomputeCodes($reportId);

        return redirect()->to(site_url('report/' . $reportId . '/sections'))->with('success', 'Partie ajoutée.');
    }

    /**
     * POST /report/{id}/sections/{parentId}/child
     */
    public function postSectionsChild(int $reportId, int $parentId)
    {
        $report = $this->findReportWithUsersOr404($reportId);
        $this->requireOwnerOrAdmin($report);

        $post    = $this->request->getPost();
        $title   = trim($post['title'] ?? '');
        $content = $post['content'] ?? '';

        if ($title === '') {
            return redirect()->back()->withInput()->with('errors', [
                'title_child_' . $parentId => 'Le titre de la sous-partie est obligatoire.',
            ]);
        }

        $this->findSectionOr404($reportId, $parentId);

        $this->sectionsService->createChildSection($parentId, [
            'title'   => $title,
            'content' => $content,
        ]);

        $this->sectionsService->recomputeCodes($reportId);

        return redirect()->to(site_url('report/' . $reportId . '/sections'))->with('success', 'Sous-partie ajoutée.');
    }

    /**
     * GET /report/{reportId}/sections/{sectionId}/edit
     */
    public function getEditSection(int $reportId, int $sectionId)
    {
        $report = $this->findReportWithUsersOr404($reportId);
        $this->requireOwner($report); // front : uniquement le owner modifie les sections

        $section = $this->findSectionOr404($reportId, $sectionId);

        return $this->view('front/reports/section_edit', [
            'report'  => $report,
            'section' => $section,
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ], ['saveData' => false]);
    }

    /**
     * POST /report/{reportId}/sections/{sectionId}/update
     */
    public function postUpdateSection(int $reportId, int $sectionId)
    {
        $report = $this->findReportWithUsersOr404($reportId);
        $this->requireOwner($report);

        $this->findSectionOr404($reportId, $sectionId);

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

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
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
     * POST /report/{reportId}/sections/{sectionId}/delete
     */
    public function postDeleteSection(int $reportId, int $sectionId)
    {
        $report = $this->findReportWithUsersOr404($reportId);
        $this->requireOwnerOrAdmin($report);

        $this->findSectionOr404($reportId, $sectionId);

        $this->sections->delete($sectionId);
        $this->sectionsService->recomputeCodes($reportId);

        return redirect()->to(site_url('report/' . $reportId . '/sections'))
            ->with('success', 'Section supprimée (ainsi que ses sous-sections).');
    }

    public function postUploadSectionImage()
    {
        $file = $this->request->getFile('image');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['error' => 'Fichier invalide'])->setStatusCode(400);
        }

        $allowed = ['image/png', 'image/jpeg', 'image/webp', 'image/gif'];
        if (!in_array($file->getMimeType(), $allowed, true)) {
            return $this->response->setJSON(['error' => 'Type non autorisé'])->setStatusCode(400);
        }

        if ($file->getSizeByUnit('mb') > 5) {
            return $this->response->setJSON(['error' => 'Trop volumineux (max 5MB)'])->setStatusCode(400);
        }

        $newName = $file->getRandomName();
        $path    = FCPATH . 'uploads/report_sections/';
        if (!is_dir($path)) mkdir($path, 0755, true);

        $file->move($path, $newName);

        return $this->response->setJSON([
            'url' => base_url('uploads/report_sections/' . $newName),
        ]);
    }

    /**
     * POST /report/{id}/delete
     */
    public function postDelete(int $id)
    {
        $report = $this->findReportWithUsersOr404($id);
        $this->requireOwnerOrAdmin($report);

        $this->sections->where('report_id', $id)->delete();
        $this->reports->delete($id);

        return redirect()->to(site_url('report'))->with('success', 'Bilan supprimé.');
    }

    /**
     * POST /report/{id}/sections/meta
     *
     * 🔒 FRONT :
     * - l'auteur peut changer title/application/version/status/author_name
     * - il NE PEUT PAS changer validated_by / validated_at
     */
    public function postUpdateMetaInline(int $reportId)
    {
        $report = $this->findReportWithUsersOr404($reportId);
        $this->requireOwnerOrAdmin($report);

        $data = [
            'title'             => trim((string)$this->request->getPost('title')),
            'application_name'  => trim((string)$this->request->getPost('application_name')),
            'version'           => trim((string)$this->request->getPost('version')),
            'status'            => trim((string)$this->request->getPost('status')) ?: (string)($report['status'] ?? 'brouillon'),
            'author_name'       => trim((string)$this->request->getPost('author_name')) ?: (string)($report['author_name'] ?? ''),
            'doc_status'        => trim((string)$this->request->getPost('doc_status')) ?: (string)($report['doc_status'] ?? 'work'),
            'modification_kind' => trim((string)$this->request->getPost('modification_kind')) ?: (string)($report['modification_kind'] ?? 'creation'),
            'author_updated_at' => date('Y-m-d H:i:s'),
            'file_media_id' => $this->request->getPost('file_media_id') !== null && $this->request->getPost('file_media_id') !== ''
                ? (int)$this->request->getPost('file_media_id')
                : null,
        ];

        $rules = [
            'title'             => 'required|min_length[3]',
            'application_name'  => 'required|min_length[2]',
            'version'           => 'permit_empty|max_length[50]',
            'status'            => 'permit_empty|in_list[brouillon,en_relecture,final]',
            'author_name'       => 'permit_empty|max_length[120]',
            'doc_status'        => 'required|in_list[work,approved,validated]',
            'modification_kind' => 'required|in_list[creation,replace]',
            'file_media_id' => 'permit_empty|is_natural_no_zero',
        ];

        $prevDoc = (string)($report['doc_status'] ?? 'work');
        $newDoc  = (string)$data['doc_status'];

        if ($prevDoc !== 'validated' && $newDoc === 'validated') {
            $data['validated_by'] = $this->userId();
            $data['validated_at'] = date('Y-m-d H:i:s');
        }

        if (! $this->validateData($data, $rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        unset($data['validated_by'], $data['validated_at']);

        $this->reports->update($reportId, $data);

        return redirect()->to(site_url('report/' . $reportId . '/sections'))
            ->with('success', 'Informations du bilan enregistrées.');
    }

    public function postMoveRootUp(int $reportId, int $sectionId)
    {
        $report = $this->findReportWithUsersOr404($reportId);
        $this->requireOwner($report);

        $this->sectionsService->moveRoot($reportId, $sectionId, -1);

        return redirect()->to(site_url('report/' . $reportId . '/sections'))
            ->with('success', 'Ordre mis à jour.');
    }

    public function postMoveRootDown(int $reportId, int $sectionId)
    {
        $report = $this->findReportWithUsersOr404($reportId);
        $this->requireOwner($report);

        $this->sectionsService->moveRoot($reportId, $sectionId, +1);

        return redirect()->to(site_url('report/' . $reportId . '/sections'))
            ->with('success', 'Ordre mis à jour.');
    }

    public function updateMeta(int $reportId)
    {
        $report = $this->mustGetMyReport($reportId); // ta méthode de sécurité

        $docStatus = (string) $this->request->getPost('doc_status');
        $modKind   = (string) $this->request->getPost('modification_kind');

        // Validation simple (tu peux passer en validation service si tu veux)
        $allowedDoc = ['work', 'approved', 'validated'];
        $allowedMod = ['creation', 'replace'];

        if (!in_array($docStatus, $allowedDoc, true) || !in_array($modKind, $allowedMod, true)) {
            $this->error('Valeurs invalides.');
            return $this->redirect()->back();
        }

        $this->reports->update($reportId, [
            'doc_status'         => $docStatus,
            'modification_kind'  => $modKind,
            'updated_at'         => date('Y-m-d H:i:s'), // si pas géré auto
        ]);

        $this->success('Informations mises à jour.');
        return $this->redirect()->back();
    }
}
