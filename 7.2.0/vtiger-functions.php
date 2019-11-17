<?php

/**
 * Retrieve realpath without resolve symbolic link.
 *
 * @param $path
 */
function __realpath($path, $link = null)
{
    if ($link) {
        $head = trim(substr($path, strlen($link)), '/');
        $base = basename($link);
        $real = __realpath(dirname($link));

        return $real.'/'.$base.'/'.$head;
    }

    $link = $path;
    while (!is_link($link) && $link != '.' && $link != '/') {
        $link = dirname($link);
    }

    if ($link != '.' && $link != '/') {
        return __realpath($path, $link);
    }

    return realpath($path);
}
