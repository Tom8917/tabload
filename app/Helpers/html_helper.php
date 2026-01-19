<?php

if (! function_exists('clean_html')) {

    function clean_html(string $html): string
    {
        $html = trim($html);
        if ($html === '') return '';

        static $purifier = null;

        if ($purifier === null) {
            $config = \HTMLPurifier_Config::createDefault();

            // Cache (important en prod)
            $cachePath = WRITEPATH . 'cache/htmlpurifier';
            if (!is_dir($cachePath)) {
                @mkdir($cachePath, 0755, true);
            }
            $config->set('Cache.SerializerPath', $cachePath);

            // ✅ Autoriser les balises + attributs nécessaires, y compris style
            $config->set('HTML.Allowed',
                'h1,h2,h3,h4,h5,h6,' .
                'p,div,span[style],strong,em,b,i,u,s,' .
                'ul,ol,li,br,hr,blockquote,pre,code,' .
                'a[href|title|target|rel|style],' .
                'img[src|alt|width|height|style],' .
                'table,thead,tbody,tr,th[style],td[style]'
            );

            // ✅ Autoriser uniquement certaines propriétés CSS inline (couleurs/tailles/alignements)
            $config->set('CSS.AllowedProperties', [
                'color',
                'background-color',
                'text-align',
                'font-size',
                'font-weight',
                'font-style',
                'text-decoration',
                'width',
                'height'
            ]);

            // Sécurité liens
            $config->set('Attr.AllowedFrameTargets', ['_blank']);
            $config->set('HTML.TargetBlank', true);

            // Schémas autorisés
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
