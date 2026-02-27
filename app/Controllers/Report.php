<?php

namespace App\Controllers;
use CodeIgniter\HTTP\ResponseInterface;

use App\Controllers\BaseController;
use App\Models\ReportModel;
use App\Models\ReportSectionModel;
use App\Models\UserModel;
use App\Models\MediaFolderModel;
use App\Services\ReportSectionService;
use App\Services\ReportTemplateService;
use CodeIgniter\Exceptions\PageNotFoundException;

use App\Models\LogModel;
use App\Services\LogService;
use Dompdf\Dompdf;
use Dompdf\Options;

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


//logs:
    private function log(string $action, string $entityType, ?int $entityId, ?string $message = null, array $meta = []): void
    {
        $svc = new LogService(model(LogModel::class));
        $svc->add(
            $this->userId() ?: null,
            $action,
            $entityType,
            $entityId,
            $message,
            $meta
        );
    }

    private function normalizeValue(mixed $v): mixed
    {
        if ($v === null) return null;

        if (is_string($v)) {
            $vv = trim($v);
            return $vv === '' ? '' : $vv;
        }

        if (is_numeric($v)) {
            $s = (string) $v;
            return str_contains($s, '.') ? (float) $v : (int) $v;
        }

        return $v;
    }

    private function buildDiff(array $before, array $after): array
    {
        $changes = [];

        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));

        foreach ($keys as $k) {
            $bRaw = $before[$k] ?? null;
            $aRaw = $after[$k] ?? null;

            $b = $this->normalizeValue($bRaw);
            $a = $this->normalizeValue($aRaw);

            if ($b === $a) {
                continue;
            }

            $changes[$k] = [
                'from' => $bRaw,
                'to'   => $aRaw,
            ];
        }

        return $changes;
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
            m.name      AS file_name,
            m.mime_type AS file_mime_type,
            m.file_size AS file_size
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
            $this->error('Accès refusé.');
            redirect()->back()->send();
            exit;
        }
    }

    private function requireOwner(array $report): void
    {
        $ownerId = (int)($report['user_id'] ?? 0);

        if ($ownerId !== $this->userId()) {
            $this->error('Accès refusé.');
            redirect()->back()->send();
            exit;
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

    public function getIndex()
    {
        $uid = $this->userId();

        return $this->view('front/reports/index', [
            'myReports' => $this->reports->where('user_id', $uid)->orderBy('created_at', 'DESC')->findAll(),
            'otherReports' => $this->reports->where('user_id !=', $uid)->orderBy('created_at', 'DESC')->findAll(),
        ], ['saveData' => false]);
    }

    public function getNew()
    {
        return $this->view('front/reports/new', [
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ], ['saveData' => false]);
    }

    public function getShow(int $id)
    {
        $report = $this->findReportWithUsersOr404($id);

        $canEdit = ((int)($report['user_id'] ?? 0) === $this->userId()) || $this->isAdmin();

        $sectionsTree = $this->sectionsService->getTreeForReport($id);

        $versions = model(\App\Models\ReportVersionModel::class)
            ->where('report_id', $id)
            ->orderBy('id', 'ASC')
            ->findAll();

        return $this->view('front/reports/show', [
            'report'       => $report,
            'sectionsTree' => $sectionsTree,
            'canEdit'      => $canEdit,
            'versions'     => $versions,
        ], ['saveData' => false]);
    }

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

    public function postCreate()
    {
        $post = $this->request->getPost();

        $rules = [
            'title'               => 'required|min_length[3]',
            'application_name'    => 'required|min_length[2]',
            'application_version' => 'permit_empty|max_length[50]',
            'file_media_id'       => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $reportId = (int) $this->reports->insert([
                'user_id'              => $this->userId(),
                'title'                => $post['title'],
                'application_name'     => $post['application_name'],
                'application_version'  => $post['application_version'] ?? null,
                'file_media_id'        => $post['file_media_id'] ?? null,
                'author_name'          => $this->currentUserFullName(),
                'status'               => 'brouillon',
                'doc_status'           => 'work',
                'doc_version'          => 'v0.1',
                'modification_kind'    => $post['modification_kind'],
            ], true);

            $folderModel = new MediaFolderModel();

            $folderName = trim((string)($post['application_name'] ?? ''));
            if ($folderName === '') $folderName = 'Application';

            $folderId = (int) $folderModel->insert([
                'name'       => $folderName,
                'parent_id'  => null,
                'user_id'    => $this->userId(),
                'sort_order' => 0,
            ], true);

            $this->reports->update($reportId, [
                'media_folder_id' => $folderId,
            ]);

            $this->log(
                'create',
                'report',
                $reportId,
                'Création du bilan',
                [
                    'changes' => [
                        'title' => ['from' => null, 'to' => $post['title'] ?? null],
                        'application_name' => ['from' => null, 'to' => $post['application_name'] ?? null],
                        'application_version' => ['from' => null, 'to' => $post['application_version'] ?? null],
                        'file_media_id' => ['from' => null, 'to' => $post['file_media_id'] ?? null],
                        'media_folder_id' => ['from' => null, 'to' => $folderId],
                        'status' => ['from' => null, 'to' => 'brouillon'],
                        'doc_status' => ['from' => null, 'to' => 'work'],
                        'doc_version' => ['from' => null, 'to' => 'v0.1'],
                        'modification_kind' => ['from' => null, 'to' => $post['modification_kind'] ?? null],
                    ],
                ]
            );

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
            $tpl->buildReportSkeleton($reportId, $config);

            $now = date('Y-m-d H:i:s');
            $this->reports->update($reportId, [
                'version_date'      => $now,
                'author_updated_at' => $now,
            ]);

            $rv = new \App\Models\ReportVersionModel();
            $rv->insert([
                'report_id'     => $reportId,
                'version_label' => 'v0.1',
                'change_type'   => 'draft',
                'doc_status'    => 'work',
                'changed_by'    => $this->userId(),
                'comment'       => 'Version initiale',
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->withInput()->with('errors', [
                    'create' => 'Erreur lors de la création du bilan.',
                ]);
            }

            return redirect()->to(site_url('report/' . $reportId . '/sections'))
                ->with('success', 'Bilan créé avec son squelette. Vous pouvez commencer la rédaction.');

        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('errors', [
                'create' => 'Création impossible : ' . $e->getMessage(),
            ]);
        }
    }


    public function postUpdate(int $id)
    {
        $report = $this->findReportWithUsersOr404($id);
        $this->requireOwnerOrAdmin($report);

        $post = $this->request->getPost();

        $rules = [
            'title'               => 'required|min_length[3]',
            'application_name'    => 'required|min_length[2]',
            'application_version' => 'permit_empty|max_length[50]',
            'file_media_id'       => 'permit_empty',
            'status'              => 'permit_empty|in_list[brouillon,en relecture,final]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $before = [
            'title'               => $report['title'] ?? null,
            'application_name'    => $report['application_name'] ?? null,
            'application_version' => $report['application_version'] ?? null,
            'file_media_id'       => $report['file_media_id'] ?? null,
            'status'              => $report['status'] ?? null,
        ];

        $after = [
            'title'               => $post['title'] ?? null,
            'application_name'    => $post['application_name'] ?? null,
            'application_version' => $post['application_version'] ?? null,
            'file_media_id'       => $post['file_media_id'] ?? null,
            'status'              => $post['status'] ?? ($report['status'] ?? 'brouillon'),
        ];

        $before['file_media_id'] = (int) ($before['file_media_id'] ?? 0) ?: null;
        $after['file_media_id']  = (int) ($after['file_media_id'] ?? 0) ?: null;

        $changes = $this->buildDiff($before, $after);

        $this->reports->update($id, [
            'title'               => $after['title'],
            'application_name'    => $after['application_name'],
            'application_version' => $after['application_version'],
            'file_media_id'       => $after['file_media_id'],
            'status'              => $after['status'],
            'author_updated_at'   => date('Y-m-d H:i:s'),
        ]);

        if (!empty($changes)) {
            $this->log(
                'update',
                'report',
                (int) $id,
                'Mise à jour du bilan',
                ['changes' => $changes]
            );
        }

        return redirect()->to(site_url('report/' . $id))->with('success', 'Bilan mis à jour.');
    }


    public function postDuplicate(int $id)
    {
        $src = $this->findReportWithUsersOr404($id);

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $now = date('Y-m-d H:i:s');
            $newTitle = trim((string)($src['title'] ?? 'Bilan')) . ' (copie)';

            $newReportData = [
                'user_id'              => $this->userId(),
                'title'                => $newTitle,
                'application_name'     => $src['application_name'] ?? '',
                'application_version'  => $src['application_version'] ?? null,
                'file_media_id'        => !empty($src['file_media_id']) ? (int)$src['file_media_id'] : null,
                'author_name'          => $this->currentUserFullName(),
                'status'               => 'brouillon',
                'doc_status'           => 'work',
                'doc_version'          => 'v0.1',
                'modification_kind'    => $src['modification_kind'] ?? 'creation',
                'version_date'         => $now,
                'author_updated_at'    => $now,

                'validated_by'         => null,
                'validated_at'         => null,
                'corrected_by'         => null,
                'corrected_at'         => null,
                'comments'             => null,
            ];

            $newId = (int) $this->reports->insert($newReportData, true);

            // LOG : duplication report
            $before = [
                'title'               => $src['title'] ?? null,
                'application_name'    => $src['application_name'] ?? null,
                'application_version' => $src['application_version'] ?? null,
                'file_media_id'       => $src['file_media_id'] ?? null,
                'status'              => $src['status'] ?? null,
                'doc_status'          => $src['doc_status'] ?? null,
                'doc_version'         => $src['doc_version'] ?? null,
                'modification_kind'   => $src['modification_kind'] ?? null,
            ];

            $after = [
                'title'               => $newTitle,
                'application_name'    => $newReportData['application_name'] ?? null,
                'application_version' => $newReportData['application_version'] ?? null,
                'file_media_id'       => $newReportData['file_media_id'] ?? null,
                'status'              => $newReportData['status'] ?? null,
                'doc_status'          => $newReportData['doc_status'] ?? null,
                'doc_version'         => $newReportData['doc_version'] ?? null,
                'modification_kind'   => $newReportData['modification_kind'] ?? null,
            ];

            $before['file_media_id'] = (int)($before['file_media_id'] ?? 0) ?: null;
            $after['file_media_id']  = (int)($after['file_media_id'] ?? 0) ?: null;

            $changes = $this->buildDiff($before, $after);

            $this->log(
                'duplicate',
                'report',
                $newId,
                'Duplication du bilan #' . (int)$id . ' vers #' . (int)$newId,
                [
                    'source_report_id' => (int)$id,
                    'target_report_id' => (int)$newId,
                    'changes'          => $changes,
                ]
            );

            $srcSections = $this->sections
                ->where('report_id', (int)$id)
                ->orderBy('level', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll();

            $map = [];

            foreach ($srcSections as $s) {
                $oldId = (int)($s['id'] ?? 0);

                $oldParent = (int)($s['parent_id'] ?? 0);
                $newParent = null;
                if ($oldParent > 0) {
                    $newParent = $map[$oldParent] ?? null;
                }

                $insert = $s;

                unset(
                    $insert['id'],
                    $insert['created_at'],
                    $insert['updated_at']
                );

                $insert['report_id'] = $newId;
                $insert['parent_id'] = $newParent;

                $newSectionId = (int) $this->sections->insert($insert, true);
                $map[$oldId] = $newSectionId;
            }

            $this->sectionsService->recomputeCodes($newId);

            $rv = new \App\Models\ReportVersionModel();
            $rv->insert([
                'report_id'     => $newId,
                'version_label' => 'v0.1',
                'change_type'   => 'draft',
                'doc_status'    => 'work',
                'changed_by'    => $this->userId(),
                'comment'       => 'Dupliqué depuis le bilan #' . (int)$id,
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->with('errors', ['dup' => 'Erreur lors de la duplication.']);
            }

            return redirect()->to(site_url('report/' . $newId . '/sections'))
                ->with('success', 'Bilan dupliqué. Vous pouvez modifier la copie sans impacter l’original.');

        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->with('errors', [
                'dup' => 'Duplication impossible : ' . $e->getMessage(),
            ]);
        }
    }


    public function getSections(int $id)
    {
        $report = $this->findReportWithUsersOr404($id);

        $canEdit = ((int)($report['user_id'] ?? 0) === $this->userId());
        $tree    = $this->sectionsService->getTreeForReport($id);

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

    public function getEditSection(int $reportId, int $sectionId)
    {
        $report = $this->findReportWithUsersOr404($reportId);
        $this->requireOwner($report);

        $section = $this->findSectionOr404($reportId, $sectionId);

        return $this->view('front/reports/section_edit', [
            'report'  => $report,
            'section' => $section,
            'errors'  => session('errors') ?? [],
            'success' => session('success'),
        ], ['saveData' => false]);
    }

    public function postUpdateSection(int $reportId, int $sectionId)
    {
        $report = $this->findReportWithUsersOr404($reportId);
        $this->requireOwner($report);

        $section = $this->findSectionOr404($reportId, $sectionId);

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

        $before = [
            'title'             => $section['title'] ?? null,
            'content'           => $section['content'] ?? null,
            'period_label'      => $section['period_label'] ?? null,
            'period_number'     => $section['period_number'] ?? null,
            'debit_value'       => $section['debit_value'] ?? null,
            'start_date'        => $section['start_date'] ?? null,
            'end_date'          => $section['end_date'] ?? null,
            'compliance_status' => $section['compliance_status'] ?? null,
        ];

        $after = [
            'title'             => $post['title'] ?? null,
            'content'           => $post['content'] ?? null,
            'period_label'      => $post['period_label'] ?? null,
            'period_number'     => ($post['period_number'] ?? '') !== '' ? (int)$post['period_number'] : null,
            'debit_value'       => ($post['debit_value'] ?? '') !== '' ? $post['debit_value'] : null,
            'start_date'        => ($post['start_date'] ?? '') ?: null,
            'end_date'          => ($post['end_date'] ?? '') ?: null,
            'compliance_status' => $post['compliance_status'] ?? 'non_applicable',
        ];

        $changes = $this->buildDiff($before, $after);

        $this->sections->update($sectionId, [
            'title'             => $after['title'],
            'content'           => $after['content'],
            'period_label'      => $after['period_label'],
            'period_number'     => $after['period_number'],
            'debit_value'       => $after['debit_value'],
            'start_date'        => $after['start_date'],
            'end_date'          => $after['end_date'],
            'compliance_status' => $after['compliance_status'],
        ]);

        $this->reports->update($reportId, [
            'author_updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!empty($changes)) {
            $this->log(
                'update',
                'report_section',
                (int) $sectionId,
                'Modification de section',
                [
                    'report_id' => (int) $reportId,
                    'changes'   => $changes,
                ]
            );
        }

        return redirect()->to(site_url('report/' . $reportId . '/sections'))
            ->with('success', 'Section mise à jour.');
    }


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

    public function postDelete(int $id)
    {
        $report = $this->findReportWithUsersOr404($id);
        $this->requireOwnerOrAdmin($report);

        $this->log(
            'delete',
            'report',
            (int) $id,
            'Suppression du bilan',
            [
                'changes' => [
                    'title' => ['from' => $report['title'] ?? null, 'to' => null],
                    'application_name' => ['from' => $report['application_name'] ?? null, 'to' => null],
                    'status' => ['from' => $report['status'] ?? null, 'to' => null],
                ],
            ]
        );

        $this->sections->where('report_id', $id)->delete();
        $this->reports->delete($id);

        return redirect()->to(site_url('report'))->with('success', 'Bilan supprimé.');
    }


    public function postUpdateMetaInline(int $reportId)
    {
        $report = $this->findReportWithUsersOr404($reportId);
        $this->requireOwnerOrAdmin($report);

        $before = [
            'title'               => $report['title'] ?? null,
            'application_name'    => $report['application_name'] ?? null,
            'application_version' => $report['application_version'] ?? null,
            'status'              => $report['status'] ?? null,
            'author_name'         => $report['author_name'] ?? null,
            'doc_status'          => $report['doc_status'] ?? null,
            'modification_kind'   => $report['modification_kind'] ?? null,
            'file_media_id'       => $report['file_media_id'] ?? null,
        ];

        $docStatus = trim((string)$this->request->getPost('doc_status'));
        if ($docStatus === '') $docStatus = (string)($report['doc_status'] ?? 'work');

        if ($docStatus === 'validated') {

            $this->log(
                'forbidden',
                'report',
                (int) $reportId,
                'Tentative de validation via interface front (bloquée)'
            );

            return redirect()->back()->with('errors', [
                'doc_status' => 'La validation se fait uniquement depuis l’interface admin.',
            ]);
        }

        $rawFileId = trim((string)$this->request->getPost('file_media_id'));
        if ($rawFileId === '0') $rawFileId = '';

        $data = [
            'title'               => trim((string)$this->request->getPost('title')),
            'application_name'    => trim((string)$this->request->getPost('application_name')),
            'application_version' => trim((string)$this->request->getPost('application_version')),
            'status'              => trim((string)$this->request->getPost('status')) ?: (string)($report['status'] ?? 'brouillon'),
            'author_name'         => trim((string)$this->request->getPost('author_name')) ?: (string)($report['author_name'] ?? ''),
            'doc_status'          => $docStatus,
            'doc_version'         => 'v0.1',
            'modification_kind'   => trim((string)$this->request->getPost('modification_kind')) ?: (string)($report['modification_kind'] ?? 'creation'),
            'author_updated_at'   => date('Y-m-d H:i:s'),
            'file_media_id'       => $rawFileId,
        ];

        $rules = [
            'title'               => 'required|min_length[3]',
            'application_name'    => 'required|min_length[2]',
            'application_version' => 'permit_empty|max_length[50]',
            'status'              => 'permit_empty|in_list[brouillon,en relecture,final]',
            'author_name'         => 'permit_empty|max_length[120]',
            'doc_status'          => 'required|in_list[work,approved]',
            'modification_kind'   => 'required|in_list[creation,replace]',
            'file_media_id'       => 'permit_empty|is_natural_no_zero',
        ];

        if (! $this->validateData($data, $rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        if ($data['file_media_id'] === '') {
            $data['file_media_id'] = null;
        } else {
            $data['file_media_id'] = (int)$data['file_media_id'];
        }

        $after = [
            'title'               => $data['title'] ?? null,
            'application_name'    => $data['application_name'] ?? null,
            'application_version' => $data['application_version'] ?? null,
            'status'              => $data['status'] ?? null,
            'author_name'         => $data['author_name'] ?? null,
            'doc_status'          => $data['doc_status'] ?? null,
            'modification_kind'   => $data['modification_kind'] ?? null,
            'file_media_id'       => $data['file_media_id'] ?? null,
        ];

        $before['file_media_id'] = (int) ($before['file_media_id'] ?? 0) ?: null;
        $after['file_media_id']  = (int) ($after['file_media_id'] ?? 0) ?: null;

        $changes = $this->buildDiff($before, $after);

        $this->reports->update($reportId, $data);

        if (!empty($changes)) {
            $this->log(
                'update',
                'report',
                (int) $reportId,
                'Mise à jour des informations (inline)',
                ['changes' => $changes]
            );
        }

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
        $report = $this->mustGetMyReport($reportId);

        $docStatus = (string) $this->request->getPost('doc_status');
        $modKind   = (string) $this->request->getPost('modification_kind');

        $allowedDoc = ['work', 'approved', 'validated'];
        $allowedMod = ['creation', 'replace'];

        if (!in_array($docStatus, $allowedDoc, true) || !in_array($modKind, $allowedMod, true)) {
            $this->error('Valeurs invalides.');
            return $this->redirect()->back();
        }

        $this->reports->update($reportId, [
            'doc_status'        => $docStatus,
            'doc_version'       => 'v0.1',
            'modification_kind' => $modKind,
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        $this->success('Informations mises à jour.');
        return $this->redirect()->back();
    }




    public function getPdf(int $id)
    {
        helper('html_helper');

        $report = $this->findReportWithUsersOr404($id);
        $this->requireOwnerOrAdmin($report);

        $sectionsTree = $this->sectionsService->getTreeForReport($id);

        $versions = model(\App\Models\ReportVersionModel::class)
            ->where('report_id', $id)
            ->orderBy('id', 'ASC')
            ->findAll();

        $author    = (string)($report['author_name'] ?? $this->currentUserFullName());
        $createdAt = (string)($report['created_at'] ?? '');
        $updatedAt = (string)($report['author_updated_at'] ?? ($report['updated_at'] ?? ''));

        $fmtDateTime = function (?string $value): string {
            if (empty($value)) return '—';
            try { return (new \DateTime($value))->format('d/m/Y H:i'); }
            catch (\Throwable $e) { return (string)$value; }
        };

        $html = view('front/reports/pdf', [
            'report'       => $report,
            'sectionsTree' => $sectionsTree,
            'versions'     => $versions,
            'author'       => $author,
            'createdAt'    => $fmtDateTime($createdAt),
            'updatedAt'    => $fmtDateTime($updatedAt),
            'generatedAt'  => date('d/m/Y H:i'),
        ]);

        // Dompdf
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('tempDir', WRITEPATH . 'cache/dompdf');

        $options->set('chroot', FCPATH);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        $canvas = $dompdf->getCanvas();
        $w = $canvas->get_width();
        $h = $canvas->get_height();

        $text = "Page {PAGE_NUM} / {PAGE_COUNT}";
        $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $size = 9;

        $textWidth = $dompdf->getFontMetrics()->getTextWidth($text, $font, $size);

        $rightMargin = 150;

        $x = $w - $textWidth - $rightMargin;

        $y = $h - 25;

        $canvas->page_text($x, $y, $text, $font, $size, [0.3, 0.3, 0.3]);

        $download = ((int)($this->request->getGet('download') ?? 0) === 1);
        $filename = 'bilan_' . (int)$id . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', ($download ? 'attachment' : 'inline') . '; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }
}
