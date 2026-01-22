<?php

if (! function_exists('clean_html')) {

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

            $config->set('HTML.Allowed',
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
                'height'
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
