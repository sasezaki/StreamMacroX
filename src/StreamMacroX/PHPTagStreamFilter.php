<?php
namespace StreamMacroX;

use php_user_filter;

class PHPTagStreamFilter extends php_user_filter
{
    function filter($in, $out, &$consumed, $closing) {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $bucket->data = PHPTagFilter::unescape($bucket->data);
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }
        return PSFS_PASS_ON;
    }
}