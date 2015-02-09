<?php
namespace StreamMacroX;

class Filter
{
	private $_buff;
	private $_macro_funcs = array();
	
	private function __construct($buff)
	{
		$this->_buff = $buff;
	}
	
	public static function build($buff)
	{
		$inct = new self($buff);

		$inct->_filter_include();
		$inct->_filter_content(); //
		$inct->_filter_define();
		$inct->_filter_constant();
		$inct->_filter_if();
		$inct->_filter_loop();
		$inct->_filter_comment();
		//var_dump($inct->_buff);

	   return $inct->_buff;
	}

	private function _filter_content()
	{
        $this->_buff = preg_replace_callback("/^\s*#content/im", function() {
            $path = Macro::$content_path;
            return PHPTagFilter::escape(file_get_contents($path));
        }, $this->_buff);
	}

	private function _filter_include()
	{
		$this->_buff = preg_replace_callback("/^\s*#include\s+(\S+)/im", array($this, "_filter_include_callback"), $this->_buff);
	}

	private function _filter_include_callback($m)
	{
		$filepath = $m[1];
		return PHPTagFilter::escape(file_get_contents($filepath));
	}

	private function _filter_define()
	{
		$this->_buff = preg_replace_callback("/^\s*#define\s+([^\s\(\)]+)\s*\(([^\)]*)\)\s*(.*)/im", array($this, "_filter_defmacrofunc_callback"), $this->_buff);
		$this->_buff = preg_replace_callback("/^\s*#define\s+([^\s\(\)]+)\s*(.*)/im", array($this, "_filter_define_callback"), $this->_buff);
	}

	private function _filter_define_callback($m)
	{
		$key = $m[1];
		$val = trim($m[2]); //var_export(trim($m[2]),true);
		return "<?php " . $key . " = " . $val . ";?>";
	}

	private function _filter_defmacrofunc_callback($m)
	{
		$funckey = "\${'**func** ".trim($m[1])."'}";
		$pram = explode (',', trim($m[2]));
		foreach($pram as $key=>&$val) {
			$val = var_export(trim($val),true);
		}
		$pram = 'array('.implode(',',$pram).')';
		$code = var_export(trim($m[3]), true);
		return "<?php " . $funckey . " = array(" . $code . "," .  $pram . ");?>";
	}
	
	private function _filter_constant()
	{
		$this->_buff = preg_replace_callback("/%%([^\s\(\)]+)%%/i", array($this, "_filter_constant_callback"), $this->_buff);
		$this->_buff = preg_replace_callback("/%%(\S+)\s*\((.*)\)%%/i", array($this, "_filter_macrofunc_callback"), $this->_buff);
	}

	private function _filter_constant_callback($m)
	{
		$key = $m[1];
		$def = var_export($m[0],true);
		return "<?php echo isset(\$" . $key . ")?\$" . $key . ":" . $def . ";?>";
	}

	private function _filter_macrofunc_callback($m)
	{
		$funckey = "\${'**func** ".trim($m[1])."'}";
		$parm = trim($m[2]);
		$def = var_export($m[0],true);
		$func = "strtr(" . $funckey. "[0], array_combine(" . $funckey. "[1], array(" . $parm . ")))";
		return "<?php echo isset(" . $funckey . ")?" . $func . ":" . $def . ";?>";
	}
	
	private function _filter_if()
	{
		$this->_buff = preg_replace_callback("/^\s*#ifdef\s+(\S+)/im", array($this, "_filter_ifdef_callback"), $this->_buff);
		$this->_buff = preg_replace_callback("/^\s*#if\s+(.*)/im", array($this, "_filter_if_callback"), $this->_buff);
		$this->_buff = preg_replace_callback("/^\s*#else/im", array($this, "_filter_else_callback"), $this->_buff);
		$this->_buff = preg_replace_callback("/^\s*#endif/im", array($this, "_filter_endif_callback"), $this->_buff);
	}

	private function _filter_ifdef_callback($m)
	{
		$key = $m[1];
		return "<?php if(isset(" . $key . ")):?>";
	}

	private function _filter_if_callback($m)
	{
		$line = trim($m[1]);
		return "<?php if(" . $line . "):?>";
	}

	private function _filter_else_callback($m)
	{
		return "<?php else:?>";
	}

	private function _filter_endif_callback($m)
	{
		return "<?php endif;?>";
	}
	
	private function _filter_loop()
	{
		$this->_buff = preg_replace_callback("/^\s*#while\s+(.*)/im", array($this, "_filter_while_callback"), $this->_buff);
		$this->_buff = preg_replace_callback("/^\s*#endwhile/im", array($this, "_filter_endwhile_callback"), $this->_buff);
		$this->_buff = preg_replace_callback("/^\s*#foreach\s+(.*)/im", array($this, "_filter_foreach_callback"), $this->_buff);
		$this->_buff = preg_replace_callback("/^\s*#endforeach/im", array($this, "_filter_endforeach_callback"), $this->_buff);
		$this->_buff = preg_replace_callback("/^\s*#for\s+(.*)/im", array($this, "_filter_for_callback"), $this->_buff);
		$this->_buff = preg_replace_callback("/^\s*#endfor/im", array($this, "_filter_endfor_callback"), $this->_buff);
	}

	private function _filter_while_callback($m)
	{
		$line = trim($m[1]);
		return "<?php while(" . $line . "):?>";
	}

	private function _filter_endwhile_callback($m)
	{
		return "<?php endwhile;?>";
	}

	private function _filter_foreach_callback($m)
	{
		$line = trim($m[1]);
		return "<?php foreach(" . $line . "):?>";
	}

	private function _filter_endforeach_callback($m)
	{
		return "<?php endforeach;?>";
	}

	private function _filter_for_callback($m)
	{
		$line = trim($m[1]);
		return "<?php for(" . $line . "):?>";
	}

	private function _filter_endfor_callback($m)
	{
		return "<?php endfor;?>";
	}

	private function _filter_comment()
	{
		$this->_buff = preg_replace("/^\s*#\//im", "", $this->_buff);
	}

}
