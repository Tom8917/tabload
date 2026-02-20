<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Logs extends BaseController
{
    protected $require_auth = true;
    protected $title = 'Logs';
    protected $menu  = 'logs';

    public function getIndex()
    {
        $req = $this->request;

        // Pagination
        $page  = max(1, (int)($req->getGet('page') ?? 1));
        $limit = (int)($req->getGet('limit') ?? 25);
        $limit = ($limit < 10) ? 10 : (($limit > 100) ? 100 : $limit);
        $offset = ($page - 1) * $limit;

        // Filtres / recherche
        $q          = trim((string)($req->getGet('q') ?? ''));
        $userQ      = trim((string)($req->getGet('user') ?? ''));
        $action     = trim((string)($req->getGet('action') ?? ''));
        $entityType = trim((string)($req->getGet('entity_type') ?? ''));
        $entityId   = trim((string)($req->getGet('entity_id') ?? ''));
        $dateFrom   = trim((string)($req->getGet('from') ?? ''));  // YYYY-MM-DD
        $dateTo     = trim((string)($req->getGet('to') ?? ''));    // YYYY-MM-DD

        // Mini garde-fous (évite des champs énormes / spam)
        $q     = mb_substr($q, 0, 200);
        $userQ = mb_substr($userQ, 0, 200);
        $entityType = mb_substr($entityType, 0, 80);
        $action = mb_substr($action, 0, 30);

        $lm = model(\App\Models\LogModel::class);

        $lm->select("
    logs.*,
    logs.entity_type AS entity_type_raw,
    logs.entity_id   AS entity_id_raw,
    CONCAT(u.firstname, ' ', u.lastname) AS user_fullname,
    u.email AS user_email,
    r.title AS report_title
")
            ->join('user u', 'u.id = logs.user_id', 'left')
            ->join(
                'reports r',
                "r.id = CAST(logs.entity_id AS UNSIGNED) AND LOWER(logs.entity_type) = 'report'",
                'left',
                false
            );

        // Filters
        if ($action !== '') {
            $lm->where('logs.action', $action);
        }

        if ($entityType !== '') {
            $lm->where('logs.entity_type', $entityType);
        }

        if ($entityId !== '' && ctype_digit($entityId)) {
            $lm->where('logs.entity_id', (int)$entityId);
        }

        // Dates (si created_at est DATETIME)
        if ($dateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $lm->where('logs.created_at >=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            $lm->where('logs.created_at <=', $dateTo . ' 23:59:59');
        }

        if ($userQ !== '') {
            $db = db_connect();

            $needle = '%' . $db->escapeLikeString($userQ) . '%';
            $needleEsc = $db->escape($needle); // ajoute les quotes + échappe

            $lm->groupStart()
                ->like('u.email', $userQ)
                ->orLike('u.firstname', $userQ)
                ->orLike('u.lastname', $userQ)
                // CONCAT en SQL brut (pas via orLike)
                ->orWhere("CONCAT(u.firstname, ' ', u.lastname) LIKE {$needleEsc}", null, false)
                ->groupEnd();
        }

        if ($q !== '') {
            $db = db_connect();

            $needle = '%' . $db->escapeLikeString($q) . '%';
            $needleEsc = $db->escape($needle);

            $lm->groupStart()
                ->like('logs.message', $q)
                ->orLike('logs.entity_type', $q)
                ->orLike('logs.action', $q)
                // CAST en SQL brut (pas via orLike)
                ->orWhere("CAST(logs.entity_id AS CHAR) LIKE {$needleEsc}", null, false)
                ->orWhere("CAST(logs.id AS CHAR) LIKE {$needleEsc}", null, false)
                ->groupEnd();
        }

        $total = (int)$lm->countAllResults(false);

        // Rows (avec filtres)
        $rows = $lm->orderBy('logs.id', 'DESC')
            ->findAll($limit, $offset);

        // Decode meta JSON
        foreach ($rows as &$r) {
            $r['meta_arr'] = [];
            if (!empty($r['meta'])) {
                $decoded = json_decode($r['meta'], true);
                if (is_array($decoded)) $r['meta_arr'] = $decoded;
            }
        }
        unset($r);

        $pages = (int)max(1, (int)ceil($total / $limit));

        return $this->view('admin/logs/index', [
            'logs'   => $rows,
            'page'   => $page,
            'pages'  => $pages,
            'limit'  => $limit,
            'total'  => $total,
            'filters' => [
                'q' => $q,
                'user' => $userQ,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
        ], true, ['saveData' => true]);
    }
}