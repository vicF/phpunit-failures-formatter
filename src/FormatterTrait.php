<?php

namespace Fokin\PhpunitFailuresFormatter;


/**
 * 
 */
trait FormatterTrait
{
    protected function expected(string $text): Formatter
    {
        /*static $formatter;
        if (!isset($formatter)) {*/
        $formatter = new Formatter();
        //}
        $backTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        return $formatter->expected($text)->backTrace($backTrace);
    }
}