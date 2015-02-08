<?php
require_once __DIR__.'/vendor/autoload.php';

StreamMacroX\Macro::register('macro');

StreamMacroX\Macro::render("example-template2.macro", [
        'title' => 'a'    
    ]
);

