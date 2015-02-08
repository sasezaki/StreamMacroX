<?php
namespace StreamMacroX;

class Compile 
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
     * Mode
     *
     * @var string
     */
    protected $_mode;

    /**
     * Stream stats.
     *
     * @var array
     */
    protected $_stat;
    
    static public function regist($protocol)
    {
        if (!in_array($protocol, stream_get_wrappers())) {
            stream_wrapper_register($protocol, __CLASS__);
        }
    }
    
    /**
     * Build
     */
    static public function build($protocol = null, $path = null)
    {
		if (file_exists($path)) { 
			$path = $protocol . '://' . $path;
            return "php://filter/read=streammacrox.unescape-phptag/resource=$path";
		} else {
			return false;
		}
    }
    
    /**
     * Opens the script file and converts markup.
     */
    public function stream_open($path, $mode, $options, &$opened_path) 
    {
        // get the view script source
        $path   = preg_replace("/^[^\:]+\:\/\//", '', $path);
        $this->_path = $path;
        $this->_mode = $mode;

        if (-1<strpos($this->_mode, 'r'))
        {
            $this->_data = $this->_loadData();
            $this->_pos = 0;
        }
        if (-1<strpos($this->_mode, 'w'))
        {
            if (!file_exists($path) ) { touch($path); }
            $this->_data = '';
            $this->_pos = 0;
        }
        if (-1<strpos($this->_mode, 'a'))
        {
            if (!file_exists($path) ) { touch($path); }
             $this->_data = $this->_loadData();
             $this->_pos = strlen($this->_data);
        }
        if (-1<strpos($this->_mode, 'x')) {
             if (file_exists($path)) { 
                $h = fopen($path, $mode);fclose($h);
                return false;
             } else {
                $h = fopen($path, $mode);fclose($h);
             }

             $this->_data = $this->_loadData();
             $this->_pos = 0;
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
     * Close
     */
    public function stream_close()
    {
        if (-1<strpos($this->_mode, "w") || -1<strpos($this->_mode, "a") || -1<strpos($this->_mode, "x")) {
            file_put_contents($this->_path, PHPTagFilter::unescape($this->_data));
        }
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
     * Reads from the stream.
     */
    public function stream_write($data) 
    {
        $left = substr($this->_data, 0, $this->_pos);
        $right = substr($this->_data, $this->_pos + strlen($data));
        $this->_data = $left . $data . $right;
        $this->_pos += strlen($data);
        return strlen($data);
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
    
    private function _loadData()
    {
		if (!file_exists($this->_path)) {return false;}
		$data = file_get_contents($this->_path);
		if ($data === false) {
			return $data;
		}
		return PHPTagFilter::escape($data);
    }
    
}
