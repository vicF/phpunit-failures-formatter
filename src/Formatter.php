<?php

namespace Fokin\PhpunitFailuresFormatter;


/**
 * 
 */
trait Formatter
{
    protected function expected(string $text): FormatterHelper
    {
        /*static $formatter;
        if (!isset($formatter)) {*/
        $formatter = new FormatterHelper();
        //}
        $backTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        return $formatter->expected($text)->backTrace($backTrace);
    }
}