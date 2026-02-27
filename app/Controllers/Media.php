<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MediaModel;
use App\Models\MediaBlobModel;
use App\Models\MediaFolderModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Media extends BaseController
{
    protected MediaModel $media;
    protected MediaBlobModel $blob;
    protected MediaFolderModel $folders;

    public function __construct()
    {
        $this->media   = new MediaModel();
        $this->blob    = new MediaBlobModel();
        $this->folders = new MediaFolderModel();
    }

    private function userId(): int
    {
        $u = session('user');
        return (int)($u->id ?? 0);
    }

    private function ensureAuth(): int
    {
        $id = $this->userId();
        if ($id <= 0) {
            redirect()->to(site_url('/'))->with('error', 'Connexion requise.')->send();
            exit;
        }
        return $id;
    }

    // ============================
    // GET: /media
    // - support ?folder=ID (utile pour le picker)
    // ============================
    public function getIndex()
    {
        $folderId = $this->request->getGet('folder');
        $folderId = is_numeric($folderId) ? (int)$folderId : null;

        return $this->renderExplorer($folderId);
    }

    // ============================
    // GET: /media/folder/{id}
    // ============================
    public function getFolder(int $id)
    {
        return $this->renderExplorer($id);
    }

    private function renderExplorer(?int $folderId)
    {
        $userId = $this->ensureAuth();

        $picker = ((int)($this->request->getGet('picker') ?? 0)) === 1;

        $filter = (string)($this->request->getGet('type') ?? 'all'); // all|image|document
        $sort   = (string)($this->request->getGet('sort') ?? 'date_desc');

        // dossier courant (lisible par tous)
        $currentFolder = null;
        if ($folderId !== null) {
            $currentFolder = $this->folders->find($folderId);
            if (!$currentFolder) throw new PageNotFoundException('Dossier introuvable');

            $currentFolder['is_owner'] = ((int)($currentFolder['user_id'] ?? 0) === $userId);
        }

        $breadcrumbs = $this->buildBreadcrumbs($currentFolder);

        // ---------- Dossiers enfants : TOUT visible
        $foldersQ = $this->folders
            ->select('media_folders.*, u.firstname, u.lastname')
            ->join('`user` u', 'u.id = media_folders.user_id', 'left');

        if ($folderId === null) $foldersQ->where('media_folders.parent_id IS NULL', null, false);
        else $foldersQ->where('media_folders.parent_id', $folderId);

        $folders = $foldersQ->orderBy('media_folders.name', 'ASC')->findAll();

        foreach ($folders as &$d) {
            $d['is_owner'] = ((int)($d['user_id'] ?? 0) === $userId);
        }
        unset($d);

        // ---------- Fichiers : TOUT visible
        $filesQ = $this->media
            ->select('media.*, f.user_id AS folder_user_id')
            ->join('media_folders f', 'f.id = media.folder_id', 'left')
            ->where('media.deleted_at', null);

        if ($folderId === null) $filesQ->where('media.folder_id', null);
        else $filesQ->where('media.folder_id', $folderId);

        if ($filter === 'image') $filesQ->where('media.type', 'image');
        elseif ($filter === 'document') $filesQ->where('media.type', 'document');

        switch ($sort) {
            case 'date_asc':  $filesQ->orderBy('media.created_at', 'ASC');  break;
            case 'name_asc':  $filesQ->orderBy('media.name', 'ASC');        break;
            case 'name_desc': $filesQ->orderBy('media.name', 'DESC');       break;
            case 'size_asc':  $filesQ->orderBy('media.file_size', 'ASC');   break;
            case 'size_desc': $filesQ->orderBy('media.file_size', 'DESC');  break;
            default:          $filesQ->orderBy('media.created_at', 'DESC'); break;
        }

        $files = $filesQ->findAll();

        foreach ($files as &$f) {
            $f['is_owner'] = $this->isOwnerFileRow($f, $userId);
        }
        unset($f);

        // actions autorisées “dans ce dossier”
        $canWriteHere = ($folderId === null)
            ? true
            : (!empty($currentFolder) && !empty($currentFolder['is_owner']));

        $data = [
            'filter'        => $filter,
            'sort'          => $sort,
            'currentFolder' => $currentFolder,
            'breadcrumbs'   => $breadcrumbs,
            'folders'       => $folders,
            'files'         => $files,
            'userId'        => $userId,
            'canWriteHere'  => $canWriteHere,
            'isPicker'      => $picker,
        ];

        if ($picker) {
            return $this->response->setBody(
                view('front/media/index_picker', $data)
            );
        }

        return $this->view('front/media/index', $data, false);
    }

    // ============================
    // JSON: /media/folders-tree
    // ============================
    public function getFoldersTree()
    {
        $userId = $this->ensureAuth();

        $rows = $this->folders
            ->select('id, name, parent_id')
            ->where('user_id', $userId)
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

    // ============================
    // POST: /media/folder/create
    // ============================
    public function postCreateFolder()
    {
        $userId = $this->ensureAuth();

        $name = trim((string)$this->request->getPost('name'));
        $parentId = $this->request->getPost('parent_id');
        $parentId = is_numeric($parentId) ? (int)$parentId : null;

        if ($name === '') return redirect()->back()->with('error', 'Nom requis.');
        if (mb_strlen($name) > 150) return redirect()->back()->with('error', 'Nom trop long (150 max).');

        if ($parentId !== null) {
            $p = $this->folders->find($parentId);
            if (!$p || (int)($p['user_id'] ?? 0) !== $userId) {
                return redirect()->back()->with('error', 'Dossier parent invalide.');
            }
        }

        $this->folders->insert([
            'name'      => $name,
            'parent_id' => $parentId,
            'user_id'   => $userId,
        ]);

        return redirect()->back()->with('message', 'Dossier créé.');
    }

    public function postRenameFolder(int $id)
    {
        $userId = $this->ensureAuth();

        $name = trim((string)$this->request->getPost('name'));
        if ($name === '' || mb_strlen($name) > 150) {
            return redirect()->back()->with('error', 'Nom invalide.');
        }

        $folder = $this->folders->find($id);
        if (!$folder || (int)($folder['user_id'] ?? 0) !== $userId) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $this->folders->update($id, ['name' => $name]);
        return redirect()->back()->with('message', 'Dossier renommé.');
    }

    public function postDeleteFolder(int $id)
    {
        $userId = $this->ensureAuth();

        $folder = $this->folders->find($id);
        if (!$folder || (int)($folder['user_id'] ?? 0) !== $userId) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $hasChildren = $this->folders->where('parent_id', $id)->countAllResults() > 0;
        if ($hasChildren) return redirect()->back()->with('error', 'Dossier non vide (sous-dossiers).');

        $hasFiles = $this->media->where('folder_id', $id)->where('deleted_at', null)->countAllResults() > 0;
        if ($hasFiles) return redirect()->back()->with('error', 'Dossier non vide (fichiers).');

        $this->folders->delete($id);
        return redirect()->back()->with('message', 'Dossier supprimé.');
    }

    // ============================
    // POST: /media/upload
    // ============================
    public function postUpload()
    {
        $userId = $this->ensureAuth();
        $isAjax = $this->request->isAJAX();

        $folderId = $this->request->getPost('folder_id');
        $folderId = is_numeric($folderId) ? (int)$folderId : null;

        if ($folderId !== null) {
            $folder = $this->folders->find($folderId);
            if (!$folder || (int)($folder['user_id'] ?? 0) !== $userId) {
                if ($isAjax) return $this->response->setStatusCode(403)->setJSON(['ok'=>0,'errors'=>['Dossier invalide.']]);
                return redirect()->back()->with('error', 'Dossier invalide.');
            }
        }

        $uploaded = $this->request->getFiles();
        $files = $uploaded['files'] ?? ($uploaded['files[]'] ?? null);
        if ($files instanceof \CodeIgniter\HTTP\Files\UploadedFile) $files = [$files];

        $ok = 0; $errors = [];

        if (is_array($files)) {
            foreach ($files as $file) {
                $res = $this->storeOne($file, $userId, $folderId);
                if ($res === true) $ok++;
                else $errors[] = $res;
            }
        }

        if ($isAjax) return $this->response->setStatusCode($ok ? 200 : 400)->setJSON(['ok'=>$ok,'errors'=>$errors]);

        return redirect()->back()
            ->with($ok ? 'message' : 'error', $ok ? "$ok fichier(s) ajouté(s)." : implode("\n", $errors));
    }

    private function storeOne($file, int $userId, ?int $folderId)
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
        if (!in_array($mime, $allowed, true)) return 'Type non supporté.';

        $type = str_starts_with($mime, 'image/') ? 'image' : 'document';
        $name = trim((string)$file->getClientName());
        if ($name === '') $name = 'fichier';

        $tmp = $file->getTempName();
        $data = $tmp && is_file($tmp) ? @file_get_contents($tmp) : false;
        if ($data === false) return 'Lecture upload impossible.';

        $db = $this->media->db;
        $db->transStart();

        $newId = $this->media->insert([
            'folder_id'   => $folderId,
            'name'        => $name,
            'mime_type'   => $mime,
            'file_size'   => $size,
            'type'        => $type,
            'entity_type' => 'user',
            'entity_id'   => $userId,
        ], true);

        if (!$newId) { $db->transRollback(); return 'Insert media KO.'; }
        if (!$this->blob->upsertBlob((int)$newId, $data)) { $db->transRollback(); return 'Insert blob KO.'; }

        $db->transComplete();
        return true;
    }

    public function postDelete(int $id)
    {
        $userId = $this->ensureAuth();
        $m = $this->getFileRowWithFolderOwner($id);
        if (!$m || !$this->isOwnerFileRow($m, $userId)) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $this->media->deleteMedia($id);
        return redirect()->back()->with('message', 'Fichier supprimé.');
    }

    public function postMove(int $id)
    {
        $userId = $this->ensureAuth();
        $m = $this->getFileRowWithFolderOwner($id);
        if (!$m || !$this->isOwnerFileRow($m, $userId)) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $target = $this->request->getPost('target_folder_id');
        $target = is_numeric($target) ? (int)$target : null;

        if ($target !== null) {
            $folder = $this->folders->find($target);
            if (!$folder || (int)($folder['user_id'] ?? 0) !== $userId) {
                return redirect()->back()->with('error', 'Dossier cible invalide.');
            }
        }

        $this->media->update($id, ['folder_id' => $target]);
        return redirect()->back()->with('message', 'Déplacé.');
    }

    public function postCopy(int $id)
    {
        $userId = $this->ensureAuth();
        $m = $this->getFileRowWithFolderOwner($id);
        if (!$m || !$this->isOwnerFileRow($m, $userId)) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $target = $this->request->getPost('target_folder_id');
        $target = is_numeric($target) ? (int)$target : null;

        if ($target !== null) {
            $folder = $this->folders->find($target);
            if (!$folder || (int)($folder['user_id'] ?? 0) !== $userId) {
                return redirect()->back()->with('error', 'Dossier cible invalide.');
            }
        }

        $blob = $this->blob->getBlob($id);
        if ($blob === null) return redirect()->back()->with('error', 'Blob introuvable.');

        $newId = $this->media->insert([
            'folder_id'   => $target,
            'name'        => (string)($m['name'] ?? 'copie'),
            'mime_type'   => $m['mime_type'] ?? null,
            'file_size'   => (int)($m['file_size'] ?? strlen($blob)),
            'type'        => $m['type'] ?? 'document',
            'entity_type' => 'user',
            'entity_id'   => $userId,
        ], true);

        if (!$newId) return redirect()->back()->with('error', 'Copie impossible (insert).');
        if (!$this->blob->upsertBlob((int)$newId, $blob)) return redirect()->back()->with('error', 'Copie impossible (blob).');

        return redirect()->back()->with('message', 'Copié.');
    }

    public function getFile(int $id)
    {
        $this->ensureAuth();

        $m = $this->media->find($id);
        if (!$m || !empty($m['deleted_at'])) throw new PageNotFoundException('Fichier introuvable');

        $blob = $this->blob->getBlob($id);
        if ($blob === null) throw new PageNotFoundException('Contenu introuvable');

        $mime = (string)($m['mime_type'] ?? 'application/octet-stream');
        $name = (string)($m['name'] ?? ('file-' . $id));
        $inline = str_starts_with($mime, 'image/') || $mime === 'application/pdf';

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', ($inline ? 'inline' : 'attachment') . '; filename="' . $this->safeFilename($name) . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($blob);
    }

    public function getDownload(int $id)
    {
        $this->ensureAuth();

        $m = $this->media->find($id);
        if (!$m || !empty($m['deleted_at'])) throw new PageNotFoundException('Fichier introuvable');

        $blob = $this->blob->getBlob($id);
        if ($blob === null) throw new PageNotFoundException('Contenu introuvable');

        $mime = (string)($m['mime_type'] ?? 'application/octet-stream');
        $name = (string)($m['name'] ?? ('file-' . $id));

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $this->safeFilename($name) . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($blob);
    }

    private function getFileRowWithFolderOwner(int $id): ?array
    {
        return $this->media
            ->select('media.*, f.user_id AS folder_user_id')
            ->join('media_folders f', 'f.id = media.folder_id', 'left')
            ->where('media.id', $id)
            ->where('media.deleted_at', null)
            ->get()
            ->getRowArray();
    }

    private function isOwnerFileRow(array $row, int $userId): bool
    {
        if (!empty($row['folder_id'])) {
            return ((int)($row['folder_user_id'] ?? 0) === $userId);
        }

        return (($row['entity_type'] ?? null) === 'user' && (int)($row['entity_id'] ?? 0) === $userId);
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