<?php
namespace Swagception\Reporter;

class HTMLReporter implements ReportsTests
{
    /**
     * @var HTMLReportItem[] List of all items to be included in this report. A normal array to preserve sort order.
     */
    protected $reportItems;
    /**
     * @var [string => string => HTMLReportItem] Cache of items, so we don't create duplicates.
     */
    protected $reportItemCache;
    /**
     * @var HTMLReportItem The report item to which we are currently writing details.
     */
    protected $currentReportItem;
    /**
     * @var string Complete path and file name which the HTML report will be written.
     */
    protected $fileName;
    /**
     * @var [string => string] Mapping of result names to the hex colours which they should be displayed in HTML.
     */
    protected $resultColours;
    /**
     * @var bool Finalise will be called multiple times. This is used to ensure the actions only happen once.
     */
    protected $isFinalised;
    /**
     * @var string Name of the special "test case" which contains information not related to a specific case. It will not show if it is blank.
     */
    protected $miscTestName;

    const RESULT_PASS = 'Pass';
    const RESULT_FAIL = 'Fail';
    const RESULT_SKIP = 'Skip';

    public function __construct()
    {
        $this->reportItems = [];
        $this->reportItemCache = [];
        $this->loadResultColours();
        $this->isFinalised = false;
        $this->loadMiscTestName();
    }

    public function setMiscInfo()
    {
        $this->setCurrentTest($this->miscTestName);
    }

    /**
     * @param string $testName Unique, user-friendly test name to be displayed on the HTML report.
     * @param string|null $data Example data which was passed to the test, if applicable.
     */
    public function setCurrentTest($testName, $data = null)
    {
        $exampleData = $this->getExampleData($data);

        if (!isset($this->reportItemCache[$testName][$exampleData])) {
            $ReportItem = (new HTMLReportItem($testName, $exampleData))
                ->withResultColours($this->resultColours);

            $this->reportItems[] = $ReportItem;
            $this->reportItemCache[$testName][$exampleData] = $ReportItem;
        }
        $this->currentReportItem = $this->reportItemCache[$testName][$exampleData];
    }

    public function setCurrentTestName($name)
    {
        $this->getCurrentReportItem()->withTestName($name);
    }

    public function logDetail($header, $message, $preformatted = false)
    {
        if ($preformatted) {
            $this->getCurrentReportItem()->logDetail($header, '<pre>' . $this->prettyPrint($message) . '</pre>');
        } else {
            $this->getCurrentReportItem()->logDetail($header, $this->prettyPrint($message));
        }
    }

    public function logException(\Exception $e)
    {
        $this->logResult('Fail');

        if ($e instanceof \PHPUnit_Framework_ExceptionWrapper) {
            $this->getCurrentReportItem()->logDetail('Exception', '<pre>' . $this->prettyPrint(''.$e) . '</pre>');
        } else {
            $this->getCurrentReportItem()->logDetail('Exception', '<pre>' . $this->prettyPrint(get_class($e) . "\n" . $e->getMessage() . "\n" . $e->getTraceAsString()) . '</pre>');
        }
    }

    public function logResult($result)
    {
        $this->getCurrentReportItem()->logResult($result);
    }

    public function finalise()
    {
        if ($this->isFinalised) {
            return;
        }

        $html = $this->getReportTemplate();
        $replacements = [
            '{{title}}' => $this->getReportTitle(),
            '{{javascript}}' => $this->getReportJS(),
            '{{body}}' => $this->getBody()
        ];

        foreach ($replacements as $key => $val) {
            $html = str_replace($key, $val, $html);
        }

        file_put_contents($this->getFileName(), $html);
        $this->isFinalised = true;
    }

    protected function getReportTemplate()
    {
        return require(__DIR__ . '/ReportTemplate.php');
    }

    protected function getReportTitle()
    {
        return 'API Test Report';
    }

    protected function getReportJS()
    {
        return $this->getJQuery() . $this->getJS();
    }

    protected function getJQuery()
    {
        return file_get_contents(__DIR__ . '/jquery.php');
    }

    protected function getJS()
    {
        return file_get_contents(__DIR__ . '/js.php');
    }

    protected function getBody()
    {
        $body = $this->getExpandAll();
        foreach ($this->reportItems as $ReportItem) {
            if ($ReportItem->getTestName() === $this->miscTestName && $ReportItem->isEmpty()) {
                //Skip
            } else {
                $body .= $ReportItem->getHTML();
            }
        }

        return $body;
    }

    protected function getExpandAll()
    {
        return '<a id="linkAll" href="#">[Expand All]</a>';
    }

    protected function getFileName()
    {
        if (!isset($this->fileName)) {
            $this->loadDefaultFileName();
        }
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return static
     */
    public function withFileName($fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    protected function loadDefaultFileName()
    {
        $this->fileName = codecept_output_dir() . DIRECTORY_SEPARATOR . 'HTMLReport.html';
    }

    /**
     * @param [string => string] $resultColours
     * @return static
     */
    public function withResultColours($resultColours)
    {
        $this->resultColours = $resultColours;
        return $this;
    }

    protected function loadResultColours()
    {
        $this->resultColours = [
            static::RESULT_PASS => '#99FF99',
            static::RESULT_FAIL => '#FF9999',
            static::RESULT_SKIP => '#FFFF99',

            //A nice, soothing grey is the default colour.
            '' => '#999999'
        ];
    }

    protected function getCurrentReportItem()
    {
        if (!isset($this->currentReportItem)) {
            $this->setCurrentTest('Unknown test');
        }
        return $this->currentReportItem;
    }

    protected function prettyPrint($data)
    {
        if (is_string($data)) {
            if (json_decode($data) === null) {
                //Isn't json, so just return as is.
                return $this->preserveSpaces(htmlentities($data));
            }

            //Is already a string but not pretty.
            $json = json_encode(json_decode($data), JSON_PRETTY_PRINT);
        } elseif (is_object($data)) {
            $json = json_encode($data, JSON_PRETTY_PRINT);
        } elseif (is_array($data)) {
            $json = json_encode($data, JSON_PRETTY_PRINT);
        } else {
            //Neither string nor object nor array, therefore not json.
            return $this->preserveSpaces(htmlentities($data));
        }

        if ($json === false) {
            //Isn't json, so just return as is.
            return htmlentities($data);
        }
        return sprintf('<pre>%1$s</pre>', htmlentities($json));
    }

    protected function getExampleData($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $strings = array_filter($data, function ($val) {
            return is_string($val);
        });

        return implode(' ', $strings);
    }

    protected function loadMiscTestName()
    {
        $this->miscTestName = 'Miscellaneous';
    }

    public function withMiscTestName($name)
    {
        $this->miscTestName = $name;
        return $this;
    }

    protected function preserveSpaces($str)
    {
        return str_replace('  ', ' &nbsp;', $str);
    }
}
