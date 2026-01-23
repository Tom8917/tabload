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

    public function createRootSection(int $reportId, array $data): int
    {
        $last = $this->sections
            ->where('report_id', $reportId)
            ->where('parent_id', null)
            ->orderBy('sort_order', 'DESC')
            ->orderBy('position', 'DESC')
            ->first();

        $nextOrder = $last ? ((int)($last['sort_order'] ?? $last['position'] ?? 0) + 1) : 1;

        $insertData = array_merge($data, [
            'report_id'   => $reportId,
            'parent_id'   => null,
            'sort_order'  => $nextOrder,
            'position'    => $nextOrder,
            'level'       => 1,
            'code'        => (string)$nextOrder,
        ]);

        return (int)$this->sections->insert($insertData, true);
    }


    public function createChildSection(int $parentId, array $data): int
    {
        $parent = $this->sections->find($parentId);

        if (!$parent) {
            throw new \RuntimeException("Parent section not found: {$parentId}");
        }

        $lastChild = $this->sections
            ->where('parent_id', $parentId)
            ->orderBy('sort_order', 'DESC')
            ->orderBy('position', 'DESC')
            ->first();

        $nextOrder = $lastChild ? ((int)($lastChild['sort_order'] ?? $lastChild['position'] ?? 0) + 1) : 1;

        $level = ((int)$parent['level']) + 1;

        $insertData = array_merge($data, [
            'report_id'   => $parent['report_id'],
            'parent_id'   => $parentId,
            'sort_order'  => $nextOrder,
            'position'    => $nextOrder,
            'level'       => $level,
            'code'        => $parent['code'] . '.' . $nextOrder,
        ]);

        return (int)$this->sections->insert($insertData, true);
    }


    public function getSectionsForReport(int $reportId): array
    {
        return $this->sections
            ->where('report_id', $reportId)
            ->orderBy('level', 'ASC')
            ->orderBy('parent_id', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('position', 'ASC')
            ->findAll();
    }


    public function recomputeCodes(int $reportId): void
    {
        $sectionsModel = new ReportSectionModel();

        $all = $sectionsModel
            ->where('report_id', $reportId)
            ->orderBy('parent_id', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('position', 'ASC')
            ->findAll();

        $byParent = [];
        foreach ($all as $s) {
            $pid = $s['parent_id'] ?? 0;
            $byParent[$pid][] = $s;
        }

        $updates = [];

        $roots = $byParent[0] ?? [];
        $rootIndex = 0;

        foreach ($roots as $root) {
            $rootIndex++;
            $rootCode = (string)$rootIndex;

            $updates[] = [
                'id'        => (int)$root['id'],
                'code'      => $rootCode,
                'level'     => 1,
                'sort_order'=> $rootIndex,
                'position'  => $rootIndex,
            ];

            $this->recomputeChildrenCodes((int)$root['id'], $rootCode, 2, $byParent, $updates);
        }

        foreach ($updates as $u) {
            $sectionsModel->update($u['id'], [
                'code'       => $u['code'],
                'level'      => $u['level'],
                'sort_order' => $u['sort_order'],
                'position'   => $u['position'],
            ]);
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

            $updates[] = [
                'id'        => (int)$child['id'],
                'code'      => $code,
                'level'     => $level,
                'sort_order'=> $i,
                'position'  => $i,
            ];

            $this->recomputeChildrenCodes((int)$child['id'], $code, $level + 1, $byParent, $updates);
        }
    }

    public function getTreeForReport(int $reportId): array
    {
        $rows = $this->sections
            ->where('report_id', $reportId)
            ->orderBy('parent_id', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('position', 'ASC')
            ->findAll();

        $byId = [];
        foreach ($rows as $r) {
            $r['children'] = [];
            $byId[(int)$r['id']] = $r;
        }

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

    public function deleteSectionWithChildren(int $reportId, int $sectionId): void
    {
        $section = $this->sections->find($sectionId);
        if (!$section || (int)$section['report_id'] !== $reportId) {
            throw new \RuntimeException("Section invalide (report mismatch) : {$sectionId}");
        }

        $ids = $this->collectDescendantIds($reportId, $sectionId);

        $ids = array_reverse($ids);

        foreach ($ids as $id) {
            $this->sections->delete($id);
        }

        $this->sections->delete($sectionId);
    }

    private function collectDescendantIds(int $reportId, int $parentId): array
    {
        $rows = $this->sections
            ->select('id')
            ->where('report_id', $reportId)
            ->where('parent_id', $parentId)
            ->findAll();

        $ids = [];

        foreach ($rows as $r) {
            $childId = (int)$r['id'];
            $ids[] = $childId;

            $ids = array_merge($ids, $this->collectDescendantIds($reportId, $childId));
        }

        return $ids;
    }

    public function moveRoot(int $reportId, int $sectionId, int $direction): void
    {
        $section = $this->sections->find($sectionId);
        if (!$section || (int)$section['report_id'] !== $reportId || $section['parent_id'] !== null) {
            throw new \RuntimeException('Section racine invalide.');
        }

        $currentPos = (int)$section['position'];

        $neighbor = $this->sections
            ->where('report_id', $reportId)
            ->where('parent_id', null)
            ->where('position', $currentPos + $direction)
            ->first();

        if (!$neighbor) {
            return;
        }

        $neighborId  = (int)$neighbor['id'];
        $neighborPos = (int)$neighbor['position'];

        $this->sections->update($sectionId, ['position' => $neighborPos]);
        $this->sections->update($neighborId, ['position' => $currentPos]);

        $this->recomputeCodes($reportId);
    }
}
