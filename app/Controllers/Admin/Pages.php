<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PageModel;

class Pages extends BaseController
{
    protected PageModel $pages;
    protected string $mediaPath;

    public function __construct()
    {
        $this->pages = new PageModel();

        $this->mediaPath = FCPATH . 'uploads/media/';
        if (!is_dir($this->mediaPath)) { @mkdir($this->mediaPath, 0775, true); }

        helper(['text','url']);
    }

    public function getIndex()
    {
        $data['pages'] = $this->pages->orderBy('title','ASC')->findAll();
        return $this->view('admin/pages/index', $data, true);
    }

    public function getCreate()
    {
        return $this->view('admin/pages/form', ['page'=>null], true);
    }

    public function postStore()
    {
        $rules = [
            'title'      => 'required|min_length[3]|max_length[150]',
            'slug'       => 'permit_empty|alpha_dash|max_length[150]|is_unique[pages.slug]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        }

        $title = (string)$this->request->getPost('title');
        $slug  = trim((string)$this->request->getPost('slug'));

        if ($slug === '') {
            $slug = $this->pages->makeSlug($title);
        } else {
            $slug = url_title(convert_accented_characters($slug), '-', true);
            if ($slug === '') {
                $slug = $this->pages->makeSlug($title);
            }
            if ($this->pages->where('slug',$slug)->first()) {
                $slug = $this->pages->makeSlug($title);
            }
        }

        $imagePath = $this->handleImage();

        $ok = $this->pages->insert([
            'title'       => $title,
            'slug'        => $slug,
            'description' => (string)$this->request->getPost('description'),
            'content'     => (string)$this->request->getPost('content'),
            'image'       => $imagePath,
        ]);

        if (!$ok) {
            return redirect()->back()->withInput()->with('errors',$this->pages->errors() ?: ['Création impossible']);
        }

        return redirect()->to(site_url('admin/pages'))->with('message','Page créée');
    }

    public function getEdit(int $id)
    {
        $page = $this->pages->find($id);
        if (!$page) return redirect()->to(site_url('admin/pages'))->with('error','Page introuvable');
        return $this->view('admin/pages/form', ['page'=>$page], true);
    }

    public function postUpdate(int $id)
    {
        $page = $this->pages->find($id);
        if (!$page) return redirect()->to(site_url('admin/pages'))->with('error','Page introuvable');

        $rules = [
            'title'      => 'required|min_length[3]|max_length[150]',
            'slug'       => "permit_empty|alpha_dash|max_length[150]|is_unique[pages.slug,id,{$id}]",
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        }

        $title = (string)$this->request->getPost('title');
        $slug  = trim((string)$this->request->getPost('slug'));

        if ($slug === '') {
            $slug = $this->pages->makeSlug($title);
        } else {
            $slug = url_title(convert_accented_characters($slug), '-', true);
            if ($slug === '') {
                $slug = $this->pages->makeSlug($title);
            } else {
                $exists = $this->pages->where('slug',$slug)->where('id !=', $id)->first();
                if ($exists) {
                    $base = $slug !== '' ? $slug : $title;
                    $base = url_title(convert_accented_characters($base), '-', true);
                    if ($base === '') $base = 'page';

                    $i = 1; $candidate = $base.'-'.$i;
                    while ($this->pages->where('slug',$candidate)->where('id !=',$id)->first()) {
                        $i++;
                        $candidate = $base.'-'.$i;
                    }
                    $slug = $candidate;
                }
            }
        }

        $imagePath = $this->handleImage($page['image'] ?? null);

        $ok = $this->pages->update($id, [
            'title'       => $title,
            'slug'        => $slug,
            'description' => (string)$this->request->getPost('description'),
            'content'     => (string)$this->request->getPost('content'),
            'image'       => $imagePath,
        ]);

        if (!$ok) {
            return redirect()->back()->withInput()->with('errors',$this->pages->errors() ?: ['Mise à jour impossible']);
        }

        return redirect()->to(site_url('admin/pages'))->with('message','Page mise à jour');
    }

    public function getDelete(int $id)
    {
        $this->pages->delete($id);
        return redirect()->back()->with('message','Page supprimée');
    }


    protected function normalizeMediaUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') return '';

        if (str_starts_with($url, 'uploads/media/')) return $url;

        $base = rtrim(base_url(), '/');
        if (str_starts_with($url, $base.'/uploads/media/')) {
            return ltrim(str_replace($base.'/', '', $url), '/');
        }

        return '';
    }

    protected function handleImage(?string $fallback = null): ?string
    {
        $imagePath = $fallback;

        $file = $this->request->getFile('image_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext = strtolower($file->getExtension());
            if (! in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) {
                return $imagePath;
            }

            $new = uniqid('media_') . '.' . $ext;
            $file->move($this->mediaPath, $new);
            $imagePath = 'uploads/media/' . $new;
        } else {
            $url = trim((string)$this->request->getPost('image_url'));
            if ($url !== '') $imagePath = $url;
        }

        return $imagePath;
    }
}
