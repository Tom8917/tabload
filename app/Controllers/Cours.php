<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CoursModel;

class Cours extends BaseController
{
    protected CoursModel $cours;

    public function __construct()
    {
        $this->cours = new CoursModel(); // casse correcte
    }

    public function getIndex()
    {
        // Paginer et passer 'list' + 'pager' (ce que la vue attend)
        $list  = $this->cours->orderBy('created_at','DESC')->paginate(12);
        $pager = $this->cours->pager;

        return $this->view('/front/cours/index.php', [
            'list'  => $list,
            'pager' => $pager,
        ], ['saveData' => true]);
    }

    public function getShow(string $slug)
    {
        $row = $this->cours->where('slug',$slug)->first();
        if (! $row) {
            return redirect()->to(site_url('cours'))->with('error','Cours introuvable');
        }

        return $this->view('/front/cours/show.php', [
            'cours' => $row,
        ], ['saveData' => true]);
    }
}
