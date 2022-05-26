<?php // vi: set fenc=utf-8 ts=4 sw=4 et:
/*
 * Copyright (C) 2013 Nicolas Grekas - p@tchwork.com
 *
 * This library is free software; you can redistribute it and/or modify it
 * under the terms of the (at your option):
 * Apache License v2.0 (http://apache.org/licenses/LICENSE-2.0.txt), or
 * GNU General Public License v2.0 (http://gnu.org/licenses/gpl-2.0.txt).
 */
include_once 'libraries/ToAscii/Normalizer.php';
use Normalizer2 as n;

/**
 * UTF-8 Grapheme Cluster aware string manipulations implementing the quasi complete
 * set of native PHP string functions that need UTF-8 awareness and more.
 * Missing are printf-family functions.
 */

class ToAscii {

    // Generic UTF-8 to ASCII transliteration
    public function convertToAscii($s , $subst_chr = '?') {
        if (preg_match("/[\x80-\xFF]/", $s))
        {
            static $translitExtra = array();
            $translitExtra or $translitExtra = static::getData('translit_extra');

            $s = n::normalize($s, n::NFKC);

           $glibc = 'glibc' === ICONV_IMPL;

            preg_match_all('/./u', $s, $s);

            foreach ($s[0] as &$c)
            {
                if (! isset($c[1])) continue;

                if ($glibc)
                {
                    $t = iconv('UTF-8', 'ASCII//TRANSLIT', $c);
                }
                else
                {
                    $t = iconv('UTF-8', 'ASCII//IGNORE//TRANSLIT', $c);

                    if (! isset($t[0])) $t = '?';
                    else if (isset($t[1])) $t = ltrim($t, '\'`"^~');
                }

                if ('?' === $t)
                {
                    if (isset($translitExtra[$c]))
                    {
                        $t = $translitExtra[$c];
                    }
                    else
                    {
                        $t = n::normalize($c, n::NFD);

                        if ($t[0] < "\x80") $t = $t[0];
                        else $t = $subst_chr;
                    }
                }

                $c = $t;
            }

            $s = implode('', $s[0]);
        }

        return $s;
    }
    
    protected static function getData($file)
    {
        $file = 'libraries/ToAscii/Utf8/data'  . $file . '.ser';
        if (file_exists($file)) return unserialize(file_get_contents($file));
        else return false;
    }
}