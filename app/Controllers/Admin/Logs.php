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
        $page  = max(1, (int) ($this->request->getGet('page') ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $lm = model(\App\Models\LogModel::class);

        $total = $lm->countAllResults(false);

        $rows = $lm->select('logs.*, CONCAT(u.firstname, " ", u.lastname) AS user_fullname, u.email AS user_email')
            ->join('user u', 'u.id = logs.user_id', 'left')
            ->orderBy('logs.id', 'DESC')
            ->findAll($limit, $offset);

        foreach ($rows as &$r) {
            $r['meta_arr'] = [];
            if (!empty($r['meta'])) {
                $decoded = json_decode($r['meta'], true);
                if (is_array($decoded)) $r['meta_arr'] = $decoded;
            }
        }
        unset($r);

        return $this->view('admin/logs/index', [
            'logs'   => $rows,
            'page'   => $page,
            'limit'  => $limit,
            'total'  => $total,
            'pages'  => (int) ceil($total / $limit),
        ], true, ['saveData' => true]);
    }
}
