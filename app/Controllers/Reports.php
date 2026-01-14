<?php

namespace App\Controllers;

use App\Models\ReportModel;
use App\Models\ReportSectionModel;
use App\Services\ReportSectionService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Reports extends BaseController
{
    protected ReportModel $reports;
    protected ReportSectionService $sectionsService;
    protected ReportSectionModel $sections;

    public function __construct()
    {
        $this->reports         = new ReportModel();
        $this->sectionsService = new ReportSectionService();
        $this->sections        = new ReportSectionModel();

        // accessible à tout user connecté (BaseController le gère)
        $this->title = 'Mes bilans';
        $this->menu  = 'reports';
    }

    private function currentUserId(): int
    {
        return (int) (session()->get('user')->id ?? 0);
    }

    private function mustGetMyReport(int $reportId): array
    {
        $report = $this->reports->findOneForUser($reportId, $this->currentUserId());
        if (!$report) {
            // 404 = meilleur pour ne rien “leaker”
            throw PageNotFoundException::forPageNotFound("Report {$reportId} not found");
        }
        return $report;
    }

    // GET /bilans
    public function getIndex()
    {
        $data = [
            'reports' => $this->reports->findAllForUser($this->currentUserId()),
            'success' => session('success'),
        ];

        return $this->view('/front/reports/index', $data, false);
    }

    // GET /bilans/new
    public function getNew()
    {
        $data = [
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ];

        return $this->view('/front/reports/new', $data, false);
    }

    // POST /bilans
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
            'user_id'          => $this->currentUserId(),
            'title'            => $post['title'],
            'application_name' => $post['application_name'],
            'version'          => $post['version'] ?? null,
            'author_name'      => $post['author_name'] ?? null,
            'status'           => 'brouillon',
        ], true);

        return redirect()->to(site_url('reports/' . $id . '/sections'))
            ->with('success', 'Bilan créé. Vous pouvez maintenant ajouter les parties et sous-parties.');
    }

    // GET /bilans/{id}/sections
    public function getSections(int $id)
    {
        $report   = $this->mustGetMyReport($id);
        $sections = $this->sectionsService->getSectionsForReport($id);

        $data = [
            'report'   => $report,
            'sections' => $sections,
            'errors'   => session('errors') ?? [],
            'success'  => session('success'),
        ];

        return $this->view('/front/reports/sections', $data, false);
    }

    // POST /bilans/{id}/sections/root
    public function postSectionsRoot(int $reportId)
    {
        $this->mustGetMyReport($reportId);

        $post = $this->request->getPost();
        $title   = trim($post['title'] ?? '');
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

    // POST /bilans/{id}/sections/{parentId}/child
    public function postSectionsChild(int $reportId, int $parentId)
    {
        $this->mustGetMyReport($reportId);

        // sécurité : le parent doit appartenir à CE report
        $parent = $this->sections->find($parentId);
        if (! $parent || (int) $parent['report_id'] !== $reportId) {
            throw PageNotFoundException::forPageNotFound("Section {$parentId} not found");
        }

        $post = $this->request->getPost();
        $title   = trim($post['title'] ?? '');
        $content = $post['content'] ?? '';

        if ($title === '') {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['title_child_' . $parentId => 'Le titre de la sous-partie est obligatoire.']);
        }

        $this->sectionsService->createChildSection($parentId, [
            'title'   => $title,
            'content' => $content,
        ]);

        return redirect()->to(site_url('reports/' . $reportId . '/sections'))
            ->with('success', 'Sous-partie ajoutée.');
    }

    // GET /bilans/{reportId}/sections/{sectionId}/edit
    public function getEditSection(int $reportId, int $sectionId)
    {
        $report = $this->mustGetMyReport($reportId);

        $section = $this->sections->find($sectionId);
        if (! $section || (int) $section['report_id'] !== $reportId) {
            throw PageNotFoundException::forPageNotFound("Section {$sectionId} not found");
        }

        return $this->view('/front/reports/section_edit', [
            'report'  => $report,
            'section' => $section,
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ], false);
    }

    // POST /bilans/{reportId}/sections/{sectionId}/update
    public function postUpdateSection(int $reportId, int $sectionId)
    {
        $this->mustGetMyReport($reportId);

        $section = $this->sections->find($sectionId);
        if (! $section || (int) $section['report_id'] !== $reportId) {
            throw PageNotFoundException::forPageNotFound("Section {$sectionId} not found");
        }

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

    // POST /bilans/{reportId}/sections/{sectionId}/delete
    public function postDeleteSection(int $reportId, int $sectionId)
    {
        $this->mustGetMyReport($reportId);

        $section = $this->sections->find($sectionId);
        if (! $section || (int) $section['report_id'] !== $reportId) {
            throw PageNotFoundException::forPageNotFound("Section {$sectionId} not found");
        }

        $this->sections->delete($sectionId);

        return redirect()->to(site_url('reports/' . $reportId . '/sections'))
            ->with('success', 'Section supprimée.');
    }
}
