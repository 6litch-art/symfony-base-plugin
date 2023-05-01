<?php

function file_replace(array|string $search, array|string $replace, array|string $fname, int &$count = null)
{
    if (!is_array($fname)) {
        $fname = [$fname];
    }
    foreach ($fname as $f) {
        file_put_contents($f, str_replace($search, $replace, file_get_contents($f), $count), flags: LOCK_EX);
    }
}
