<?php

namespace Fokin\PhpunitFailuresFormatter\tests;

use Fokin\PhpunitFailuresFormatter\FormatterTrait;

class FormatterTest extends \PHPUnit\Framework\TestCase
{
    use FormatterTrait;

    public function testFormatter()
    {
        $formatter = new \Fokin\PhpunitFailuresFormatter\Formatter();
        $formatter->expected('expected')
            ->actual('actual')
            ->res('res', 'title1')
            ->req('req', 'title2')
            ->url('url', 'title3');
        $this->assertStringContainsString('expected', $formatter);
        $this->assertStringContainsString('actual', $formatter);
        $this->assertStringContainsString('res', $formatter);
        $this->assertStringContainsString('req', $formatter);
        $this->assertStringContainsString('url', $formatter);
        $this->assertStringContainsString('title1', $formatter);
        $this->assertStringContainsString('title2', $formatter);
        $this->assertStringContainsString('title3', $formatter);
    }

    public function testFail()
    {
        $this->markTestSkipped('Comment this line to see failure output: ' . __LINE__);
        $this->fail(
            $this->expected('Expected text')
                ->actual('Actual text')
                ->legend('Some legend')
                ->res('res', 'title1')
                ->req('req', 'title2')
                ->url('http://google.com', 'Link with title')
                ->url('http://github.com')
                ->jiraIssue('EE-123')
        );
    }
}