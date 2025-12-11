<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ReportModel;
use App\Services\ReportSectionService;
use App\Models\ReportSectionModel;

class Report extends BaseController
{
    protected ReportModel $reports;
    protected ReportSectionService $sectionsService;

    protected ReportSectionModel $sections;

    public function __construct()
    {
        $this->reports         = new ReportModel();
        $this->sectionsService = new ReportSectionService();
        $this->sections        = new ReportSectionModel();
    }

    /**
     * GET /admin/reports
     */
    public function getIndex()
    {
        $data['reports'] = $this->reports
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->view('admin/reports/index', $data, true);
    }

    /**
     * GET /admin/reports/new
     */
    public function getNew()
    {
        $data = [
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ];

        return $this->view('admin/reports/new', $data, true);
    }

    /**
     * POST /admin/reports
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
            'title'            => $post['title'],
            'application_name' => $post['application_name'],
            'version'          => $post['version'] ?? null,
            'author_name'      => $post['author_name'] ?? null,
            'status'           => 'brouillon',
        ], true);

        return redirect()->to(site_url('admin/reports/' . $id . '/sections'))
            ->with('success', 'Bilan créé. Vous pouvez maintenant ajouter les parties et sous-parties.');
    }

    /**
     * GET /admin/reports/{id}/sections
     */
    public function getSections(int $id)
    {
        $report = $this->reports->find($id);

        if (! $report) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Report {$id} not found");
        }

        $sections = $this->sectionsService->getSectionsForReport($id);

        $data = [
            'report'   => $report,
            'sections' => $sections,
            'errors'   => session('errors') ?? [],
            'success'  => session('success'),
        ];

        return $this->view('admin/reports/sections', $data, true);
    }

    /**
     * POST /admin/reports/{id}/sections/root
     */
    public function postSectionsRoot(int $reportId)
    {
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

        return redirect()->to(site_url('admin/reports/' . $reportId . '/sections'))
            ->with('success', 'Partie ajoutée.');
    }

    /**
     * POST /admin/reports/{id}/sections/{parentId}/child
     */
    public function postSectionsChild(int $reportId, int $parentId)
    {
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

        return redirect()->to(site_url('admin/reports/' . $reportId . '/sections'))
            ->with('success', 'Sous-partie ajoutée.');
    }


    /**
     * GET /admin/reports/{reportId}/sections/{sectionId}/edit
     * Formulaire d'édition d'une section (partie ou sous-partie).
     */
    public function getEditSection(int $reportId, int $sectionId)
    {
        $report = $this->reports->find($reportId);
        if (! $report) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Report {$reportId} not found");
        }

        $section = $this->sections->find($sectionId);
        if (! $section || (int) $section['report_id'] !== $reportId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Section {$sectionId} not found");
        }

        $data = [
            'report'  => $report,
            'section' => $section,
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ];

        return $this->view('admin/reports/section_edit', $data, true);
    }

    /**
     * POST /admin/reports/{reportId}/sections/{sectionId}/update
     * Sauvegarde des modifications de la section.
     */
    public function postUpdateSection(int $reportId, int $sectionId)
    {
        $section = $this->sections->find($sectionId);
        if (! $section || (int) $section['report_id'] !== $reportId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Section {$sectionId} not found");
        }

        $post = $this->request->getPost();

        $rules = [
            'title'            => 'required|min_length[2]',
            'content'          => 'permit_empty',
            'period_label'     => 'permit_empty|max_length[100]',
            'period_number'    => 'permit_empty|integer',
            'debit_value'      => 'permit_empty|decimal',
            'start_date'       => 'permit_empty|valid_date[Y-m-d]',
            'end_date'         => 'permit_empty|valid_date[Y-m-d]',
            'compliance_status'=> 'permit_empty|in_list[conforme,non_conforme,partiel,non_applicable]',
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

        return redirect()->to(site_url('admin/reports/' . $reportId . '/sections'))
            ->with('success', 'Section mise à jour.');
    }

    /**
     * POST /admin/reports/{reportId}/sections/{sectionId}/delete
     * Supprime une section (et ses enfants, et ses tableaux via FK CASCADE).
     */
    public function postDeleteSection(int $reportId, int $sectionId)
    {
        $section = $this->sections->find($sectionId);
        if (! $section || (int) $section['report_id'] !== $reportId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Section {$sectionId} not found");
        }

        // Grâce aux FK en CASCADE, les enfants et tables liées seront supprimés aussi
        $this->sections->delete($sectionId);

        return redirect()->to(site_url('admin/reports/' . $reportId . '/sections'))
            ->with('success', 'Section supprimée (ainsi que ses sous-sections).');
    }
}
