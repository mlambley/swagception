<?php
namespace Swagception\Reporter;

class HTMLReportItem
{
    protected $result;
    protected $details;
    protected $testID;
    protected $data;
    protected $testName;
    protected $resultColours;

    public function __construct($testID, $data = null)
    {
        $this->details = [];
        $this->testID = $testID;
        $this->data = $data;
    }

    public function logDetail($header, $message)
    {
        $this->details[] = [$header, $message];
    }

    public function logResult($result)
    {
        $this->result = $result;
    }

    public function withResultColours($resultColours)
    {
        $this->resultColours = $resultColours;
        return $this;
    }

    public function getHTML()
    {
        $html = $this->getHeader();
        $html .= sprintf('<div id="details%1$s" style="display:none;">', $this->getAnchorName());
        foreach ($this->details as $detail) {
            $html .= $this->htmlRow($detail);
        }
        $html .= '</div>';

        return $html;
    }

    public function isEmpty()
    {
        return count($this->details) === 0;
    }

    public function getTestName()
    {
        if (!isset($this->testName)) {
            $this->loadTestName();
        }
        return $this->testName;
    }

    public function withTestName($name)
    {
        $this->testName = $name;
        return $this;
    }

    protected function loadTestName()
    {
        $this->testName = $this->testID;
        if (!empty($this->data)) {
            $this->testName .= ' | ' . $this->data;
        }
    }

    protected function getHeader()
    {
        return $this->getAnchor() . '<h2>' . $this->getResultHTML(). $this->getTestName() . $this->getLink() . '</h2>';
    }

    protected function getResultHTML()
    {
        return sprintf('<span style="float:left; margin: 0 10px 0 0; width:100px; background-color:%1$s; text-align:center; font-weight:bold;">%2$s</span>', $this->getResultColour(), $this->result);
    }

    protected function getResultColour()
    {
        if (!empty($this->resultColours)) {
            if (isset($this->resultColours[$this->result])) {
                return $this->resultColours[$this->result];
            } elseif (isset($this->resultColours[''])) {
                return $this->resultColours[''];
            }
        }

        //No colour for this result, and no default colour set.
        return null;
    }

    public function htmlRow($cells)
    {
        $str = '';
        if (!empty($cells)) {
            if ($cells[0] !== null) {
                $str .= sprintf('<h3 style="margin:10px 0 2px 30px">%1$s</h3>', $cells[0]);
            }
            unset($cells[0]);
        }

        if (!empty($cells)) {
            foreach ($cells as $cell) {
                if ($cell !== null) {
                    $str .= sprintf('<div style="margin:0 0 0 30px">%1$s</div>', $cell);
                }
            }
        }

        return $str;
    }

    protected function getLink()
    {
        return sprintf('<a id="link%1$s" class="link" style="margin-left:15px; font-size:10pt; font-weight:normal" href="#">[Expand]</a>', $this->getAnchorName());
    }

    protected function getAnchor()
    {
        return sprintf('<a name="%1$s"></a>', $this->getAnchorName());
    }

    protected function getAnchorName()
    {
        return md5($this->testID . $this->data);
    }
}
