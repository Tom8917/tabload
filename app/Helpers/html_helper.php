<?php

// ==================================
// APP/Helpers/html_helper.php
// ==================================

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

            // ⚠️ border-radius PAS supporté par HTMLPurifier -> warning
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

            // On autorise data: (base64) pour Dompdf
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
    /**
     * Convertit toutes les <img src="..."> en data URI base64.
     * - Supporte src absolu (https://domain/...), /uploads/..., uploads/...
     * - Convertit WEBP -> PNG si possible (sinon Dompdf affichera souvent l'alt)
     */
    function pdf_embed_images(string $html): string
    {
        $html = (string)$html;
        if (trim($html) === '') return $html;

        $base   = rtrim(base_url('/'), '/');                 // ex: https://tabload.fr
        $public = rtrim(FCPATH, DIRECTORY_SEPARATOR);        // .../public

        $resolveDiskPath = static function (string $src) use ($base, $public): ?string {
            $src = trim($src);
            if ($src === '') return null;

            // data: déjà embarqué
            if (str_starts_with($src, 'data:')) return null;

            // retire querystring ?v=123
            $srcNoQs = preg_replace('/\?.*$/', '', $src) ?? $src;

            // URL du site -> chemin
            if (str_starts_with($srcNoQs, $base)) {
                $srcNoQs = substr($srcNoQs, strlen($base)); // /uploads/...
            }

            // URL externe -> on ne gère pas ici
            if (preg_match('#^https?://#i', $srcNoQs)) {
                return null;
            }

            // normalise ./ et ../
            $srcNoQs = preg_replace('#^\./#', '', $srcNoQs) ?? $srcNoQs;
            while (str_starts_with($srcNoQs, '../')) {
                $srcNoQs = substr($srcNoQs, 3);
            }

            // "uploads/.." => "/uploads/.."
            if ($srcNoQs !== '' && $srcNoQs[0] !== '/') {
                $srcNoQs = '/' . $srcNoQs;
            }

            $disk = $public . str_replace('/', DIRECTORY_SEPARATOR, $srcNoQs);
            return is_file($disk) ? $disk : null;
        };

        $makeDataUri = static function (string $diskPath): ?string {
            $ext = strtolower(pathinfo($diskPath, PATHINFO_EXTENSION));

            // mime de base
            $mime = match ($ext) {
                'png'  => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                'gif'  => 'image/gif',
                'webp' => 'image/webp',
                default => null,
            };

            if ($mime === null) return null;

            // WEBP -> PNG (beaucoup plus fiable pour Dompdf)
            if ($mime === 'image/webp') {
                if (function_exists('imagecreatefromwebp')) {
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
                // Si on ne sait pas convertir, on tente quand même le webp (mais Dompdf peut échouer)
            }

            $bin = @file_get_contents($diskPath);
            if ($bin === false || $bin === '') return null;

            return 'data:' . $mime . ';base64,' . base64_encode($bin);
        };

        return preg_replace_callback(
            '#<img\b([^>]*?)\bsrc=["\']([^"\']+)["\']([^>]*)>#i',
            static function (array $m) use ($resolveDiskPath, $makeDataUri) {
                $before = $m[1] ?? '';
                $src    = $m[2] ?? '';
                $after  = $m[3] ?? '';

                $disk = $resolveDiskPath($src);
                if (!$disk) {
                    // On laisse tel quel (si c’est externe, Dompdf tentera avec isRemoteEnabled)
                    return $m[0];
                }

                $dataUri = $makeDataUri($disk);
                if (!$dataUri) {
                    return $m[0];
                }

                // On remplace la src par le data URI
                $tag = '<img' . $before . ' src="' . $dataUri . '"' . $after . '>';

                // Evite les images géantes si l’éditeur n’a pas mis de style
                if (!preg_match('#\bstyle=["\']#i', $tag)) {
                    $tag = preg_replace('#<img\b#i', '<img style="max-width:100%;height:auto;"', $tag, 1) ?? $tag;
                }

                return $tag;
            },
            $html
        ) ?? $html;
    }
}