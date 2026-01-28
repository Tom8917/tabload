<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ReportModel;
use App\Models\ReportSectionModel;
use App\Services\ReportSectionService;
use App\Services\ReportTemplateService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\ResponseInterface;

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

        $this->title = 'Bilans';
        $this->menu  = 'reports';
    }

    private function userId(): int
    {
        $user = session('user');
        return (int)($user->id ?? 0);
    }

    private function isAdmin(): bool
    {
        $user = session('user');
        return (int)($user->id_permission ?? 0) === 1;
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

    private function findSectionOr404(int $reportId, int $sectionId): array
    {
        $section = $this->sections->find($sectionId);
        if (!$section || (int)($section['report_id'] ?? 0) !== $reportId) {
            throw PageNotFoundException::forPageNotFound("Section {$sectionId} not found");
        }
        return $section;
    }

    /**
     * (Optionnel) Si un jour tu autorises un accès admin à des non-admin,
     * tu peux garder cette sécurité.
     * Avec ton group filter adminOnly, ça ne sert normalement pas.
     */
    private function requireOwnerOrAdmin(array $report): void
    {
        $ownerId = (int)($report['user_id'] ?? 0);
        if ($ownerId !== $this->userId() && !$this->isAdmin()) {
            throw HTTPException::forbidden('Accès refusé');
        }
    }


    public function getIndex()
    {
        return $this->view('admin/reports/index', [
            'reports' => $this->reports->orderBy('created_at', 'DESC')->findAll(),
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ], true);
    }

    public function getNew()
    {
        return $this->view('admin/reports/new', [
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ], true);
    }

    public function postCreate()
    {
        $post = $this->request->getPost();

        $rules = [
            'title'            => 'required|min_length[3]',
            'application_name' => 'required|min_length[2]',
            'application_version' => 'permit_empty|max_length[50]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $authorName = trim((string)($post['author_name'] ?? ''));
        if ($authorName === '') $authorName = $this->currentUserFullName();

        $id = $this->reports->insert([
            'user_id'              => $this->userId(),
            'title'                => $post['title'],
            'application_name'     => $post['application_name'],
            'application_version'  => $post['application_version'] ?? null,
            'author_name'          => $authorName,
            'status'               => 'brouillon',
            'doc_status'           => 'work',
            'doc_version'          => 'v0.1',
            'modification_kind'    => 'creation',
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

//        $history = new \App\Services\ReportHistoryService(new \App\Models\ReportVersionModel());
//        $history->add((int)$id, 'draft', $this->userId(), 'Version initiale', $post['version'] ?? null);

        $rv = new \App\Models\ReportVersionModel();
        $rv->insert([
            'report_id'     => (int)$id,
            'version_label' => 'v0.1',
            'change_type'   => 'draft',
            'doc_status'    => 'work',
            'changed_by'    => $this->userId(),
            'comment'       => 'Version initiale',
        ]);

        return redirect()->to(site_url('admin/reports/' . $id . '/sections'))
            ->with('success', 'Bilan créé avec son squelette. Vous pouvez commencer la rédaction.');
    }

    public function getShow(int $id)
    {
        $report       = $this->findReportWithUsersOr404($id);
        $sectionsTree = $this->sectionsService->getTreeForReport($id);

        $admins = model(\App\Models\UserModel::class)
            ->select('id, firstname, lastname, email')
            ->where('id_permission', 1)
            ->orderBy('lastname', 'ASC')
            ->findAll();

        $versions = model(\App\Models\ReportVersionModel::class)
            ->where('report_id', $id)
            ->orderBy('id', 'ASC')
            ->findAll();

        return $this->view('admin/reports/show', [
            'report'       => $report,
            'sectionsTree' => $sectionsTree,
            'admins'       => $admins,
            'versions'     => $versions,
            'errors'       => session('errors') ?? [],
            'success'      => session('success'),
        ], true);
    }

    public function getEdit(int $id)
    {
        $report = $this->findReportWithUsersOr404($id);

        return $this->view('admin/reports/edit', [
            'report'  => $report,
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ], true);
    }

    public function postUpdate(int $id)
    {
        $report = $this->findReportWithUsersOr404($id);
        $post   = $this->request->getPost();

        $rules = [
            'title'               => 'required|min_length[3]',
            'application_name'    => 'required|min_length[2]',
            'application_version' => 'permit_empty|max_length[50]',
            'doc_version'         => 'permit_empty|max_length[20]',
            'status'              => 'permit_empty|in_list[brouillon,en relecture,final]',
            'doc_status'          => 'permit_empty|in_list[work,approved,validated]',
            'file_media_id'       => 'permit_empty|is_natural_no_zero',
            'modification_kind'   => 'permit_empty|in_list[creation,replace]',
            'author_name'         => 'permit_empty|max_length[120]',
            'version_date'        => 'permit_empty',
            'comments'            => 'permit_empty|max_length[5000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $now = date('Y-m-d H:i:s');

        $update = [
            'title'               => $post['title'],
            'application_name'    => $post['application_name'],
            'application_version' => $post['application_version'] ?? null,
            'doc_version'         => $post['doc_version'] ?? ($report['doc_version'] ?? 'v0.1'),
            'status'              => $post['status'] ?? ($report['status'] ?? 'brouillon'),
            'doc_status'          => $post['doc_status'] ?? ($report['doc_status'] ?? 'work'),
            'author_name'         => isset($post['author_name']) ? trim((string)$post['author_name']) : ($report['author_name'] ?? null),
            'modification_kind'   => isset($post['modification_kind']) ? (string)$post['modification_kind'] : ($report['modification_kind'] ?? null),
            'version_date'        => !empty($post['version_date']) ? $post['version_date'] : ($report['version_date'] ?? null),
            'comments'            => isset($post['comments']) ? trim((string)$post['comments']) : ($report['comments'] ?? null),
            'corrected_by'        => $this->userId(),
            'corrected_at'        => $now,
        ];

        if (array_key_exists('file_media_id', $post) && $post['file_media_id'] !== '') {
            $update['file_media_id'] = (int)$post['file_media_id'];
        }

        $this->reports->update($id, $update);

//        $history = new \App\Services\ReportHistoryService(new \App\Models\ReportVersionModel());
//        $history->add(
//            $id,
//            'correction',
//            $this->userId(),
//            'Correction admin',
//            $post['version'] ?? ($report['version'] ?? null)
//        );

        return redirect()->to(site_url('admin/reports/' . $id . '/sections'))
            ->with('success', 'Bilan mis à jour.');
    }

    public function postUpdateComments(int $id)
    {
        $report = $this->findReportWithUsersOr404($id);

        $rules = [
            'comments' => 'permit_empty|max_length[5000]',
        ];

        $data = [
            'comments' => trim((string)$this->request->getPost('comments')),
        ];

        if (! $this->validateData($data, $rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $now = date('Y-m-d H:i:s');

        $this->reports->update($id, [
            'comments'     => $data['comments'] !== '' ? $data['comments'] : null,
            'corrected_by' => $this->userId(),
            'corrected_at' => $now,
        ]);

//        $history = new \App\Services\ReportHistoryService(new \App\Models\ReportVersionModel());
//        $history->add($id, 'comment', $this->userId(), 'Commentaire admin', $report['version'] ?? null);

        return redirect()->back()->with('success', 'Commentaire enregistré.');
    }

    public function postDelete(int $id)
    {
        $this->findReportWithUsersOr404($id);

        $this->sections->where('report_id', $id)->delete();
        $this->reports->delete($id);

        return redirect()->to(site_url('admin/reports'))
            ->with('success', 'Bilan supprimé.');
    }


    public function getSections(int $id)
    {
        $report = $this->findReportWithUsersOr404($id);
        $tree   = $this->sectionsService->getTreeForReport($id);

        $admins = model(\App\Models\UserModel::class)
            ->select('id, firstname, lastname, email')
            ->where('id_permission', 1)
            ->orderBy('lastname', 'ASC')
            ->findAll();

        return $this->view('admin/reports/sections', [
            'report'       => $report,
            'sectionsTree' => $tree,
            'roots'        => $tree,
            'admins'       => $admins,
            'errors'       => session('errors') ?? [],
            'success'      => session('success'),
        ], true);
    }

    public function postSectionsRoot(int $reportId)
    {
        $this->findReportWithUsersOr404($reportId);

        $post    = $this->request->getPost();
        $title   = trim((string)($post['title'] ?? ''));
        $content = (string)($post['content'] ?? '');

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

        return redirect()->to(site_url('admin/reports/' . $reportId . '/sections'))
            ->with('success', 'Partie ajoutée.');
    }

    public function postSectionsChild(int $reportId, int $parentId)
    {
        $this->findReportWithUsersOr404($reportId);
        $this->findSectionOr404($reportId, $parentId);

        $post    = $this->request->getPost();
        $title   = trim((string)($post['title'] ?? ''));
        $content = (string)($post['content'] ?? '');

        if ($title === '') {
            return redirect()->back()->withInput()->with('errors', [
                'title_child_' . $parentId => 'Le titre de la sous-partie est obligatoire.',
            ]);
        }

        $this->sectionsService->createChildSection($parentId, [
            'title'   => $title,
            'content' => $content,
        ]);

        $this->sectionsService->recomputeCodes($reportId);

        return redirect()->to(site_url('admin/reports/' . $reportId . '/sections'))
            ->with('success', 'Sous-partie ajoutée.');
    }

    public function getEditSection(int $reportId, int $sectionId)
    {
        $report  = $this->findReportWithUsersOr404($reportId);
        $section = $this->findSectionOr404($reportId, $sectionId);

        return $this->view('admin/reports/section_edit', [
            'report'  => $report,
            'section' => $section,
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ], true);
    }

    public function postUpdateSection(int $reportId, int $sectionId)
    {
        $report = $this->findReportWithUsersOr404($reportId);
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
            'corrected_by' => $this->userId(),
            'corrected_at' => date('Y-m-d H:i:s'),
        ]);

//        $history = new \App\Services\ReportHistoryService(new \App\Models\ReportVersionModel());
//        $history->add($reportId, 'correction', $this->userId(), 'Modification section (correcteur)', $report['version'] ?? null);

        return redirect()->to(site_url('admin/reports/' . $reportId . '/sections'))
            ->with('success', 'Section mise à jour.');
    }

    public function postDeleteSection(int $reportId, int $sectionId)
    {
        $this->findReportWithUsersOr404($reportId);
        $this->findSectionOr404($reportId, $sectionId);

        $this->sectionsService->deleteSectionWithChildren($reportId, $sectionId);
        $this->sectionsService->recomputeCodes($reportId);

        return redirect()->to(site_url('admin/reports/' . $reportId . '/sections'))
            ->with('success', 'Section supprimée (ainsi que ses sous-sections).');
    }

    public function postMoveRootUp(int $reportId, int $sectionId)
    {
        $this->findReportWithUsersOr404($reportId);

        $this->sectionsService->moveRoot($reportId, $sectionId, -1);

        return redirect()->to(site_url('admin/reports/' . $reportId . '/sections'))
            ->with('success', 'Ordre mis à jour.');
    }

    public function postMoveRootDown(int $reportId, int $sectionId)
    {
        $this->findReportWithUsersOr404($reportId);

        $this->sectionsService->moveRoot($reportId, $sectionId, +1);

        return redirect()->to(site_url('admin/reports/' . $reportId . '/sections'))
            ->with('success', 'Ordre mis à jour.');
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

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $file->move($path, $newName);

        return $this->response->setJSON([
            'url' => base_url('uploads/report_sections/' . $newName),
        ]);
    }


    public function postMarkInReview(int $id)
    {
        $report = $this->findReportWithUsersOr404($id);

        $this->reports->update($id, [
            'status'       => 'en relecture',
            'doc_status'       => 'work',
            'corrected_by' => $this->userId(),
            'corrected_at' => date('Y-m-d H:i:s'),
        ]);

//        $history = new \App\Services\ReportHistoryService(new \App\Models\ReportVersionModel());
//        $history->add($id, 'in_review', $this->userId(), 'Passage en relecture', $report['version'] ?? null);

        return redirect()->to(site_url('admin/reports/' . $id . '/sections'))
            ->with('success', 'Bilan passé en relecture.');
    }

    public function postAssignValidator(int $id)
    {
        $this->findReportWithUsersOr404($id);

        $validatorId = (int)$this->request->getPost('validated_by');
        if ($validatorId <= 0) {
            return redirect()->back()->with('errors', ['validated_by' => 'Sélectionnez un validateur.']);
        }

        $exists = model(\App\Models\UserModel::class)
            ->where('id', $validatorId)
            ->where('id_permission', 1)
            ->countAllResults();

        if ($exists === 0) {
            return redirect()->back()->with('errors', ['validated_by' => 'Validateur invalide.']);
        }

        $this->reports->update($id, [
            'validated_by' => $validatorId,
        ]);

        return redirect()->to(site_url('admin/reports/' . $id . '/sections'))
            ->with('success', 'Validateur désigné.');
    }

    public function postValidate(int $id)
    {
        $report = $this->findReportWithUsersOr404($id);

        $now = date('Y-m-d H:i:s');
        $validatorId = (int)($report['validated_by_id'] ?? 0);
        if ($validatorId <= 0) $validatorId = $this->userId();

        $this->reports->update($id, [
            'status'       => 'final',
            'doc_status'   => 'validated',
            'doc_version'  => 'v1.0',
            'validated_by' => $validatorId,
            'validated_at' => $now,
        ]);

//        $history = new \App\Services\ReportHistoryService(new \App\Models\ReportVersionModel());
//        $history->add($id, 'validation', $this->userId(), 'Document validé', $report['version'] ?? null);

        $rv = new \App\Models\ReportVersionModel();

        $exists = $rv->where('report_id', $id)
            ->where('change_type', 'validation')
            ->where('version_label', 'v1.0')
            ->first();

        if (!$exists) {
            $rv->insert([
                'report_id'     => $id,
                'version_label' => 'v1.0',
                'change_type'   => 'validation',
                'doc_status'    => 'validated',
                'changed_by'    => $this->userId(),
                'comment'       => 'Version validée',
            ]);
        }

        return redirect()->to(site_url('admin/reports/' . $id . '/sections'))
            ->with('success', 'Bilan validé.');
    }
}
