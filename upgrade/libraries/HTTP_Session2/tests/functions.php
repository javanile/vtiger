<?php
/**
 * Recursively delete a directory
 *
 * @param  string $dir Directory name
 * @param  boolean $deleteRootToo Delete specified top-level directory as well
 * @link   http://de2.php.net/manual/en/function.unlink.php#87045
 * @author John Hassal
 */
function unlinkRecursive($dir, $deleteRootToo = false)
{
    if (!is_dir($dir)) {
        return;
    }

    if (!$dh = opendir($dir)) {
        return;
    }
    while (false !== ($obj = readdir($dh))) {
        if ($obj == '.' || $obj == '..') {
            continue;
        }

        if (file_exists($dir . '/' . $obj)) {
            if (!@unlink($dir . '/' . $obj)) {
                unlinkRecursive($dir.'/'.$obj, true);
            }
        }
    }
    closedir($dh);

    if ($deleteRootToo) {
        @rmdir($dir);
    }
    return;
}
?>
