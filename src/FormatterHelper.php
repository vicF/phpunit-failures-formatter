<?php

namespace Fokin\PhpunitFailuresFormatter;

use Symfony\Component\Console\Formatter\OutputFormatter;
use tests\unit\Helpers\Formatter;

/**
 *
 */
class FormatterHelper implements \Stringable
{
    const URL = 1;
    const JIRA = 2;
    const REQUEST = 3;
    const RESULT = 4;
    protected $expected;
    protected $actual;
    protected $backtrace;
    protected $data = [];
    protected OutputFormatter $formatter;

    public function __construct()
    {
        $this->formatter = new OutputFormatter();
        $this->formatter->setDecorated(true);
    }

    /**
     * @param string $text
     * @return FormatterHelper
     */
    public function expected(string $text): FormatterHelper
    {
        $this->expected = '{color:green}' . $text . '{color}';
        return $this;
    }

    public function actual(string $text): FormatterHelper
    {
        $this->actual = '{color:red}' . $text . '{color}';
        return $this;
    }

    protected function _prepareData($data)
    {
        if (gettype($data) !== 'string') {
            $data = Tools::varDump($data);
        }
        return $data;
    }

    /**
     * @param $data
     * @param null $title
     * @return $this
     */
    public function res($data, $title = null): FormatterHelper
    {

        $this->data[] = [self::RESULT, "{code}" . $this->_prepareData($data) . "{code}", $title];
        return $this;
    }

    public function req($data, $title = null): FormatterHelper
    {
        $this->data[] = [self::REQUEST, "{code}" . $this->_prepareData($data) . "{code}", $title];
        return $this;
    }

    public function url($url, $title = null): FormatterHelper
    {
        $this->data[] = [self::URL, $url, $title];
        return $this;
    }

    public function jiraIssue($url, $title = null): FormatterHelper
    {
        $this->data[] = [self::JIRA, $url, $title];
        return $this;
    }

    public function backTrace($backtrace): FormatterHelper
    {
        $this->backtrace = $backtrace;
        return $this;
    }

    /**
     * @param $params
     * @param null $title
     * @param bool $geo
     * @return $this
     */
    public function legend($params, $title = null, $geo = false): FormatterHelper
    {
        $this->_strParts[] = ['_legend', $params, $title, $geo];
        return $this;
    }

    /**
     * @return string|void
     */
    public function __toString()
    {
        $output = $this->formatter->format(
            "✅   <fg=green>{$this->expected}</>\n❗   <fg=red>{$this->actual}</>\n");
        foreach ($this->data as $resArray) {
            @[$type, $data, $title] = $resArray;
            switch ($type) {
                case self::RESULT:
                    $output .= '⚙   ️';
                    if (!empty($title)) {
                        $output .= $this->formatter->format("<options=bold>$title</>\n");
                    }
                    $output .= $data . "\n";
                    break;
                case self::REQUEST:
                    $output .= '📡   ️';
                    if (!empty($title)) {
                        $output .= $this->formatter->format("<options=bold>$title</>\n");
                    }
                    $output .= $data . "\n";
                    break;
                case self::URL:
                    $output .= '🔗   ️';
                    if (!empty($title)) {
                        $output .= $this->formatter->format("<options=bold>$title</>\n");
                    }
                    $output .= $this->formatter->format("    <href={$data}>$data</>\n");
                    break;
                case self::JIRA:
                    $output .= '🔗   ️';
                    if (!empty($title)) {
                        $output .= $this->formatter->format("<options=bold>$title</>\n");
                    }
                    $output .= $this->formatter->format("    Jira issue: <href=https://jira.internetbrands.com/browse/{$data}>$data</>\n");
                    break;
                default:
                    $output .= '🖥   ️';
                    if (!empty($title)) {
                        $output .= $this->formatter->format("<options=bold>$title</>\n");
                    }
                    if (is_array($data)) {
                        $data = Tools::varDump($data, 1000);
                    }
                    $output .= $data . "\n";

            }
        }
        if (!empty($this->backtrace)) {
            $output .= $this->formatter->format("🛠   <fg=gray>Line:{$this->backtrace[0]['line']}\n{$this->backtrace[0]['file']}</>\n");
        }

        return $output;
    }

    /**
     * @param $value
     * @param null $title
     * @return FormatterHelper
     */
    public function resXmlFormatted($value, $title = null): FormatterHelper
    {
        try {
            $dom = new \DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($value);
            $value = $dom->saveXML();
        } catch (\Throwable $e) {
            // formatting failed
        }
        return $this->resXml($value, $title);

    }

    /**
     * Print out xml data
     *
     * @param $value
     * @param null $title
     * @param null $topCut
     * @param null $bottomCut
     * @return $this
     */
    public function resXml($value, $title = null, $topCut = null, $bottomCut = null): FormatterHelper
    {
        $this->data[] = ['_resXml', $value, $title, $topCut, $bottomCut];
        return $this;
    }

    /**
     * @param $value
     * @param string $title
     * @return $this
     */
    public function dbErrors($value, $title = ''): FormatterHelper
    {
        $this->data[] = ['_dbErrors', $value, $title];
        return $this;
    }

    /**
     * @param $data
     * @return $this
     */
    public function libXmlErrors($data)
    {
        $this->data[] = ['_libXmlErrors', $data];
        return $this;
    }
}