<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PageModel;

class Pages extends BaseController
{
    protected PageModel $pages;

    public function __construct()
    {
        $this->pages = new PageModel();
    }

    public function getIndex()
    {
        $data['pages'] = $this->pages->orderBy('created_at','ASC')->findAll();
        return $this->view('front/pages/index', $data, true);
    }

    public function getShow(string $slug)
    {
        $page = $this->pages->where('slug',$slug)->first();
        if (!$page) return redirect()->to(site_url('pages'))->with('error','Page introuvable');
        return $this->view('front/pages/show', ['page'=>$page], true);
    }
}
