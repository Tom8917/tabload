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

    public function recomputeCodes(int $reportId): void
    {
        $sectionsModel = new \App\Models\ReportSectionModel();

        // Récupère toutes les sections du report, ordonnées par parent + position
        $all = $sectionsModel
            ->where('report_id', $reportId)
            ->orderBy('parent_id', 'ASC')
            ->orderBy('position', 'ASC')
            ->findAll();

        // Index par parent_id
        $byParent = [];
        foreach ($all as $s) {
            $pid = $s['parent_id'] ?? 0; // null -> 0
            $byParent[$pid][] = $s;
        }

        // DFS : assigne codes
        $updates = [];

        // Root (parent null)
        $roots = $byParent[0] ?? [];
        $rootIndex = 0;

        foreach ($roots as $root) {
            $rootIndex++;
            $rootCode = (string)$rootIndex;

            $updates[] = ['id' => (int)$root['id'], 'code' => $rootCode, 'level' => 1];

            $this->recomputeChildrenCodes((int)$root['id'], $rootCode, 2, $byParent, $updates);
        }

        // Applique en DB (batch update)
        if (!empty($updates)) {
            foreach ($updates as $u) {
                $sectionsModel->update($u['id'], [
                    'code'  => $u['code'],
                    'level' => $u['level'],
                ]);
            }
        }
    }

    private function recomputeChildrenCodes(
        int $parentId,
        string $prefix,
        int $level,
        array $byParent,
        array &$updates
    ): void
    {
        $children = $byParent[$parentId] ?? [];
        $i = 0;

        foreach ($children as $child) {
            $i++;
            $code = $prefix . '.' . $i;

            $updates[] = ['id' => (int)$child['id'], 'code' => $code, 'level' => $level];

            $this->recomputeChildrenCodes((int)$child['id'], $code, $level + 1, $byParent, $updates);
        }
    }

    public function getTreeForReport(int $reportId): array
    {
        $model = new \App\Models\ReportSectionModel();

        $rows = $model->where('report_id', $reportId)
            ->orderBy('parent_id', 'ASC')
            ->orderBy('position', 'ASC')
            ->findAll();

        // index par id
        $byId = [];
        foreach ($rows as $r) {
            $r['children'] = [];
            $byId[(int)$r['id']] = $r;
        }

        // construction du tree
        $tree = [];
        foreach ($byId as $id => &$node) {
            $pid = $node['parent_id'] ? (int)$node['parent_id'] : 0;
            if ($pid === 0) {
                $tree[] = &$node;
            } elseif (isset($byId[$pid])) {
                $byId[$pid]['children'][] = &$node;
            }
        }
        unset($node);

        return $tree;
    }
}
