<?php

use App\Models\MediaModel;
use App\Models\MediaBlobModel;

if (!function_exists('clean_html')) {
    function clean_html(string $html): string
    {
        $html = trim($html);
        if ($html === '') return '';

        static $purifier = null;

        if ($purifier === null) {
            $config = \HTMLPurifier_Config::createDefault();

            $cachePath = WRITEPATH . 'cache/htmlpurifier';
            if (!is_dir($cachePath)) {
                @mkdir($cachePath, 0755, true);
            }
            $config->set('Cache.SerializerPath', $cachePath);

            $config->set(
                'HTML.Allowed',
                'h1[style|class],h2[style|class],h3[style|class],h4[style|class],h5[style|class],h6[style|class],' .
                'p[style|class],div[style|class],span[style|class],strong,em,b,i,u,s,' .
                'ul,ol,li,br,hr,blockquote,pre,code,' .
                'a[href|title|target|rel|style|class],' .
                'img[src|alt|width|height|style|class],' .
                'table,thead,tbody,tr,th[style|class],td[style|class]'
            );

            $config->set('CSS.AllowedProperties', [
                'color',
                'background-color',
                'text-align',
                'font-size',
                'font-weight',
                'font-style',
                'text-decoration',
                'margin',
                'padding',
                'width',
                'height',
                'max-width',
                'border',
            ]);

            $config->set('Attr.AllowedFrameTargets', ['_blank']);
            $config->set('HTML.TargetBlank', true);
            $config->set('CSS.AllowImportant', true);

            $config->set('URI.AllowedSchemes', [
                'http'   => true,
                'https'  => true,
                'mailto' => true,
                'data'   => true,
            ]);

            $purifier = new \HTMLPurifier($config);
        }

        return $purifier->purify($html);
    }
}

if (!function_exists('pdf_embed_images')) {
    function pdf_embed_images(string $html): string
    {
        $html = (string)$html;
        if (trim($html) === '') return $html;

        $base   = rtrim(base_url('/'), '/');
        $public = rtrim(FCPATH, DIRECTORY_SEPARATOR);

        $resolveMediaId = static function (string $src) use ($base): ?int {
            $src = trim($src);
            if ($src === '') return null;

            $srcNoQs = preg_replace('/\?.*$/', '', $src) ?? $src;

            if (str_starts_with($srcNoQs, $base)) {
                $srcNoQs = substr($srcNoQs, strlen($base));
            }

            if (preg_match('#/media/file/(\d+)$#i', $srcNoQs, $m)) {
                $id = (int)$m[1];
                return $id > 0 ? $id : null;
            }

            return null;
        };

        $makeDataUriFromDb = static function (int $mediaId): ?string {
            try {
                /** @var \App\Models\MediaModel $mediaModel */
                $mediaModel = model(MediaModel::class);
                /** @var \App\Models\MediaBlobModel $blobModel */
                $blobModel  = model(MediaBlobModel::class);

                $media = $mediaModel->find($mediaId);
                $blob  = $blobModel->find($mediaId);

                if (!$media || !$blob || empty($blob['data'])) return null;

                $mime = strtolower((string)($media['mime_type'] ?? 'application/octet-stream'));
                $bin  = $blob['data'];

                if ($mime === 'image/webp' && function_exists('imagecreatefromstring')) {
                    $im = @imagecreatefromstring($bin);
                    if ($im) {
                        ob_start();
                        imagepng($im);
                        imagedestroy($im);
                        $png = (string)ob_get_clean();
                        if ($png !== '') {
                            return 'data:image/png;base64,' . base64_encode($png);
                        }
                    }
                }

                return 'data:' . $mime . ';base64,' . base64_encode($bin);
            } catch (\Throwable $e) {
                return null;
            }
        };

        $resolveDiskPath = static function (string $src) use ($base, $public): ?string {
            $src = trim($src);
            if ($src === '') return null;

            if (str_starts_with($src, 'data:')) return null;

            $srcNoQs = preg_replace('/\?.*$/', '', $src) ?? $src;

            if (str_starts_with($srcNoQs, $base)) {
                $srcNoQs = substr($srcNoQs, strlen($base)); // /uploads/...
            }

            if (preg_match('#^https?://#i', $srcNoQs)) {
                return null;
            }

            $srcNoQs = preg_replace('#^\./#', '', $srcNoQs) ?? $srcNoQs;
            while (str_starts_with($srcNoQs, '../')) {
                $srcNoQs = substr($srcNoQs, 3);
            }

            if ($srcNoQs !== '' && $srcNoQs[0] !== '/') {
                $srcNoQs = '/' . $srcNoQs;
            }

            $disk = $public . str_replace('/', DIRECTORY_SEPARATOR, $srcNoQs);
            return is_file($disk) ? $disk : null;
        };

        $makeDataUriFromDisk = static function (string $diskPath): ?string {
            $ext = strtolower(pathinfo($diskPath, PATHINFO_EXTENSION));

            $mime = match ($ext) {
                'png'  => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                'gif'  => 'image/gif',
                'webp' => 'image/webp',
                default => null,
            };

            if ($mime === null) return null;

            if ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
                $im = @imagecreatefromwebp($diskPath);
                if ($im) {
                    ob_start();
                    imagepng($im);
                    imagedestroy($im);
                    $bin = (string)ob_get_clean();
                    if ($bin !== '') {
                        return 'data:image/png;base64,' . base64_encode($bin);
                    }
                }
            }

            $bin = @file_get_contents($diskPath);
            if ($bin === false || $bin === '') return null;

            return 'data:' . $mime . ';base64,' . base64_encode($bin);
        };

        return preg_replace_callback(
            '#<img\b([^>]*?)\bsrc=["\']([^"\']+)["\']([^>]*)>#i',
            static function (array $m) use ($resolveMediaId, $makeDataUriFromDb, $resolveDiskPath, $makeDataUriFromDisk) {
                $before = $m[1] ?? '';
                $src    = $m[2] ?? '';
                $after  = $m[3] ?? '';

                $mid = $resolveMediaId($src);
                if ($mid) {
                    $dataUri = $makeDataUriFromDb($mid);
                    if ($dataUri) {
                        $tag = '<img' . $before . ' src="' . $dataUri . '"' . $after . '>';
                        if (!preg_match('#\bstyle=["\']#i', $tag)) {
                            $tag = preg_replace('#<img\b#i', '<img style="max-width:100%;height:auto;"', $tag, 1) ?? $tag;
                        }
                        return $tag;
                    }
                    return $m[0];
                }

                $disk = $resolveDiskPath($src);
                if ($disk) {
                    $dataUri = $makeDataUriFromDisk($disk);
                    if ($dataUri) {
                        $tag = '<img' . $before . ' src="' . $dataUri . '"' . $after . '>';
                        if (!preg_match('#\bstyle=["\']#i', $tag)) {
                            $tag = preg_replace('#<img\b#i', '<img style="max-width:100%;height:auto;"', $tag, 1) ?? $tag;
                        }
                        return $tag;
                    }
                }

                return $m[0];
            },
            $html
        ) ?? $html;
    }
}