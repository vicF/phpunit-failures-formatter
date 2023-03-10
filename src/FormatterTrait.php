<?php

namespace Fokin\PhpunitFailuresFormatter;


/**
 * 
 */
trait FormatterTrait
{
    protected function expected($text)
    {
        /*static $formatter;
        if (!isset($formatter)) {*/
        $formatter = $this->formatter();
        //}
        $backTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        return $formatter->expected($text)->backTrace($backTrace);
    }

    protected function formatter() {
        return new Formatter();
    }

    
}