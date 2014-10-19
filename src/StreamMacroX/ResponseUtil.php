<?php
namespace StreamMacroX;

class ResponseUtil
{
	public static function chunkLine($data)
	{
	    $len = strlen($data);
	    $chunk = dechex($len)."\r\n".$data."\r\n";
	    
	    return $chunk;
	}
}
