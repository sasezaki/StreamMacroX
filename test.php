<?php
require_once __DIR__.'/vendor/autoload.php';

function sleeprange() {
    static $count = 0;
    
    start:
    ++$count;
    yield time();
    
    if ($count < 3) {
        sleep(1);
        goto start;
    }
}

StreamMacroX\Macro::register('macro');

//render example
StreamMacroX\Macro::render("example-template.php");
