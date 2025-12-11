<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Media extends BaseController
{
    protected string $uploadPath;

    public function __construct()
    {
        $this->uploadPath = FCPATH . 'uploads/media/';
        if (!is_dir($this->uploadPath)) {
            @mkdir($this->uploadPath, 0775, true);
        }
    }

    public function getIndex()
    {
        $files = [];
        if (is_dir($this->uploadPath)) {
            foreach (new \DirectoryIterator($this->uploadPath) as $fileinfo) {
                if ($fileinfo->isFile()) {
                    $ext = strtolower($fileinfo->getExtension());
                    if (in_array($ext, ['jpg','jpeg','png','webp','gif','pdf'], true)) {
                        $files[] = [
                            'name'  => $fileinfo->getFilename(),
                            'url'   => base_url('uploads/media/' . $fileinfo->getFilename()),
                            'size'  => $fileinfo->getSize(),
                            'mtime' => $fileinfo->getMTime(),
                        ];
                    }
                }
            }
        }

        usort($files, fn($a, $b) => $b['mtime'] <=> $a['mtime']);

        return $this->view('admin/media/index', ['files' => $files], true);
    }

    public function getList()
    {
        $dir = FCPATH . 'uploads/media/';
        $base = rtrim(base_url(), '/');
        $files = [];

        if (is_dir($dir)) {
            foreach (new \DirectoryIterator($dir) as $fi) {
                if ($fi->isFile()) {
                    $ext = strtolower($fi->getExtension());
                    if (in_array($ext, ['jpg','jpeg','png','webp','gif','pdf'], true)) {
                        $name = $fi->getFilename();
                        $rel  = 'uploads/media/' . $name;
                        $url  = $base . '/' . $rel;
                        $files[] = [
                            'name'  => $name,
                            'rel'   => $rel,
                            'url'   => $url,
                            'size'  => $fi->getSize(),
                            'mtime' => $fi->getMTime(),
                        ];
                    }
                }
            }
        }

        usort($files, fn($a,$b) => $b['mtime'] <=> $a['mtime']);

        return $this->response->setJSON(['files' => $files]);
    }

    public function getDelete(string $file)
    {
        $file = basename($file);
        $full = $this->uploadPath . $file;

        if (is_file($full)) {
            @unlink($full);
            return redirect()->back()->with('message','Image supprimée');
        }

        return redirect()->back()->with('error','Fichier introuvable');
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

        if (isset($uploaded['image_file'])) {
            $res = $this->storeOne($uploaded['image_file']);
            if ($res === true) $ok++;
            else $errors[] = $res;
        }

        if ($ok === 0 && empty($errors)) {
            $errors[] = "Aucun fichier reçu (vérifie la limite PHP post_max_size/upload_max_filesize).";
        }

        if ($ok > 0) {
            return redirect()->to(site_url('admin/media'))
                ->with('message', "$ok fichier(s) ajouté(s).")
                ->with('error', !empty($errors) ? implode("\n", $errors) : null);
        }

        return redirect()->back()->with('error', implode("\n", $errors));
    }

    /**
     * @param \CodeIgniter\HTTP\Files\UploadedFile|null $file
     * @return true|string  true si OK, sinon message d'erreur
     */
    protected function storeOne($file)
    {
        if (!$file) {
            return "Aucun fichier.";
        }
        if (!$file->isValid()) {
            return "Upload invalide: {$file->getErrorString()} (code {$file->getError()}).";
        }
        if ($file->hasMoved()) {
            return "Fichier déjà déplacé.";
        }

        $size = (int) $file->getSize();
        if ($size <= 0) {
            return "Taille de fichier nulle (post_max_size/upload_max_filesize ?).";
        }
        if ($size > 4 * 1024 * 1024) {
            return "Fichier trop volumineux (> 4 Mo).";
        }

        $clientMime = strtolower((string) $file->getClientMimeType());
        $realMime   = strtolower((string) $file->getMimeType());

        $allowedMimes = [
            'image/jpeg'       => 'jpg',
            'image/pjpeg'      => 'jpg',
            'image/jpg'        => 'jpg',
            'image/png'        => 'png',
            'image/x-png'      => 'png',
            'image/webp'       => 'webp',
            'image/gif'        => 'gif',
            'application/pdf'  => 'pdf',
        ];

        $targetExt = $allowedMimes[$realMime] ?? ($allowedMimes[$clientMime] ?? null);
        if ($targetExt === null) {
            if (in_array($realMime, ['image/heic','image/heif','image/x-heic','image/x-heif'], true)) {
                return "HEIC/HEIF non supporté. Convertis en JPG/PNG avant upload.";
            }
            return "Type de fichier non supporté (MIME client: {$clientMime}, réel: {$realMime}).";
        }

        $original = $file->getClientName();
        $base     = pathinfo($original, PATHINFO_FILENAME);
        $origExt  = strtolower(pathinfo($original, PATHINFO_EXTENSION));

        $safeBase = $this->slugifyFilename($base);
        if ($safeBase === '') {
            $safeBase = 'fichier';
        }

        $finalExt = $targetExt;
        if ($targetExt === 'jpg' && in_array($origExt, ['jpg','jpeg'], true)) {
            $finalExt = $origExt;
        } elseif ($targetExt === 'png' && in_array($origExt, ['png'], true)) {
            $finalExt = 'png';
        } elseif ($targetExt === 'webp' && $origExt === 'webp') {
            $finalExt = 'webp';
        } elseif ($targetExt === 'gif' && $origExt === 'gif') {
            $finalExt = 'gif';
        } elseif ($targetExt === 'pdf' && $origExt === 'pdf') {
            $finalExt = 'pdf';
        }

        $finalName = $safeBase . '.' . $finalExt;
        $finalName = $this->uniqueFilename($this->uploadPath, $finalName);

        if (! $file->move($this->uploadPath, $finalName)) {
            $errStr = method_exists($file, 'getErrorString') ? $file->getErrorString() : 'erreur inconnue';
            $err    = method_exists($file, 'getError') ? $file->getError() : 0;
            return "Échec du déplacement du fichier ({$errStr}, code {$err}).";
        }

        return true;
    }

    /**
     * Slug minimal pour noms de fichiers : lettres/chiffres, tirets et underscores.
     * Supprime accents et espaces, coupe à ~120 chars.
     */
    private function slugifyFilename(string $name): string
    {
        if (function_exists('iconv')) {
            $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        }
        $name = strtolower($name);
        $name = preg_replace('~[^a-z0-9-_\.]+~', '-', $name);
        $name = preg_replace('~-+~', '-', $name);
        $name = trim($name, '-_.');
        if (strlen($name) > 120) {
            $name = substr($name, 0, 120);
        }
        $reserved = ['con','prn','aux','nul','com1','lpt1'];
        if (in_array($name, $reserved, true)) {
            $name .= '-file';
        }
        return $name;
    }

    /**
     * Si le fichier existe déjà, ajoute -1, -2, ... avant l'extension.
     * ex: photo.jpg -> photo-1.jpg -> photo-2.jpg
     */

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

