<?php

namespace Fokin\PhpunitFailuresFormatter;

use Symfony\Component\Console\Formatter\OutputFormatter;

/**
 *
 */
class Formatter implements \Stringable
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
    protected static array $colors;

    public function __construct()
    {
        $this->formatter = new OutputFormatter();
        self::initColors();
    }

    protected static function initColors()
    {
        if (empty(self::$colors)) {
            $options = getenv('TERM') ? '' : ' -T xterm';
            self::$colors['BLACK'] = @exec("tput setaf 0 $options");
            self::$colors['GREEN'] = @exec("tput setaf 1 $options");
            self::$colors['RED'] = @exec("tput setaf 3 $options");
            self::$colors['BOLD'] = @exec("tput bold $options");
            self::$colors['BLUE'] = @exec("tput setaf 5 $options");
            self::$colors['CL'] = @exec("tput sgr0 $options");
        }
    }

    /**
     * What result is expected
     *
     * @param string $text
     * @return Formatter
     */
    public function expected(string $text): Formatter
    {
        $this->expected = $text;
        return $this;
    }

    /**
     * Actual result
     *
     * @param string $text
     * @return $this
     */
    public function actual(string $text): Formatter
    {
        $this->actual = $text;
        return $this;
    }

    protected function _prepareData($data)
    {
        if (gettype($data) !== 'string') {
            $data = self::varDump($data);
        }
        return $data;
    }

    /**
     * Result of something
     *
     * @param      $data
     * @param null $title
     * @return $this
     */
    public function res($data, $title = null): Formatter
    {

        $this->data[] = [self::RESULT, $this->_prepareData($data), $title];
        return $this;
    }

    /**
     * Incoming data
     *
     * @param $data
     * @param $title
     * @return $this
     */
    public function req($data, $title = null): Formatter
    {
        $this->data[] = [self::REQUEST, $this->_prepareData($data), $title];
        return $this;
    }

    /**
     * @TODO there is an issue with dash in URL in console
     * @param $url
     * @param $title
     * @return $this
     */
    public function url($url, $title = null): Formatter
    {
        $this->data[] = [self::URL, $url, $title];
        return $this;
    }

    /**
     * @TODO there is an issue with dash in URL in console
     *
     * @param $url
     * @param $title
     * @return $this
     */
    public function jiraIssue($url, $title = null): Formatter
    {
        $this->data[] = [self::JIRA, $url, $title];
        return $this;
    }

    public function backTrace($backtrace): Formatter
    {
        $this->backtrace = $backtrace;
        return $this;
    }

    /**
     * @param      $params
     * @param null $title
     * @return $this
     */
    public function legend($params, $title = null): Formatter
    {
        $this->_strParts[] = ['_legend', $params, $title];
        return $this;
    }

    /**
     * If constant JIRA_TAGS defined will return tag wrapped in black color tag to make it invisible
     *
     * @param      $tag
     * @param bool $newLine
     * @return string
     */
    protected static function jiraTag($tag, bool $newLine = false)
    {
        extract(self::$colors);
        return defined('JIRA_TAGS') ? " {$BLACK}{{$tag}}{$CL} " . ($newLine ? "\n" : '') : '';
    }

    protected function areHyperlinksSupported()
    {
        static $supported;
        if (!isset($supported)) {
            // Set HYPERLINKS_SUPPORTED=1 if your terminal supports them
            //
            $supported = getenv('SUPPORT_HYPERLINKS'); // && !getenv('JENKINS_URL');
        }
        return $supported;
    }

    /**
     * @return string|void
     */
    public function __toString()
    {
        extract(self::$colors);
        $output =
            self::jiraTag('color:green', true) . "✅   {$GREEN}{$this->expected}{$CL}" . self::jiraTag('color') . self::jiraTag('color:red') . "\n❗   {$RED}{$this->actual}{$CL}" . self::jiraTag('color');
        foreach ($this->data as $resArray) {
            @[$type, $data, $title] = $resArray;
            switch ($type) {
                case self::RESULT:
                    $output .= "\n🗂   ️";
                    $title ??= 'Result';
                    $output .= "{$BOLD}{$title}{$CL}"
                        . self::jiraTag('code') . "\n"
                        . $data . self::jiraTag('code');
                    break;
                case self::REQUEST:
                    $output .= "\n📡   ️";
                    $title ??= 'Request';
                    $output .= "{$BOLD}{$title}{$CL}"
                        . self::jiraTag('code') . "\n"
                        . $data . self::jiraTag('code');
                    break;
                case self::URL:
                    $output .= "\n🔗   ️";
                    if ($title) {
                        $output .= "{$BOLD}{$title}{$CL}\n    ";
                    }
                    $output .= $this->hyperlink($data);
                    break;
                case self::JIRA:
                    $output .= "\n🔗   ️";
                    $title ??= 'Jira issue';
                    $output .= "{$BOLD}{$title}{$CL}\n";
                    if (defined('JIRA_URL')) {
                        $str = $this->hyperlink(JIRA_URL . $data, $data);
                    } else {
                        $str = $data;
                    }
                    $output .= "    Jira issue: {$str}";
                    break;
                default:
                    $output .= "\n🖥   ️";
                    $title ??= 'Data';
                    $output .= "{$BOLD}{$title}{$CL}\n";
                    if (is_array($data)) {
                        $data = self::varDump($data, 1000);
                    }
                    $output .= $data;
            }
        }
        if (!empty($this->backtrace)) {
            $output .= "\n🛠   {$BLUE}Line:{$this->backtrace[0]['line']}\n{$this->backtrace[0]['file']}{$CL}";
        }

        return $output . "\n";
    }


    protected function hyperlink($url, $text = null)
    {
        if ($text === null) {
            $text = $url;
        }
        if (!$this->areHyperlinksSupported()) {
            // If running in Jenkins console
            return $url;
        } else {
            return "\e]8;;$url\e\\$text\e]8;;\e\\\n";
        }
    }

    /**
     * @param      $value
     * @param null $title
     * @return Formatter
     */
    public function resXmlFormatted($value, $title = null): Formatter
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
     * @param      $value
     * @param null $title
     * @param null $topCut
     * @param null $bottomCut
     * @return $this
     */
    public function resXml($value, $title = null, $topCut = null, $bottomCut = null): Formatter
    {
        $this->data[] = ['_resXml', $value, $title, $topCut, $bottomCut];
        return $this;
    }

    /**
     * @param        $value
     * @param string $title
     * @return $this
     */
    public function dbErrors($value, $title = ''): Formatter
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

    /**
     * Dumps variable value. Replacement for print_r with recursion limit
     *
     * @param       $variable
     * @param int $strlen
     * @param int $width
     * @param int $depth
     * @param bool $showCaller
     * @param int $i
     * @param array $objects
     * @return string
     */
    public static function varDump($variable, int $strlen = 100, int $width = 25, int $depth = 10, bool $showCaller = false, int $i = 0, &$objects = [])
    {
        $search = ["\0", "\a", "\b", "\f", "\n", "\r", "\t", "\v"];
        $replace = ['\0', '\a', '\b', '\f', '\n', '\r', '\t', '\v'];

        $string = '';

        switch (gettype($variable)) {
            case 'boolean':
                $string .= $variable ? 'true' : 'false';
                break;
            case 'integer':
            case 'double':
                $string .= $variable;
                break;
            case 'resource':
                $string .= '[resource]';
                break;
            case 'NULL':
                $string .= 'null';
                break;
            case 'unknown type':
                $string .= '???';
                break;
            case 'string':
                $len = strlen($variable);
                $variable = str_replace($search, $replace, substr($variable, 0, $strlen), $count);
                $variable = substr($variable, 0, $strlen);
                if ($len < $strlen) {
                    $string .= '"' . $variable . '"';
                } else {
                    $string .= 'string(' . $len . '): "' . $variable . '"...';
                }
                break;
            case 'array':
                $len = count($variable);
                if ($i == $depth) {
                    $string .= 'array(' . $len . ') {...}';
                } elseif (!$len) {
                    $string .= 'array(0) {}';
                } else {
                    $keys = array_keys($variable);
                    $spaces = str_repeat(' ', $i * 2);
                    $string .= "array($len)\n" . $spaces . '{';
                    $count = 0;
                    foreach ($keys as $key) {
                        if ($count == $width) {
                            $string .= "\n" . $spaces . '  ...';
                            break;
                        }
                        $string .= "\n" . $spaces . "  [$key] => ";
                        $string .= self::varDump($variable[$key], $strlen, $width, $depth, false, $i + 1, $objects);
                        $count++;
                    }
                    $string .= "\n" . $spaces . '}';
                }
                break;
            case 'object':
                $id = array_search($variable, $objects, true);
                if ($id !== false) {
                    $string .= get_class($variable) . '#' . ($id + 1) . ' {...}';
                } else if ($i == $depth) {
                    $string .= get_class($variable) . ' {...}';
                } else {
                    $id = array_push($objects, $variable);
                    $array = (array)$variable;
                    $spaces = str_repeat(' ', $i * 2);
                    $string .= get_class($variable) . "#$id\n" . $spaces . '{';
                    $properties = array_keys($array);
                    foreach ($properties as $property) {
                        $name = str_replace("\0", ':', trim($property));
                        $string .= "\n" . $spaces . "  [$name] => ";
                        $string .= self::varDump($array[$property], $strlen, $width, $depth, false, $i + 1, $objects);
                    }
                    $string .= "\n" . $spaces . '}';
                }
                break;
        }

        if ($i > 0) {
            return $string;
        }
        if ($showCaller) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            do {
                $caller = array_shift($backtrace);
            } while ($caller && !isset($caller['file']));
            if ($caller) {
                $string .= "\n(" . $caller['file'] . ':' . $caller['line'] . ')';
            }
        }
        return $string;
    }

    public function setTestName($name): Formatter
    {
        return $this;
    }
}