<?php
require_once __DIR__.'/vendor/autoload.php';

StreamMacroX\Macro::register('macro');

StreamMacroX\Macro::render("macro://example-template2.macro", function(){
    return [
        'title' => 'a'    
    ];
});

