<?php
namespace Swagception\Reporter;

interface ReportsTests
{
    /**
     * Sets an identifier for the current test (eg. the function name) and its example data (if applicable).
     * Each test id/data combination must be unique, otherwise the results will not display correctly.
     *
     * @param string $testID
     * @param string $data
     */
    public function setCurrentTest($testID, $data = null);
    /**
     * Sets a user-friendly name for the current test. This information should be displayed prominently.
     * If this function is not called, the system will automatically determine a test name based on its id and data.
     *
     * @param string $name
     */
    public function setCurrentTestName($name);
    /**
     * Tells the reporter to report miscellaneous information, not directly related to a test.
     */
    public function setMiscInfo();
    /**
     * Stores information about the current test. Either header or data can be null, and anything which is null should be ignored.
     * Duplicate headers are allowed. Extra data must be appended, rather than replaced.
     *
     * Examples:
     * $Logger->setCurrentTest('testThatItWorks', 'with this data');
     * $Logger->logDetail('Response code', '404');
     * $Logger->logDetail('Response message', 'Could not find this resource.');
     * $Logger->logDetail('Failure', null);
     *
     * $Logger->logDetail('Duplicates are fine', 'This will be shown');
     * $Logger->logDetail('Duplicates are fine', 'This will also be shown. The header will print twice.');
     *
     * @param string|null $header
     * @param string|null $message
     * @param bool $preformatted Whether the formatting of the string should be preserved.
     */
    public function logDetail($header, $message, $preformatted = false);
    /**
     * Records that the current test has generated the provided exception.
     *
     * @param \Exception $e
     */
    public function logException(\Exception $e);
    /**
     * Stores the result of the current test. This information should be displayed prominently.
     * Suggested values include 'Fail', 'Pass', 'Skip', etc.
     *
     * @param string $result
     */
    public function logResult($result);
    /**
     * Finalise the logging of this test suite, whether that means writing it to a file, sending to a remote server, etc.
     * No other functions should be called in this instance after finalise is called.
     */
    public function finalise();
}
