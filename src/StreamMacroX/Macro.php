<?php
namespace StreamMacroX;

class Macro
{
    public static $content_path; // for layout

    protected static $protocol;
        
    static public function register($protocol)
    {
        if (!in_array($protocol, stream_get_wrappers())) {
            Compile::regist($protocol.'.compile');
        }
        if (!in_array('streammacrox.unescape-phptag', stream_get_filters())) {
            stream_filter_register("streammacrox.unescape-phptag", "StreamMacroX\\PHPTagStreamFilter");            
        }

        self::$protocol = $protocol;

        return true;
    }

    public static function render($path, $options = [])
    {
        $callback = $options;
        $opts = (is_callable($options)) ? call_user_func($callback, $path) : $options;
        if (is_array($opts)) {
            extract($opts);
            include Compile::build(self::$protocol.'.compile', $path, $opts);
        } else {
            include Compile::build(self::$protocol.'.compile', $path);
        }
    }
}
