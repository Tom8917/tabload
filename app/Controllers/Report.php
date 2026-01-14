<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ReportModel;
use App\Models\ReportSectionModel;
use App\Services\ReportSectionService;
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

    /**
     * Petit helper : récupère l’id user connecté (à adapter à ton auth)
     */
    private function userId(): int
    {
        // Exemple courant : session('user')->id
        $user = session('user');
        return (int) ($user->id ?? 0);
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
        $data['reports'] = $this->reports
            ->where('user_id', $this->userId())
            ->orderBy('created_at', 'DESC')
            ->findAll();

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

    /**
     * POST /reports
     */
    public function postCreate()
    {
        $post = $this->request->getPost();

        $rules = [
            'title'            => 'required|min_length[3]',
            'application_name' => 'required|min_length[2]',
            'version'          => 'permit_empty|max_length[50]',
            'author_name'      => 'permit_empty|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $id = $this->reports->insert([
            'user_id'          => $this->userId(),
            'title'            => $post['title'],
            'application_name' => $post['application_name'],
            'version'          => $post['version'] ?? null,
            'author_name'      => $post['author_name'] ?? null,
            'status'           => 'brouillon',
        ], true);

        return redirect()->to(site_url('reports/' . $id . '/sections'))
            ->with('success', 'Bilan créé. Vous pouvez maintenant ajouter les parties et sous-parties.');
    }

    /**
     * GET /reports/{id}/sections
     */
    public function getSections(int $id)
    {
        $report   = $this->findOwnedReportOr404($id);
        $sections = $this->sectionsService->getSectionsForReport($id);

        $data = [
            'report'   => $report,
            'sections' => $sections,
            'errors'   => session('errors') ?? [],
            'success'  => session('success'),
        ];

        return $this->view('front/reports/sections', $data, false);
    }

    /**
     * POST /reports/{id}/sections/root
     */
    public function postSectionsRoot(int $reportId)
    {
        $this->findOwnedReportOr404($reportId);

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

        return redirect()->to(site_url('reports/' . $reportId . '/sections'))
            ->with('success', 'Partie ajoutée.');
    }

    /**
     * POST /reports/{id}/sections/{parentId}/child
     */
    public function postSectionsChild(int $reportId, int $parentId)
    {
        $this->findOwnedReportOr404($reportId);

        $post = $this->request->getPost();
        $title = trim($post['title'] ?? '');
        $content = $post['content'] ?? '';

        if ($title === '') {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['title_child_' . $parentId => 'Le titre de la sous-partie est obligatoire.']);
        }

        // Optionnel mais conseillé : vérifier que $parentId appartient bien à ce report
        $parent = $this->sections->find($parentId);
        if (! $parent || (int) $parent['report_id'] !== $reportId) {
            throw PageNotFoundException::forPageNotFound("Parent section {$parentId} not found");
        }

        $this->sectionsService->createChildSection($parentId, [
            'title'   => $title,
            'content' => $content,
        ]);

        return redirect()->to(site_url('reports/' . $reportId . '/sections'))
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

        return redirect()->to(site_url('reports/' . $reportId . '/sections'))
            ->with('success', 'Section mise à jour.');
    }

    /**
     * POST /reports/{reportId}/sections/{sectionId}/delete
     */
    public function postDeleteSection(int $reportId, int $sectionId)
    {
        $this->findOwnedReportOr404($reportId);
        $this->findOwnedSectionOr404($reportId, $sectionId);

        $this->sections->delete($sectionId);

        return redirect()->to(site_url('reports/' . $reportId . '/sections'))
            ->with('success', 'Section supprimée (ainsi que ses sous-sections).');
    }
}
