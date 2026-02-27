<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MediaModel;
use App\Models\MediaBlobModel;
use App\Models\MediaFolderModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Media extends BaseController
{
    protected $require_auth = true;
    protected $requiredPermissions = ['administrateur'];

    protected MediaModel $media;
    protected MediaBlobModel $blob;
    protected MediaFolderModel $folders;

    public function __construct()
    {
        $this->media   = new MediaModel();
        $this->blob    = new MediaBlobModel();
        $this->folders = new MediaFolderModel();

        $this->title = 'Médiathèque';
        $this->menu  = 'media';
    }

    public function getIndex()
    {
        return $this->renderExplorer(null);
    }

    public function getFolder(int $id)
    {
        return $this->renderExplorer($id);
    }

    private function renderExplorer(?int $folderId)
    {
        $filter = (string)($this->request->getGet('type') ?? 'all'); // all|image|document
        $sort   = (string)($this->request->getGet('sort') ?? 'date_desc');

        // dossier courant
        $currentFolder = null;
        if ($folderId !== null) {
            $currentFolder = $this->folders->find($folderId);
            if (!$currentFolder) throw new PageNotFoundException('Dossier introuvable');
        }

        // dossiers enfants
        $folderQuery = $this->folders
            ->select('media_folders.*, u.firstname, u.lastname')
            ->join('`user` u', 'u.id = media_folders.user_id', 'left');

        if ($folderId === null) {
            $folderQuery->where('media_folders.parent_id IS NULL', null, false);
        } else {
            $folderQuery->where('media_folders.parent_id', $folderId);
        }

        $folders = $folderQuery
            ->orderBy('media_folders.name', 'ASC')
            ->findAll();

        // fichiers
        $fileQuery = $this->media->where('deleted_at', null);

        if ($folderId === null) $fileQuery->where('folder_id', null);
        else $fileQuery->where('folder_id', $folderId);

        if ($filter === 'image')      $fileQuery->where('type', 'image');
        elseif ($filter === 'document') $fileQuery->where('type', 'document');

        switch ($sort) {
            case 'date_asc':  $fileQuery->orderBy('created_at', 'ASC');  break;
            case 'name_asc':  $fileQuery->orderBy('name', 'ASC');        break;
            case 'name_desc': $fileQuery->orderBy('name', 'DESC');       break;
            case 'size_asc':  $fileQuery->orderBy('file_size', 'ASC');   break;
            case 'size_desc': $fileQuery->orderBy('file_size', 'DESC');  break;
            default:          $fileQuery->orderBy('created_at', 'DESC'); break;
        }

        $files = $fileQuery->findAll();

        $breadcrumbs = $this->buildBreadcrumbs($currentFolder);

        return $this->view('admin/media/index', [
            'title'         => 'Médiathèque',
            'filter'        => $filter,
            'sort'          => $sort,
            'currentFolder' => $currentFolder,
            'folders'       => $folders,
            'files'         => $files,
            'breadcrumbs'   => $breadcrumbs,
        ], true);
    }

    public function getFoldersTree()
    {
        $rows = $this->folders
            ->select('id, name, parent_id')
            ->orderBy('parent_id', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        $folders = array_map(static function ($r) {
            return [
                'id'        => (int)($r['id'] ?? 0),
                'name'      => (string)($r['name'] ?? ''),
                'parent_id' => ($r['parent_id'] !== null) ? (int)$r['parent_id'] : null,
            ];
        }, $rows ?: []);

        return $this->response->setJSON(['folders' => $folders]);
    }

    public function postCreateFolder()
    {
        $name = trim((string)$this->request->getPost('name'));
        $parentId = $this->request->getPost('parent_id');
        $parentId = is_numeric($parentId) ? (int)$parentId : null;

        if ($name === '') return redirect()->back()->with('error', 'Nom requis.');
        if (mb_strlen($name) > 150) return redirect()->back()->with('error', 'Nom trop long (150 max).');

        if ($parentId !== null) {
            $parent = $this->folders->find($parentId);
            if (!$parent) return redirect()->back()->with('error', 'Dossier parent introuvable.');
        }

        $adminId = (int)(session('user')->id ?? 0);

        $this->folders->insert([
            'name'      => $name,
            'parent_id' => $parentId,
            'user_id'   => $adminId,
        ]);

        return redirect()->back()->with('message', 'Dossier créé.');
    }

    public function postRenameFolder(int $id)
    {
        $folder = $this->folders->find($id);
        if (!$folder) return redirect()->back()->with('error', 'Dossier introuvable.');

        $name = trim((string)$this->request->getPost('name'));
        if ($name === '') return redirect()->back()->with('error', 'Nom requis.');
        if (mb_strlen($name) > 150) return redirect()->back()->with('error', 'Nom trop long (150 max).');

        $this->folders->update($id, ['name' => $name]);

        return redirect()->back()->with('message', 'Dossier renommé.');
    }

    public function postDeleteFolder(int $id)
    {
        $folder = $this->folders->find($id);
        if (!$folder) return redirect()->back()->with('error', 'Dossier introuvable.');

        $children = $this->folders->where('parent_id', $id)->countAllResults();
        if ($children > 0) return redirect()->back()->with('error', 'Dossier non vide (sous-dossiers).');

        $files = $this->media->where('folder_id', $id)->where('deleted_at', null)->countAllResults();
        if ($files > 0) return redirect()->back()->with('error', 'Dossier non vide (fichiers).');

        $this->folders->delete($id);

        return redirect()->back()->with('message', 'Dossier supprimé.');
    }

    public function postUpload()
    {
        $isAjax = $this->request->isAJAX();

        $folderId = $this->request->getPost('folder_id');
        $folderId = is_numeric($folderId) ? (int)$folderId : null;

        if ($folderId !== null) {
            $folder = $this->folders->find($folderId);
            if (!$folder) {
                if ($isAjax) return $this->response->setStatusCode(400)->setJSON(['ok'=>0,'errors'=>['Dossier cible introuvable.']]);
                return redirect()->back()->with('error', 'Dossier cible introuvable.');
            }
        }

        $uploaded = $this->request->getFiles();
        $files = $uploaded['files'] ?? ($uploaded['files[]'] ?? null);
        if ($files instanceof \CodeIgniter\HTTP\Files\UploadedFile) $files = [$files];

        if (!is_array($files) || empty($files)) {
            if ($isAjax) return $this->response->setStatusCode(400)->setJSON(['ok'=>0,'errors'=>['Aucun fichier reçu.']]);
            return redirect()->back()->with('error', 'Aucun fichier reçu.');
        }

        $ok = 0; $errors = [];

        foreach ($files as $file) {
            $result = $this->storeOneFile($file, $folderId);
            if ($result === true) $ok++;
            else $errors[] = $result;
        }

        if ($isAjax) {
            return $this->response->setStatusCode($ok ? 200 : 400)->setJSON(['ok'=>$ok,'errors'=>$errors]);
        }

        if ($ok > 0) return redirect()->back()->with('message', $ok . ' fichier(s) ajouté(s).');
        return redirect()->back()->with('error', implode("\n", $errors));
    }

    private function storeOneFile($file, ?int $folderId)
    {
        if (!$file || !$file->isValid()) return 'Fichier invalide.';
        $size = (int)$file->getSize();
        if ($size <= 0) return 'Taille invalide.';
        if ($size > 5 * 1024 * 1024) return 'Fichier > 5 Mo.';

        $mime = strtolower((string)$file->getMimeType());
        $allowed = [
            'image/jpeg','image/png','image/webp','image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
        if (!in_array($mime, $allowed, true)) return 'Type non supporté : ' . $mime;

        $type = str_starts_with($mime, 'image/') ? 'image' : 'document';

        $name = trim((string)$file->getClientName());
        if ($name === '') $name = 'fichier';

        $tmp = $file->getTempName();
        $data = ($tmp && is_file($tmp)) ? @file_get_contents($tmp) : false;
        if ($data === false) return 'Lecture impossible.';

        $db = $this->media->db;
        $db->transStart();

        $mediaId = $this->media->insert([
            'folder_id'   => $folderId,
            'name'        => $name,
            'mime_type'   => $mime,
            'file_size'   => $size,
            'type'        => $type,
            'entity_type' => null,
            'entity_id'   => null,
        ], true);

        if (!$mediaId) { $db->transRollback(); return 'Insert media KO.'; }

        $ok = $this->blob->upsertBlob((int)$mediaId, $data);
        if (!$ok) { $db->transRollback(); return 'Insert blob KO.'; }

        $db->transComplete();
        return true;
    }

    public function postMove(int $mediaId)
    {
        $m = $this->media->getById($mediaId);
        if (!$m || !empty($m['deleted_at'])) return redirect()->back()->with('error', 'Fichier introuvable.');

        $target = $this->request->getPost('target_folder_id');
        $target = is_numeric($target) ? (int)$target : null;

        if ($target !== null) {
            $folder = $this->folders->find($target);
            if (!$folder) return redirect()->back()->with('error', 'Dossier cible introuvable.');
        }

        $this->media->update($mediaId, ['folder_id' => $target]);
        return redirect()->back()->with('message', 'Fichier déplacé.');
    }

    public function postCopy(int $mediaId)
    {
        $m = $this->media->getById($mediaId);
        if (!$m || !empty($m['deleted_at'])) return redirect()->back()->with('error', 'Fichier introuvable.');

        $target = $this->request->getPost('target_folder_id');
        $target = is_numeric($target) ? (int)$target : null;

        if ($target !== null) {
            $folder = $this->folders->find($target);
            if (!$folder) return redirect()->back()->with('error', 'Dossier cible introuvable.');
        }

        $blob = $this->blob->getBlob($mediaId);
        if ($blob === null) return redirect()->back()->with('error', 'Contenu (blob) introuvable.');

        $db = $this->media->db;
        $db->transStart();

        $newId = $this->media->insert([
            'folder_id'   => $target,
            'name'        => (string)($m['name'] ?? 'copie'),
            'mime_type'   => $m['mime_type'] ?? null,
            'file_size'   => (int)($m['file_size'] ?? strlen($blob)),
            'type'        => $m['type'] ?? 'document',
            'entity_type' => null,
            'entity_id'   => null,
        ], true);

        if (!$newId) { $db->transRollback(); return redirect()->back()->with('error', 'Copie impossible (insert).'); }
        if (!$this->blob->upsertBlob((int)$newId, $blob)) { $db->transRollback(); return redirect()->back()->with('error', 'Copie impossible (blob).'); }

        $db->transComplete();
        return redirect()->back()->with('message', 'Fichier copié.');
    }

    public function postDelete(int $mediaId)
    {
        $m = $this->media->getById($mediaId);
        if (!$m || !empty($m['deleted_at'])) return redirect()->back()->with('error', 'Fichier introuvable.');

        $this->media->deleteMedia($mediaId);
        return redirect()->back()->with('message', 'Fichier supprimé.');
    }

    public function getFile(int $id)
    {
        $m = $this->media->find($id);
        if (!$m || !empty($m['deleted_at'])) throw new PageNotFoundException('Fichier introuvable');

        $blob = $this->blob->getBlob($id);
        if ($blob === null) throw new PageNotFoundException('Contenu introuvable');

        $mime = (string)($m['mime_type'] ?? 'application/octet-stream');
        $name = $this->safeFilename((string)($m['name'] ?? ('file-' . $id)));
        $inline = str_starts_with($mime, 'image/') || $mime === 'application/pdf';

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', ($inline ? 'inline' : 'attachment') . '; filename="' . $name . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($blob);
    }

    public function getDownload(int $id)
    {
        $m = $this->media->find($id);
        if (!$m || !empty($m['deleted_at'])) throw new PageNotFoundException('Fichier introuvable');

        $blob = $this->blob->getBlob($id);
        if ($blob === null) throw new PageNotFoundException('Contenu introuvable');

        $mime = (string)($m['mime_type'] ?? 'application/octet-stream');
        $name = $this->safeFilename((string)($m['name'] ?? ('file-' . $id)));

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $name . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($blob);
    }

    private function safeFilename(string $name): string
    {
        $name = trim(str_replace(["\r","\n","\0"], '', $name));
        $name = preg_replace('~[\/\\\\:\*\?"<>\|]+~', '-', $name);
        return $name !== '' ? $name : 'file';
    }

    private function buildBreadcrumbs(?array $currentFolder): array
    {
        $crumbs = [['id' => null, 'name' => 'Racine']];
        if (!$currentFolder) return $crumbs;

        $seen  = [];
        $stack = [];

        $f = $currentFolder;

        while (is_array($f) && !empty($f)) {
            $fid = (int)($f['id'] ?? 0);
            if ($fid <= 0) break;

            if (isset($seen[$fid])) break;
            $seen[$fid] = true;

            $stack[] = ['id' => $fid, 'name' => (string)($f['name'] ?? ('Dossier #' . $fid))];

            $pid = $f['parent_id'] ?? null;
            if ($pid === null) break;

            $f = $this->folders->find((int)$pid);
            if (!$f) break;
        }

        return array_merge($crumbs, array_reverse($stack));
    }
}