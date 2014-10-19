<?php
namespace StreamMacroX;

class PHPTagFilter
{
    static public function escape($data)
    {
        $replaces = array();
        $replaces["<\\?"] = "<\\\\?";
        $replaces["<?"] = "<\\?";
        $replaces["\\?>"] = "\\\\?>";
        $replaces["?>"] = "\\?>";
        $buff = strtr( $data, $replaces);
        return Filter::build($buff);
    }
    
    static public function unescape($data)
    {
        $replaces = array();
        $replaces["<\\\\?"] = "<\\?";
        $replaces["<\\?"] = "<?";
        $replaces["\\\\?>"] = "\\?>";
        $replaces["\\?>"] = "?>";         
        return strtr($data, $replaces);
    }
}