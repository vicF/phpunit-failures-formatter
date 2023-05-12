<?php

namespace Fokin\PhpunitFailuresFormatter;


/**
 * 
 */
trait FormatterTrait
{
    public function expected($text)
    {
        /*static $formatter;
        if (!isset($formatter)) {*/
        $formatter = $this->formatter();
        //}
        $backTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        return $formatter->expected($text)->backTrace($backTrace);
    }

    public function formatter() {
        return new Formatter();
    }

    
}