<?php

/**
 * @param $path
 */
function __realpath($path)
{
    $link = $path;
    while (!is_link($link) && $link != '.' && $link != '/') {
        $link = dirname($link);
    }

    if ($link != '.' && $link != '/') {
        return __realpath_link($path, $link);
    }

    return realpath($path);
}

/**
 * @param $path
 * @param $link
 * @return bool|string
 */
function __realpath_link($path, $link)
{
    $dir = dirname($link);
    $cat = trim(substr($path, strlen($link)), '/');
    $base = basename($link);
    $real = __realpath($dir);

    return $real.'/'.$base.'/'.$cat;
}
