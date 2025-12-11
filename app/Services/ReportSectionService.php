<?php

namespace App\Services;

use App\Models\ReportSectionModel;

class ReportSectionService
{
    protected ReportSectionModel $sections;

    public function __construct()
    {
        $this->sections = new ReportSectionModel();
    }

    /**
     * Crée une section racine (niveau 1) : 1, 2, 3...
     */
    public function createRootSection(int $reportId, array $data): int
    {
        $last = $this->sections
            ->where('report_id', $reportId)
            ->where('parent_id', null)
            ->orderBy('position', 'DESC')
            ->first();

        $nextPosition = $last ? ((int) $last['position'] + 1) : 1;
        $code         = (string) $nextPosition;

        $insertData = array_merge($data, [
            'report_id' => $reportId,
            'parent_id' => null,
            'position'  => $nextPosition,
            'level'     => 1,
            'code'      => $code,
        ]);

        return $this->sections->insert($insertData, true);
    }

    /**
     * Crée une sous-section enfant : 1.1, 1.2, 1.2.1, etc.
     */
    public function createChildSection(int $parentId, array $data): int
    {
        $parent = $this->sections->find($parentId);

        if (! $parent) {
            throw new \RuntimeException("Parent section not found: {$parentId}");
        }

        $lastChild = $this->sections
            ->where('parent_id', $parentId)
            ->orderBy('position', 'DESC')
            ->first();

        $nextPosition = $lastChild ? ((int) $lastChild['position'] + 1) : 1;

        $level = ((int) $parent['level']) + 1;
        $code  = $parent['code'] . '.' . $nextPosition;

        $insertData = array_merge($data, [
            'report_id' => $parent['report_id'],
            'parent_id' => $parentId,
            'position'  => $nextPosition,
            'level'     => $level,
            'code'      => $code,
        ]);

        return $this->sections->insert($insertData, true);
    }

    /**
     * Récupère toutes les sections d'un bilan, triées pour affichage / PDF.
     */
    public function getSectionsForReport(int $reportId): array
    {
        return $this->sections
            ->where('report_id', $reportId)
            ->orderBy('level', 'ASC')
            ->orderBy('parent_id', 'ASC')
            ->orderBy('position', 'ASC')
            ->findAll();
    }
}
