<?php

use HTMLPurifier;
use HTMLPurifier_Config;

if (! function_exists('clean_html')) {
    function clean_html(string $html): string
    {
        static $purifier = null;

        if ($purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('Cache.DefinitionImpl', null);
            $config->set('HTML.Allowed',
                'h1,h2,h3,h4,h5,h6,p,div,span,strong,em,ul,ol,li,br,hr,blockquote,' .
                'a[href|title|target|rel],' .
                'img[src|alt|width|height]'
            );
            $config->set('Attr.AllowedFrameTargets', ['_blank']);
            $config->set('HTML.TargetBlank', true);

            $purifier = new HTMLPurifier($config);
        }

        return $purifier->purify($html);
    }
}
