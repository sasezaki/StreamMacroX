<?php
namespace StreamMacroX;

class Macro
{
    /**
     * Stream stats.
     *
     * @var array
     */
    protected $_stat;

        
    static public function register($protocol)
    {
        if (!in_array($protocol, stream_get_wrappers())) {
            Compile::regist($protocol.'.compile');
        }
        if (!in_array('streammacrox.unescape-phptag', stream_get_filters())) {
            stream_filter_register("streammacrox.unescape-phptag", "StreamMacroX\\PHPTagStreamFilter");            
        }

        return true;
    }

    public static function render($path, $options = [])
    {
        $m = array();
        preg_match("/^([^\:]+)\:\/\/(.*)/", $path, $m);
        $protocol  = $m[1];
        $path  = $m[2];
        
        $callback = $options;
        if (is_callable($callback)) {
            $opts = call_user_func($callback, $path);
            extract($opts);
            include Compile::build($protocol.'.compile', $path, $opts);
        } else {
            include Compile::build($protocol.'.compile', $path);
        }
    }
}
