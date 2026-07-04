<?php
declare(strict_types=1);

namespace App\Core;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Nettoie le HTML des actualités avant affichage public.
 */
final class HtmlSanitizer
{
    private static ?HTMLPurifier $purifier = null;

    public static function clean(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }
        return self::instance()->purify($html);
    }

    private static function instance(): HTMLPurifier
    {
        if (self::$purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', 'p,br,strong,em,u,b,i,h2,h3,h4,ul,ol,li,a[href|target|rel],blockquote,img[src|alt|width|height]');
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true]);
            $config->set('Attr.AllowedFrameTargets', ['_blank']);
            self::$purifier = new HTMLPurifier($config);
        }
        return self::$purifier;
    }
}
