<?php
namespace StreamMacroX;

class Macro
{
    /**
     * Current stream position.
     *
     * @var int
     */
    protected $_pos = 0;

    /**
     * Data for streaming.
     *
     * @var string
     */
    protected $_data;

    /**
     * file path
     *
     * @var string
     */
    protected $_path;

    /**
     * callback for when opened 
     *
     * @var string
     */
    static protected $_callbackes;

    /**
     * Stream stats.
     *
     * @var array
     */
    protected $_stat;

        
    static public function register($protocol, $callback=null)
    {
        if (!in_array($protocol, stream_get_wrappers())) {
            stream_wrapper_register($protocol , __CLASS__);
            Compile::regist($protocol.'.compile');
        }
        if (!in_array('streammacrox.unescape-phptag', stream_get_filters())) {
            stream_filter_register("streammacrox.unescape-phptag", "StreamMacroX\\PHPTagStreamFilter");            
        }
        
        $old = isset(self::$_callbackes[$protocol]) ? self::$_callbackes[$protocol] : null ;
        self::$_callbackes[$protocol] = $callback;
        return $old;
    }
    
    /**
     * Opens the script file and converts markup.
     */
    public function stream_open($path, $mode, $options, &$opened_path) 
    {
        if ( false === strpos($mode, 'r') || -1 < strpos($mode, '+'))
        {
            //trigger_error("failed to open stream: Macro wrapper does not support writeable connections");
            return false;
        }

        // get the view script source
        $m = array();
        preg_match("/^([^\:]+)\:\/\/(.*)/", $path, $m);
        $protocol  = $m[1];
        $path  = $m[2];
        
        $this->_path = $path;
        
        $callback = isset(self::$_callbackes[$protocol]) ? self::$_callbackes[$protocol] : null;
        if (is_callable($callback)) {
            $opts = call_user_func($callback, $path);
            $this->_data = Compile::build($protocol.'.compile', $path, $opts);
        } else {
            $this->_data = Compile::build($protocol.'.compile', $path);
        }
        
        /**
         * If reading the file failed, update our local stat store
         * to reflect the real stat of the file, then return on failure
         */
        if ($this->_data === false) {
            //$this->_stat = stat($path);
            return false;
        }
        
        /**
         * file_get_contents() won't update PHP's stat cache, so we grab a stat 
         * of the file to prevent additional reads should the script be 
         * requested again, which will make include() happy.
         */
        $this->_stat = stat($path);

        return true;
    }
    
    /**
     * Included so that __FILE__ returns the appropriate info
     * 
     * @return array
     */
    public function url_stat()
    {
        return $this->_stat;
    }

    /**
     * Reads from the stream.
     */
    public function stream_read($count) 
    {
        $ret = substr($this->_data, $this->_pos, $count);
        $this->_pos += strlen($ret);
        return $ret;
    }
    
    /**
     * Tells the current position in the stream.
     */
    public function stream_tell() 
    {
        return $this->_pos;
    }

    
    /**
     * Tells if we are at the end of the stream.
     */
    public function stream_eof() 
    {
        return $this->_pos >= strlen($this->_data);
    }

    
    /**
     * Stream statistics.
     */
    public function stream_stat() 
    {
        return $this->_stat;
    }

    
    /**
     * Seek to a specific point in the stream.
     */
    public function stream_seek($offset, $whence) 
    {
        switch ($whence) {
            case SEEK_SET:
                if ($offset < strlen($this->_data) && $offset >= 0) {
                $this->_pos = $offset;
                    return true;
                } else {
                    return false;
                }
                break;

            case SEEK_CUR:
                if ($offset >= 0) {
                    $this->_pos += $offset;
                    return true;
                } else {
                    return false;
                }
                break;

            case SEEK_END:
                if (strlen($this->_data) + $offset >= 0) {
                    $this->_pos = strlen($this->_data) + $offset;
                    return true;
                } else {
                    return false;
                }
                break;

            default:
                return false;
        }
    }
}
