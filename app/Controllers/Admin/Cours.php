<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CoursModel;

class Cours extends BaseController
{
    protected CoursModel $cours;
    protected string $mediaPath;

    public function __construct()
    {
        $this->cours = new CoursModel();

        $this->mediaPath = FCPATH . 'uploads/media/';
        if (!is_dir($this->mediaPath)) { @mkdir($this->mediaPath, 0775, true); }

        helper(['text','url']);
    }

    public function getIndex()
    {
        $data['cours'] = $this->cours->orderBy('title','ASC')->findAll();
        return $this->view('/admin/cours/index.php', $data, ['saveData'=>true]);
    }

    public function getCreate()
    {
        return $this->view('/admin/cours/form.php', ['cours'=>null], ['saveData'=>true]);
    }

    public function postStore()
    {
        $rules = [
            'title' => 'required|min_length[3]|max_length[150]',
            'slug'  => 'permit_empty|alpha_dash|max_length[150]|is_unique[cours.slug]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        }

        $title = (string)$this->request->getPost('title');
        $slug  = trim((string)$this->request->getPost('slug'));

        if ($slug === '') {
            $slug = $this->cours->makeSlug($title);
        } else {
            $slug = url_title(convert_accented_characters($slug), '-', true);
            if ($slug === '' || $this->cours->where('slug',$slug)->first()) {
                $slug = $this->cours->makeSlug($title);
            }
        }

        $imagePath = $this->handleImage(); // lit image_file ou image_url (Media)

        $ok = $this->cours->insert([
            'title'       => $title,
            'slug'        => $slug,
            'description' => (string)$this->request->getPost('description'),
            'content'     => (string)$this->request->getPost('content'),
            'image'       => $imagePath ?: null,
        ]);

        if (!$ok) {
            return redirect()->back()->withInput()->with('errors',$this->cours->errors() ?: ['Création impossible']);
        }

        return redirect()->to(site_url('admin/cours'))->with('message','Cours créé');
    }

    public function getEdit(int $id)
    {
        $row = $this->cours->find($id);
        if (!$row) return redirect()->to(site_url('admin/cours'))->with('error','Cours introuvable');

        return $this->view('/admin/cours/form.php', ['cours'=>$row], ['saveData'=>true]);
    }

    public function postUpdate(int $id)
    {
        $row = $this->cours->find($id);
        if (!$row) return redirect()->to(site_url('admin/cours'))->with('error','Cours introuvable');

        $rules = [
            'title' => 'required|min_length[3]|max_length[150]',
            'slug'  => "permit_empty|alpha_dash|max_length[150]|is_unique[cours.slug,id,{$id}]",
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        }

        $title = (string)$this->request->getPost('title');
        $slug  = trim((string)$this->request->getPost('slug'));

        if ($slug === '') {
            $slug = $this->cours->makeSlug($title);
        } else {
            $slug = url_title(convert_accented_characters($slug), '-', true);
            if ($slug === '') {
                $slug = $this->cours->makeSlug($title);
            } else {
                $exists = $this->cours->where('slug',$slug)->where('id !=', $id)->first();
                if ($exists) {
                    $base = $slug !== '' ? $slug : $title;
                    $base = url_title(convert_accented_characters($base), '-', true);
                    if ($base === '') $base = 'cours';
                    $i = 1; $candidate = $base.'-'.$i;
                    while ($this->cours->where('slug',$candidate)->where('id !=',$id)->first()) {
                        $i++; $candidate = $base.'-'.$i;
                    }
                    $slug = $candidate;
                }
            }
        }

        // ✅ IMPORTANT : fallback sur $row['image'] pour ne PAS perdre l'image si on ne retouche rien
        $imagePath = $this->handleImage($row['image'] ?? null);

        $ok = $this->cours->update($id, [
            'title'       => $title,
            'slug'        => $slug,
            'description' => (string)$this->request->getPost('description'),
            'content'     => (string)$this->request->getPost('content'),
            'image'       => $imagePath ?: null,
        ]);

        if (!$ok) {
            return redirect()->back()->withInput()->with('errors',$this->cours->errors() ?: ['Mise à jour impossible']);
        }

        return redirect()->to(site_url('admin/cours'))->with('message','Cours mis à jour');
    }

    public function getDelete(int $id)
    {
        $this->cours->delete($id);
        return redirect()->back()->with('message','Cours supprimé');
    }

    /* ===== Helpers ===== */

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
        $current = $fallback;

        // Upload direct (optionnel)
        $file = $this->request->getFile('image_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext = strtolower($file->getExtension());
            if (in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) {
                $new = uniqid('media_') . '.' . $ext;
                $file->move($this->mediaPath, $new);
                return 'uploads/media/' . $new;
            }
            return $current;
        }

        // Sélection depuis Media
        $url = trim((string)$this->request->getPost('image_url'));
        if ($url !== '') {
            $norm = $this->normalizeMediaUrl($url);
            if ($norm !== '') return $norm;
        }

        return $current;
    }
}
