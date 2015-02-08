<?php
require_once __DIR__.'/vendor/autoload.php';

StreamMacroX\Macro::register('macro', function(){
    return [
        'title' => 'a'   
    ];
});

$title = 'TITLE';
$main = 'hellooooo';

include "macro://example-template2.macro";

//render example
//include "macro://example-template.php";
//ob_start(null, 32);
//stream_copy_to_stream(fopen("macro://example-template.php", "r"), fopen("php://output", "w"));
