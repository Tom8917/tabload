<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MediaFolderModel;
use App\Models\MediaModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Media extends BaseController
{
    protected string $uploadPath;
    protected MediaModel $mediaModel;
    protected MediaFolderModel $folderModel;

    public function __construct()
    {
        $this->uploadPath = FCPATH . 'uploads/media/';
        if (!is_dir($this->uploadPath)) {
            @mkdir($this->uploadPath, 0775, true);
        }

        $this->mediaModel  = new MediaModel();
        $this->folderModel = new MediaFolderModel();
        $this->title = 'Médias';
        $this->menu  = 'media';
    }

    // ------------------------------------------------------------
    // Explorer
    // ------------------------------------------------------------

    public function getIndex()
    {
        return $this->renderExplorer(null);
    }

    public function getFolder(int $folderId)
    {
        return $this->renderExplorer($folderId);
    }

    private function renderExplorer(?int $folderId)
    {
        $picker = (bool) $this->request->getGet('picker');
        $filter = (string) ($this->request->getGet('type') ?? 'all');        // all|image|document
        $sort   = (string) ($this->request->getGet('sort') ?? 'date_desc');  // date_desc...

        $currentFolder = null;
        if ($folderId !== null) {
            $currentFolder = $this->folderModel->getById($folderId);
            if (!$currentFolder) {
                throw new PageNotFoundException('Dossier introuvable');
            }
        }

        $breadcrumbs = $this->buildBreadcrumbs($currentFolder);
        $folders     = $this->folderModel->getChildren($folderId);

        $q = $this->mediaModel;
        $q = ($folderId === null) ? $q->where('folder_id', null) : $q->where('folder_id', $folderId);

        if ($filter === 'image') {
            $q = $q->where('kind', 'image');
        } elseif ($filter === 'document') {
            $q = $q->where('kind', 'document');
        }

        switch ($sort) {
            case 'date_asc':  $q = $q->orderBy('created_at', 'ASC'); break;
            case 'name_asc':  $q = $q->orderBy('file_name', 'ASC'); break;
            case 'name_desc': $q = $q->orderBy('file_name', 'DESC'); break;
            case 'size_asc':  $q = $q->orderBy('file_size', 'ASC'); break;
            case 'size_desc': $q = $q->orderBy('file_size', 'DESC'); break;
            default:          $q = $q->orderBy('created_at', 'DESC'); break;
        }

        $files = $q->findAll();

        $rootUrl = site_url('admin/media') . $this->buildQueryKeep([
                'picker' => $picker ? '1' : null,
                'type'   => $filter,
                'sort'   => $sort
            ]);

        if ($currentFolder && !empty($currentFolder['parent_id'])) {
            $backUrl = site_url('admin/media/folder/' . (int)$currentFolder['parent_id']) . $this->buildQueryKeep([
                    'picker' => $picker ? '1' : null,
                    'type'   => $filter,
                    'sort'   => $sort
                ]);
        } else {
            $backUrl = $rootUrl;
        }

        $data = [
            'picker'        => $picker,
            'filter'        => $filter,
            'sort'          => $sort,
            'currentFolder' => $currentFolder,
            'breadcrumbs'   => $breadcrumbs,
            'folders'       => $folders,
            'files'         => $files,

            'uploadUrl'     => site_url('admin/media/upload'),
            'rootUrl'       => $rootUrl,
            'backUrl'       => $backUrl,
        ];

        if ($picker) {
            return view('admin/media/index_picker', $data);
        }

        return $this->view('admin/media/index', $data, true);
    }

    /**
     * Conserve les query params courants et applique des overrides.
     * - si valeur null => supprime la clé.
     */
    private function buildQueryKeep(array $override = []): string
    {
        $qs = [];
        parse_str((string)($this->request->getServer('QUERY_STRING') ?? ''), $qs);

        foreach ($override as $k => $v) {
            if ($v === null) unset($qs[$k]);
            else $qs[$k] = $v;
        }

        $q = http_build_query($qs);
        return $q ? ('?' . $q) : '';
    }

    private function buildBreadcrumbs(?array $currentFolder): array
    {
        $trail = [['id' => null, 'name' => 'Racine']];

        if (!$currentFolder) return $trail;

        $stack = [];
        $node = $currentFolder;

        $guard = 0;
        while ($node) {
            $stack[] = ['id' => (int)$node['id'], 'name' => (string)$node['name']];
            $pid = $node['parent_id'] ?? null;
            if (!$pid) break;
            $node = $this->folderModel->getById((int)$pid);
            $guard++;
            if ($guard > 50) break;
        }

        $stack = array_reverse($stack);
        return array_merge($trail, $stack);
    }

    // ------------------------------------------------------------
    // Folders
    // ------------------------------------------------------------

    public function postCreateFolder()
    {
        $name = trim((string) $this->request->getPost('name'));
        $parentRaw = $this->request->getPost('parent_id');
        $parentId = is_numeric($parentRaw) ? (int)$parentRaw : null;

        if ($name === '') {
            return redirect()->back()->with('error', 'Nom de dossier requis.');
        }

        if ($parentId !== null) {
            $p = $this->folderModel->getById($parentId);
            if (!$p) return redirect()->back()->with('error', 'Dossier parent introuvable.');
        }

        $this->folderModel->insert([
            'name'       => $name,
            'parent_id'  => $parentId,
            'sort_order' => 0,
        ]);

        // reste dans le dossier courant
        $redirectUrl = $parentId ? site_url('admin/media/folder/' . $parentId) : site_url('admin/media');
        return redirect()->to($redirectUrl . $this->buildQueryKeep())->with('message', 'Dossier créé.');
    }

    public function postDeleteFolder(int $folderId)
    {
        $folder = $this->folderModel->getById($folderId);
        if (!$folder) return redirect()->back()->with('error', 'Dossier introuvable.');

        $children = $this->folderModel->where('parent_id', $folderId)->countAllResults();
        if ($children > 0) {
            return redirect()->back()->with('error', 'Dossier non vide : il contient des sous-dossiers.');
        }

        $files = $this->mediaModel->where('folder_id', $folderId)->countAllResults();
        if ($files > 0) {
            return redirect()->back()->with('error', 'Dossier non vide : il contient des fichiers.');
        }

        $parentId = $folder['parent_id'] ?? null;

        $this->folderModel->delete($folderId);

        $redirectUrl = $parentId ? site_url('admin/media/folder/' . (int)$parentId) : site_url('admin/media');
        return redirect()->to($redirectUrl . $this->buildQueryKeep())->with('message', 'Dossier supprimé.');
    }

    public function getFoldersTree()
    {
        $rows = $this->folderModel
            ->select('id, name, parent_id')
            ->orderBy('parent_id', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'folders' => array_map(fn($r) => [
                'id'        => (int)$r['id'],
                'name'      => (string)$r['name'],
                'parent_id' => $r['parent_id'] !== null ? (int)$r['parent_id'] : null,
            ], $rows)
        ]);
    }

    // ------------------------------------------------------------
    // Files
    // ------------------------------------------------------------

    public function postDelete(int $id)
    {
        $row = $this->mediaModel->getById($id);
        if (!$row) {
            return redirect()->back()->with('error', 'Fichier introuvable.');
        }

        $full = FCPATH . ltrim((string)$row['file_path'], '/');
        if (is_file($full)) {
            @unlink($full);
        }

        $this->mediaModel->delete($id);
        return redirect()->back()->with('message', 'Fichier supprimé.');
    }

    public function postUpload()
    {
        $uploaded = $this->request->getFiles();
        $ok = 0;
        $errors = [];

        if (isset($uploaded['files'])) {
            foreach ($uploaded['files'] as $file) {
                $res = $this->storeOne($file);
                if ($res === true) $ok++;
                else $errors[] = $res;
            }
        }

        if ($ok === 0 && empty($errors)) {
            $errors[] = "Aucun fichier reçu (vérifie post_max_size/upload_max_filesize).";
        }

        $folderIdRaw = $this->request->getPost('folder_id');
        $folderId    = is_numeric($folderIdRaw) ? (int)$folderIdRaw : null;

        $redirectUrl = $folderId ? site_url('admin/media/folder/' . $folderId) : site_url('admin/media');

        // conserve picker/type/sort si présent
        $redirectUrl .= $this->buildQueryKeep();

        if ($ok > 0) {
            return redirect()->to($redirectUrl)
                ->with('message', "$ok fichier(s) ajouté(s).")
                ->with('error', !empty($errors) ? implode("\n", $errors) : null);
        }

        return redirect()->to($redirectUrl)->with('error', implode("\n", $errors));
    }

    public function postMove(int $id)
    {
        $row = $this->mediaModel->getById($id);
        if (!$row) return redirect()->back()->with('error', 'Fichier introuvable.');

        $targetRaw = $this->request->getPost('target_folder_id');
        $targetId  = is_numeric($targetRaw) ? (int)$targetRaw : null;

        if ($targetId !== null && !$this->folderModel->getById($targetId)) {
            return redirect()->back()->with('error', 'Dossier cible introuvable.');
        }

        $currentId = $row['folder_id'] ?? null;
        if (($currentId === null && $targetId === null) || ((int)$currentId === (int)$targetId)) {
            return redirect()->back()->with('message', 'Déjà dans ce dossier.');
        }

        $this->mediaModel->update($id, ['folder_id' => $targetId]);
        return redirect()->back()->with('message', 'Fichier déplacé.');
    }

    public function postCopy(int $id)
    {
        $row = $this->mediaModel->getById($id);
        if (!$row) return redirect()->back()->with('error', 'Fichier introuvable.');

        $targetRaw = $this->request->getPost('target_folder_id');
        $targetId  = is_numeric($targetRaw) ? (int)$targetRaw : null;

        if ($targetId !== null && !$this->folderModel->getById($targetId)) {
            return redirect()->back()->with('error', 'Dossier cible introuvable.');
        }

        $srcRel  = (string)$row['file_path'];
        $srcFull = FCPATH . ltrim($srcRel, '/');
        if (!is_file($srcFull)) {
            return redirect()->back()->with('error', 'Fichier physique introuvable.');
        }

        $srcName = (string)$row['file_name'];
        $base    = pathinfo($srcName, PATHINFO_FILENAME);
        $ext     = pathinfo($srcName, PATHINFO_EXTENSION);
        $candidate = $base . '-copy' . ($ext ? '.'.$ext : '');

        $newName = $this->uniqueFilename($this->uploadPath, $candidate);
        $newFull = $this->uploadPath . $newName;

        if (!@copy($srcFull, $newFull)) {
            return redirect()->back()->with('error', 'Impossible de copier le fichier sur le disque.');
        }

        $newRel = 'uploads/media/' . $newName;

        $data = [
            'folder_id' => $targetId,
            'file_name' => $newName,
            'file_path' => $newRel,
            'mime_type' => $row['mime_type'] ?? null,
            'file_size' => (int)($row['file_size'] ?? filesize($newFull)),
            'kind'      => $row['kind'] ?? (str_starts_with((string)$row['mime_type'], 'image/') ? 'image' : 'document'),
            'entity_type' => null,
            'entity_id'   => null,
        ];

        if (!$this->mediaModel->insert($data)) {
            @unlink($newFull);
            $errs = $this->mediaModel->errors();
            return redirect()->back()->with('error', 'Copie OK mais insert BDD KO: ' . (!empty($errs) ? implode(' | ', $errs) : 'erreur inconnue'));
        }

        return redirect()->back()->with('message', 'Fichier copié.');
    }

    // ------------------------------------------------------------
    // Upload helpers
    // ------------------------------------------------------------

    /**
     * @param \CodeIgniter\HTTP\Files\UploadedFile|null $file
     * @return true|string
     */
    protected function storeOne($file)
    {
        if (!$file) return "Aucun fichier.";
        if (!$file->isValid()) return "Upload invalide: {$file->getErrorString()} (code {$file->getError()}).";
        if ($file->hasMoved()) return "Fichier déjà déplacé.";

        $size = (int) $file->getSize();
        if ($size <= 0) return "Taille de fichier nulle (post_max_size/upload_max_filesize ?).";
        if ($size > 4 * 1024 * 1024) return "Fichier trop volumineux (> 4 Mo).";

        $clientMime = strtolower((string) $file->getClientMimeType());
        $realMime   = strtolower((string) $file->getMimeType());

        $allowedMimes = [
            // images
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',

            // docs
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        ];

        $targetExt = $allowedMimes[$realMime] ?? ($allowedMimes[$clientMime] ?? null);
        if ($targetExt === null) {
            if (in_array($realMime, ['image/heic','image/heif','image/x-heic','image/x-heif'], true)) {
                return "HEIC/HEIF non supporté. Convertis en JPG/PNG avant upload.";
            }
            return "Type non supporté (MIME client: {$clientMime}, réel: {$realMime}).";
        }

        $original = $file->getClientName();
        $base     = pathinfo($original, PATHINFO_FILENAME);
        $origExt  = strtolower(pathinfo($original, PATHINFO_EXTENSION));

        $safeBase = $this->slugifyFilename($base);
        if ($safeBase === '') $safeBase = 'fichier';

        $finalExt = $targetExt;
        if ($targetExt === 'jpg' && in_array($origExt, ['jpg','jpeg'], true)) $finalExt = $origExt;
        if ($targetExt === 'png' && $origExt === 'png') $finalExt = 'png';
        if ($targetExt === 'webp' && $origExt === 'webp') $finalExt = 'webp';
        if ($targetExt === 'gif' && $origExt === 'gif') $finalExt = 'gif';
        if ($targetExt === 'pdf' && $origExt === 'pdf') $finalExt = 'pdf';

        $finalName = $this->uniqueFilename($this->uploadPath, $safeBase . '.' . $finalExt);

        if (!$file->move($this->uploadPath, $finalName)) {
            $errStr = method_exists($file, 'getErrorString') ? $file->getErrorString() : 'erreur inconnue';
            $err    = method_exists($file, 'getError') ? $file->getError() : 0;
            return "Échec déplacement ({$errStr}, code {$err}).";
        }

        $relativePath = 'uploads/media/' . $finalName;

        $folderIdRaw = $this->request->getPost('folder_id');
        $folderId    = is_numeric($folderIdRaw) ? (int) $folderIdRaw : null;

        $mimeForKind = $realMime ?: $clientMime;
        $kind = str_starts_with($mimeForKind, 'image/') ? 'image' : 'document';

        $data = [
            'folder_id' => $folderId,
            'file_name' => $finalName,
            'file_path' => $relativePath,
            'mime_type' => $mimeForKind,
            'file_size' => $size,
            'kind'      => $kind,
        ];

        if (!$this->mediaModel->insert($data)) {
            @unlink($this->uploadPath . $finalName);
            $errs = $this->mediaModel->errors();
            return "Upload OK mais insert BDD KO: " . (!empty($errs) ? implode(' | ', $errs) : 'erreur inconnue');
        }

        return true;
    }

    private function slugifyFilename(string $name): string
    {
        if (function_exists('iconv')) $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        $name = strtolower($name);
        $name = preg_replace('~[^a-z0-9-_\.]+~', '-', $name);
        $name = preg_replace('~-+~', '-', $name);
        $name = trim($name, '-_.');
        if (strlen($name) > 120) $name = substr($name, 0, 120);
        $reserved = ['con','prn','aux','nul','com1','lpt1'];
        if (in_array($name, $reserved, true)) $name .= '-file';
        return $name;
    }

    private function uniqueFilename(string $dir, string $filename): string
    {
        $dir = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $ext  = pathinfo($filename, PATHINFO_EXTENSION);

        $candidate = $filename;
        $i = 1;
        while (is_file($dir . $candidate)) {
            $candidate = $base . '-' . $i . ($ext ? '.' . $ext : '');
            $i++;
            if ($i > 5000) {
                $candidate = $base . '-' . uniqid() . ($ext ? '.' . $ext : '');
                break;
            }
        }
        return $candidate;
    }
}
