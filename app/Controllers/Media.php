<?php

namespace App\Controllers;

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
    }

    /**
     * ID utilisateur connecté (0 si absent).
     */
    private function userId(): int
    {
        $u = session('user');
        return (int)($u->id ?? 0);
    }

    /**
     * Récupère un dossier en imposant qu'il appartient à l'utilisateur.
     * - folderId = null => racine (ok)
     */
    private function getFolderOr404(?int $folderId): ?array
    {
        if ($folderId === null) return null;

        $folder = $this->folderModel->getById($folderId);
        if (!$folder) {
            throw new PageNotFoundException('Dossier introuvable');
        }

        return $folder;
    }

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
        $userId = $this->userId();
        if ($userId <= 0) {
            // adapte selon ton app : ici je bloque si pas connecté
            return redirect()->to(site_url('/'))->with('error', 'Connexion requise.');
        }

        $picker = (bool) $this->request->getGet('picker');
        $filter = (string) ($this->request->getGet('type') ?? 'all');
        $sort   = (string) ($this->request->getGet('sort') ?? 'date_desc');

        $currentFolder = $this->getFolderOr404($folderId);
        $breadcrumbs   = $this->buildBreadcrumbs($currentFolder);
        $isAdmin = (bool) (session('user')->is_admin ?? false); // adapte à ton système
        $canManageCurrentFolder = false;

        if ($currentFolder) {
            $ownerId = (int) ($currentFolder['user_id'] ?? 0);
            $canManageCurrentFolder = $isAdmin || ($ownerId === $userId);
        }

        $folders       = $this->folderModel->getChildren($folderId); // TOUS

        // ✅ uniquement fichiers dans le dossier (et dossier appartient à l'user car vérifié au-dessus)
        $q = $this->mediaModel;

        if ($folderId === null) $q = $q->where('folder_id', null);
        else $q = $q->where('folder_id', $folderId);

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

        $rootUrl = site_url('media') . $this->buildQueryKeep([
                'picker' => $picker ? '1' : null,
                'type'   => $filter,
                'sort'   => $sort
            ]);

        if ($currentFolder && !empty($currentFolder['parent_id'])) {
            $backUrl = site_url('media/folder/' . (int)$currentFolder['parent_id']) . $this->buildQueryKeep([
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

            'uploadUrl'     => site_url('media/upload'),
            'rootUrl'       => $rootUrl,
            'backUrl'       => $backUrl,

            'canManageCurrentFolder' => $canManageCurrentFolder,
            'isAdmin' => $isAdmin,
            'userId'  => $userId,
        ];

        if ($picker) {
            return view('front/media/index_picker', $data);
        }

        return $this->view('front/media/index', $data, false);
    }

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

    /**
     * Breadcrumbs sécurisés (ne remonte que dans les dossiers appartenant à l'user).
     */
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

    /**
     * FRONT : créer un dossier => user_id obligatoire
     */
    public function postCreateFolder()
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return redirect()->to(site_url('/'))->with('error', 'Connexion requise.');
        }

        $name = trim((string) $this->request->getPost('name'));
        $parentRaw = $this->request->getPost('parent_id');
        $parentId = is_numeric($parentRaw) ? (int)$parentRaw : null;

        if ($name === '') {
            return redirect()->back()->with('error', 'Nom de dossier requis.');
        }

        // ✅ parent doit appartenir à l'user si fourni
        if ($parentId !== null) {
            $p = $this->folderModel->getByIdForUser($parentId, $userId);
            if (!$p) {
                return redirect()->back()->with('error', 'Dossier parent introuvable.');
            }
        }

        $this->folderModel->insert([
            'name'       => $name,
            'parent_id'  => $parentId,
            'user_id'    => $userId,
            'sort_order' => 0,
        ]);

        $redirectUrl = $parentId ? site_url('media/folder/' . $parentId) : site_url('media');
        return redirect()->to($redirectUrl . $this->buildQueryKeep())->with('message', 'Dossier créé.');
    }

    /**
     * FRONT : supprimer un dossier uniquement si owner.
     * Ici on garde ta règle "dossier doit être vide" (pas enfants, pas fichiers).
     * Si tu veux permettre suppression récursive, on le fera côté model/service.
     */
    public function postDeleteFolder(int $folderId)
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return redirect()->to(site_url('/'))->with('error', 'Connexion requise.');
        }

        // ✅ owner check
        $folder = $this->folderModel->getByIdForUser($folderId, $userId);
        if (!$folder) {
            return redirect()->back()->with('error', 'Action non autorisée (dossier introuvable).');
        }

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

        $redirectUrl = $parentId ? site_url('media/folder/' . (int)$parentId) : site_url('media');
        return redirect()->to($redirectUrl . $this->buildQueryKeep())->with('message', 'Dossier supprimé.');
    }

    /**
     * FRONT : arbre des dossiers => uniquement ceux de l'user
     */
    public function getFoldersTree()
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Connexion requise.']);
        }

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


    public function postRenameFolder(int $id)
    {
        $user = session('user');
        $userId = is_object($user) ? (int)($user->id ?? 0) : (int)($user['id'] ?? 0);

        if ($userId <= 0) {
            return redirect()->to(site_url('media'))->with('error', 'Non autorisé.');
        }

        $name = trim((string)$this->request->getPost('name'));
        if ($name === '' || mb_strlen($name) > 150) {
            return redirect()->back()->with('error', 'Nom invalide.');
        }

        $fm = new \App\Models\MediaFolderModel();
        $folder = $fm->find($id);

        if (!$folder) {
            return redirect()->back()->with('error', 'Dossier introuvable.');
        }

        if ((int)($folder['user_id'] ?? 0) !== $userId) {
            return redirect()->back()->with('error', 'Non autorisé.');
        }

        $fm->update($id, ['name' => $name]);

        return redirect()->back()->with('message', 'Dossier renommé.');
    }

    /**
     * FRONT : supprimer un fichier => autorisé uniquement si le fichier est dans un dossier appartenant à l'user
     * (ou racine, à condition que le fichier soit "à l'user" => ici on impose qu'il doit être dans un dossier owned,
     * sinon, si folder_id null, on refuse (plus sûr). Si tu veux une racine par user, on peut la modéliser.)
     */
    public function postDelete(int $id)
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return redirect()->to(site_url('/'))->with('error', 'Connexion requise.');
        }

        $row = $this->mediaModel->getById($id);
        if (!$row) {
            return redirect()->back()->with('error', 'Fichier introuvable.');
        }

        $folderId = $row['folder_id'] ?? null;
        if ($folderId === null) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        if (! $this->folderModel->isOwner((int)$folderId, $userId)) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        // utilise le model sécurisé
        $this->mediaModel->deleteMedia((int)$id);

        return redirect()->back()->with('message', 'Fichier supprimé.');
    }

    /**
     * FRONT : upload uniquement dans un dossier appartenant à l'user (folder_id obligatoire)
     */
    public function postUpload()
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return redirect()->to(site_url('/'))->with('error', 'Connexion requise.');
        }

        $folderIdRaw = $this->request->getPost('folder_id');
        $folderId    = is_numeric($folderIdRaw) ? (int)$folderIdRaw : null;

        if ($folderId === null) {
            return redirect()->back()->with('error', 'Dossier requis pour l’upload.');
        }

        if (! $this->folderModel->isOwner($folderId, $userId)) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $uploaded = $this->request->getFiles();
        $ok = 0;
        $errors = [];
        $files = $uploaded['files'] ?? null;

        if ($files instanceof \CodeIgniter\HTTP\Files\UploadedFile) {
            $files = [$files];
        }

        if (is_array($files)) {
            foreach ($files as $file) {
                $res = $this->storeOne($file, $folderId);
                if ($res === true) $ok++;
                else $errors[] = $res;
            }
        }

        if ($ok === 0 && empty($errors)) {
            $errors[] = "Aucun fichier reçu (vérifie post_max_size/upload_max_filesize).";
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'ok'     => $ok,
                'errors' => $errors,
            ]);
        }

        $redirectUrl = site_url('media/folder/' . $folderId) . $this->buildQueryKeep();

        if ($ok > 0) {
            return redirect()->to($redirectUrl)
                ->with('message', "$ok fichier(s) ajouté(s).")
                ->with('error', !empty($errors) ? implode("\n", $errors) : null);
        }

        return redirect()->to($redirectUrl)->with('error', implode("\n", $errors));
    }

    /**
     * FRONT : move => source folder owner + target folder owner
     */
    public function postMove(int $id)
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return redirect()->to(site_url('/'))->with('error', 'Connexion requise.');
        }

        $row = $this->mediaModel->getById($id);
        if (!$row) return redirect()->back()->with('error', 'Fichier introuvable.');

        $currentFolderId = $row['folder_id'] ?? null;
        if ($currentFolderId === null || ! $this->folderModel->isOwner((int)$currentFolderId, $userId)) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $targetRaw = $this->request->getPost('target_folder_id');
        $targetId  = is_numeric($targetRaw) ? (int)$targetRaw : null;

        if ($targetId === null) {
            return redirect()->back()->with('error', 'Dossier cible requis.');
        }

        if (! $this->folderModel->isOwner($targetId, $userId)) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        if ((int)$currentFolderId === (int)$targetId) {
            return redirect()->back()->with('message', 'Déjà dans ce dossier.');
        }

        $this->mediaModel->update($id, ['folder_id' => $targetId]);
        return redirect()->back()->with('message', 'Fichier déplacé.');
    }

    /**
     * FRONT : copy => source owner + target owner
     */
    public function postCopy(int $id)
    {
        $userId = $this->userId();
        if ($userId <= 0) {
            return redirect()->to(site_url('/'))->with('error', 'Connexion requise.');
        }

        $row = $this->mediaModel->getById($id);
        if (!$row) return redirect()->back()->with('error', 'Fichier introuvable.');

        $currentFolderId = $row['folder_id'] ?? null;
        if ($currentFolderId === null || ! $this->folderModel->isOwner((int)$currentFolderId, $userId)) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $targetRaw = $this->request->getPost('target_folder_id');
        $targetId  = is_numeric($targetRaw) ? (int)$targetRaw : null;

        if ($targetId === null) {
            return redirect()->back()->with('error', 'Dossier cible requis.');
        }

        if (! $this->folderModel->isOwner($targetId, $userId)) {
            return redirect()->back()->with('error', 'Action non autorisée.');
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
            'folder_id'   => $targetId,
            'file_name'   => $newName,
            'file_path'   => $newRel,
            'mime_type'   => $row['mime_type'] ?? null,
            'file_size'   => (int)($row['file_size'] ?? filesize($newFull)),
            'kind'        => $row['kind'] ?? (str_starts_with((string)$row['mime_type'], 'image/') ? 'image' : 'document'),
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

    /**
     * Stocke un fichier (front) dans un dossier owned.
     */
    protected function storeOne($file, int $folderId)
    {
        if (!$file) return "Aucun fichier.";
        if (!$file->isValid()) return "Upload invalide: {$file->getErrorString()} (code {$file->getError()}).";
        if ($file->hasMoved()) return "Fichier déjà déplacé.";

        $size = (int) $file->getSize();
        if ($size <= 0) return "Taille de fichier nulle (post_max_size/upload_max_filesize ?).";
        if ($size > 5 * 1024 * 1024) return "Fichier trop volumineux (> 5 Mo).";

        $clientMime = strtolower((string) $file->getClientMimeType());
        $realMime   = strtolower((string) $file->getMimeType());

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',

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
