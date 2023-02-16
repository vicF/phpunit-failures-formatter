# Test Failures Formatter
Can be used with phpunit or codeception to format failure messages for test assertions.
It can create colored messages describing expected result in green, actual result (failure) in red. 
Additionally it will format various data you supply that may help you to identify the cause of failure. 
Incoming parameters and results in various PHP formats, URLs etc. 
Also it adds Jira tags so that failure can be copied from console to Jira issue.   

## Installation

composer require --dev fokin/phpunit-failures-formatter

##Setup

Add `use Fokin\PhpunitFailuresFormatter\FormatterTrait;` to your test case. 
Alternately you can use static call to formatter class:
`Fokin\PhpunitFailuresFormatter\Formatter::expected()`

##Usage

In your test case:
$this->assertTrue(false, 
    $this->expected('I thought that it would be true')
        ->actual('Sad to admit that it is false')
        ->reg(['This is example array'=> 'that would be printed'], 'Example of incoming data')
        ->res('Example result data')
        ->url('http://localhost/bad_page', 'URL that failed')
        ->jiraIssue('ABC-123', 'Last known issue for this case')
); 

To add Jira tags to the output define constant JIRA_TAGS 
