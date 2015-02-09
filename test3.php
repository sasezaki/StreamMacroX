<?php
require_once __DIR__.'/vendor/autoload.php';

StreamMacroX\Macro::register('macro');
StreamMacroX\Macro::$content_path = 'example-template3.macro';

//render example
StreamMacroX\Macro::render("layout.macro");
