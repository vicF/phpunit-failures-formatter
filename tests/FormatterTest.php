<?php

namespace Fokin\PhpunitFailuresFormatter\tests;
class FormatterTest extends \PHPUnit\Framework\TestCase
{
    public function testFormatter() {
        $formatter = new \Fokin\PhpunitFailuresFormatter\Formatter();
        $formatter->expected('test');
        $this->assertStringContainsString('test', $formatter);
    }
}