<?php

namespace App\Controllers\Admin;

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

    protected $require_auth = true;

    public function __construct()
    {
        $this->reports         = new ReportModel();
        $this->sectionsService = new ReportSectionService();
        $this->sections        = new ReportSectionModel();

        // Optionnel : si ton BaseController gère ça
        $this->title = 'Bilans';
        $this->menu  = 'reports';
    }

    private function currentUserFullName(): string
    {
        $user = session('user');

        $first = trim((string)($user->firstname ?? ''));
        $last  = trim((string)($user->lastname ?? ''));

        $full = trim($first . ' ' . $last);

        if ($full === '') {
            $full = trim((string)($user->name ?? ''));
        }
        if ($full === '') {
            $full = 'Utilisateur';
        }

        return $full;
    }

    private function userId(): int
    {
        $user = session()->get('user');
        return (int)($user->id ?? 0);
    }

    private function findReportOr404(int $reportId): array
    {
        $report = $this->reports->find($reportId);
        if (!$report) {
            throw PageNotFoundException::forPageNotFound("Report {$reportId} not found");
        }
        return $report;
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
     * GET admin/reports
     */
    public function getIndex()
    {
        return $this->view('admin/reports/index', [
            'reports' => $this->reports->orderBy('created_at', 'DESC')->findAll(),
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ], true);
    }

    /**
     * GET admin/reports/new
     */
    public function getNew()
    {
        return $this->view('admin/reports/new', [
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ], true);
    }

    /**
     * POST admin/reports
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
            'author_name'      => $post['author_name'] ?? null,
            'status'           => 'brouillon',
        ], true);


        // Template skeleton (même logique que front)
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

        return redirect()->to(site_url('admin/reports/' . $id . '/sections'))
            ->with('success', 'Bilan créé avec son squelette. Vous pouvez commencer la rédaction.');
    }

    /**
     * GET admin/reports/{id}
     */
    public function getShow(int $id)
    {
        $report = $this->findReportOr404($id);

        $sectionsTree = $this->sectionsService->getTreeForReport($id);

        return $this->view('admin/reports/show', [
            'report'       => $report,
            'sectionsTree' => $sectionsTree,
            'canEdit'      => true,
            'errors'       => session('errors') ?? [],
            'success'      => session('success'),
        ], true);
    }

    /**
     * GET admin/reports/{id}/edit
     */
    public function getEdit(int $id)
    {
        $report = $this->findReportOr404($id);

        return $this->view('admin/reports/edit', [
            'report'  => $report,
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ], true);
    }

    /**
     * POST admin/reports/{id}/update
     */
    public function postUpdate(int $id)
    {
        $report = $this->findReportOr404($id);
        $post   = $this->request->getPost();

        $rules = [
            'title'            => 'required|min_length[3]',
            'application_name' => 'required|min_length[2]',
            'version'          => 'permit_empty|max_length[50]',
            'status'           => 'permit_empty|in_list[brouillon,en_relecture,final]',
            'doc_status'       => 'permit_empty|in_list[work,approved,validated]',
            'version_date'     => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $now = date('Y-m-d H:i:s');

        $newDocStatus = $post['doc_status'] ?? ($report['doc_status'] ?? 'work');
        $oldDocStatus = $report['doc_status'] ?? 'work';

        $update = [
            'title'            => $post['title'],
            'application_name' => $post['application_name'],
            'version'          => $post['version'] ?? null,
            'status'           => $post['status'] ?? ($report['status'] ?? 'brouillon'),
            'doc_status'       => $newDocStatus,

            // optionnel : si tu veux permettre à l’admin de changer la date affichée dans le tableau d’intro
            'version_date'     => !empty($post['version_date']) ? $post['version_date'] : ($report['version_date'] ?? null),

            // ✅ correcteur
            'corrected_by'     => $this->userId(),
            'corrected_at'     => $now,
        ];

        // ✅ Validation si approuvé/validé
        $didValidate = false;
        if (in_array($newDocStatus, ['approved', 'validated'], true)) {
            // Si on veut “dater” seulement si on passe dans un de ces statuts (et pas à chaque edit)
            if (!in_array($oldDocStatus, ['approved', 'validated'], true) || empty($report['validated_at'])) {
                $update['validated_by'] = $this->userId();
                $update['validated_at'] = $now;
                $didValidate = true;
            }
        }

        $this->reports->update($id, $update);

        // Historique
        $history = new \App\Services\ReportHistoryService(new \App\Models\ReportVersionModel());

        if ($didValidate) {
            $history->add($id, $newDocStatus === 'validated' ? 'validation' : 'approval', $this->userId(),
                $newDocStatus === 'validated' ? 'Document validé' : 'Document approuvé',
                $post['version'] ?? ($report['version'] ?? null)
            );
        } else {
            $history->add($id, 'correction', $this->userId(), 'Correction admin', $post['version'] ?? ($report['version'] ?? null));
        }

        return redirect()->to(site_url('admin/reports/' . $id))
            ->with('success', 'Bilan mis à jour (auteur conservé).');
    }

    /**
     * POST admin/reports/{id}/delete
     */
    public function postDelete(int $id)
    {
        $this->findReportOr404($id);

        $this->sections->where('report_id', $id)->delete();
        $this->reports->delete($id);

        return redirect()->to(site_url('admin/reports'))
            ->with('success', 'Bilan supprimé.');
    }

    /**
     * GET admin/reports/{id}/sections
     */
    public function getSections(int $id)
    {
        $report = $this->findReportOr404($id);
        $canEdit = $this->request->getGet('edit') === '1';

        $tree  = $this->sectionsService->getTreeForReport($id);
        $roots = $tree;

        return $this->view('admin/reports/sections', [
            'report'       => $report,
            'sectionsTree' => $tree,
            'roots'        => $roots,
            'canEdit'      => true,
            'errors'       => session('errors') ?? [],
            'success'      => session('success'),
        ], true);
    }

    /**
     * POST admin/reports/{id}/sections/root
     */
    public function postSectionsRoot(int $reportId)
    {
        $this->findReportOr404($reportId);

        $post    = $this->request->getPost();
        $title   = trim($post['title'] ?? '');
        $content = $post['content'] ?? '';

        if ($title === '') {
            return redirect()->back()->withInput()->with('errors', [
                'title_root' => 'Le titre de la partie est obligatoire.'
            ]);
        }

        $this->sectionsService->createRootSection($reportId, [
            'title'   => $title,
            'content' => $content,
        ]);

        // si tu veux des codes toujours nickel même après ajout root :
        $this->sectionsService->recomputeCodes($reportId);

        return redirect()->to(site_url('admin/reports/' . $reportId . '/sections'))
            ->with('success', 'Partie ajoutée.');
    }

    /**
     * POST admin/reports/{id}/sections/{parentId}/child
     */
    public function postSectionsChild(int $reportId, int $parentId)
    {
        $this->findReportOr404($reportId);

        $post    = $this->request->getPost();
        $title   = trim($post['title'] ?? '');
        $content = $post['content'] ?? '';

        if ($title === '') {
            return redirect()->back()->withInput()->with('errors', [
                'title_child_' . $parentId => 'Le titre de la sous-partie est obligatoire.'
            ]);
        }

        // Vérifie parent appartient bien à ce report
        $this->findSectionOr404($reportId, $parentId);

        $this->sectionsService->createChildSection($parentId, [
            'title'   => $title,
            'content' => $content,
        ]);

        $this->sectionsService->recomputeCodes($reportId);

        return redirect()->to(site_url('admin/reports/' . $reportId . '/sections'))
            ->with('success', 'Sous-partie ajoutée.');
    }

    /**
     * GET admin/reports/{reportId}/sections/{sectionId}/edit
     */
    public function getEditSection(int $reportId, int $sectionId)
    {
        $report  = $this->findReportOr404($reportId);
        $section = $this->findSectionOr404($reportId, $sectionId);
        $canEdit = $this->request->getGet('edit') === '1';

        return $this->view('admin/reports/section_edit', [
            'report'  => $report,
            'section' => $section,
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ], true);
    }

    /**
     * POST admin/reports/{reportId}/sections/{sectionId}/update
     */
    public function postUpdateSection(int $reportId, int $sectionId)
    {
        $this->findReportOr404($reportId);
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

        $history = new \App\Services\ReportHistoryService(new \App\Models\ReportVersionModel());
        $history->add($reportId, 'correction', $this->userId(), 'Modification section (correcteur)', $report['version'] ?? null);

        return redirect()->to(site_url('admin/reports/' . $reportId . '/sections'))
            ->with('success', 'Section mise à jour.');
    }

    /**
     * POST admin/reports/{reportId}/sections/{sectionId}/delete
     */
    public function postDeleteSection(int $reportId, int $sectionId)
    {
        $this->findReportOr404($reportId);
        $this->findSectionOr404($reportId, $sectionId);

        $this->sections->delete($sectionId);
        $this->sectionsService->recomputeCodes($reportId);

        return redirect()->to(site_url('admin/reports/' . $reportId . '/sections'))
            ->with('success', 'Section supprimée (ainsi que ses sous-sections).');
    }

    /**
     * POST admin/reports/sections/upload-image
     */
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
}
