<?php
namespace Swagception\Extension;

use \Codeception\Events;

class Swagception extends \Codeception\Extension
{
    public static $events = array(
        Events::TEST_START => 'testStart',
        Events::TEST_ERROR => 'testError',
        Events::TEST_FAIL => 'testFail',
        Events::TEST_INCOMPLETE => 'testIncomplete',
        Events::TEST_SKIPPED => 'testSkipped',
        Events::TEST_WARNING => 'testWarning',
        Events::TEST_SUCCESS => 'testSuccess',
        Events::SUITE_AFTER => 'afterSuite',
    );

    public function testStart(\Codeception\Event\TestEvent $e)
    {
        $SwaggerSchema = $this->getSwaggerSchema($e->getTest()->getTestClass());
        if (!empty($SwaggerSchema)) {
            $SwaggerSchema->setCurrentTest($e->getTest()->getMetaData()->getName(), $e->getTest()->getMetaData()->getCurrent('example'));
        }
    }

    public function testError(\Codeception\Event\FailEvent $e)
    {
        $SwaggerSchema = $this->getSwaggerSchema($e->getTest()->getTestClass());
        if (!empty($SwaggerSchema)) {
            $SwaggerSchema->logException($e->getFail());
        }
    }

    public function testFail(\Codeception\Event\FailEvent $e)
    {
        $SwaggerSchema = $this->getSwaggerSchema($e->getTest()->getTestClass());
        if (!empty($SwaggerSchema)) {
            $SwaggerSchema->logResult('Fail');
            $SwaggerSchema->logDetail('Fail', $e->getFail()->getMessage());
        }
    }

    public function testIncomplete(\Codeception\Event\FailEvent $e)
    {
        $SwaggerSchema = $this->getSwaggerSchema($e->getTest()->getTestClass());
        if (!empty($SwaggerSchema)) {
            $SwaggerSchema->logResult('Incomplete');
            $SwaggerSchema->logDetail('Incomplete', $e->getFail()->getMessage());
        }
    }

    public function testSkipped(\Codeception\Event\FailEvent $e)
    {
        $SwaggerSchema = $this->getSwaggerSchema($e->getTest()->getTestClass());
        if (!empty($SwaggerSchema)) {
            $SwaggerSchema->logResult('Skip');
            $SwaggerSchema->logDetail('Skip', $e->getFail()->getMessage());
        }
    }

    public function testWarning(\Codeception\Event\FailEvent $e)
    {
        $SwaggerSchema = $this->getSwaggerSchema($e->getTest()->getTestClass());
        if (!empty($SwaggerSchema)) {
            $SwaggerSchema->logResult('Warning');
            $SwaggerSchema->logDetail('Warning', $e->getFail()->getMessage());
        }
    }

    public function testSuccess(\Codeception\Event\TestEvent $e)
    {
        $SwaggerSchema = $this->getSwaggerSchema($e->getTest()->getTestClass());
        if (!empty($SwaggerSchema)) {
            $SwaggerSchema->logResult('Pass');
        }
    }

    public function afterSuite(\Codeception\Event\SuiteEvent $e)
    {
        $tests = $e->getSuite()->tests();
        foreach ($tests as $test) {
            $SwaggerSchema = $this->getSwaggerSchema($test->getTestClass());

            if (!empty($SwaggerSchema)) {
                //Finalise may be called multiple times for each reporter, so the SwaggerSchema will need to be able to handle this.
                $SwaggerSchema->finalise();
            }
        }
    }

    protected function getSwaggerSchema($cest)
    {
        if (method_exists($cest, '_getSwaggerContainer')) {
            $Container = $cest->_getSwaggerContainer();

            if ($Container instanceof \Swagception\Container\ContainsInstances) {
                $SwaggerSchema = $Container->getSchema();

                //It doesn't strictly need to be an instance of SwaggerSchema. We're only going to call the functions as defined in ReportsTests.
                if ($SwaggerSchema instanceof \Swagception\Reporter\ReportsTests) {
                    return $SwaggerSchema;
                }
            }
        }
        return null;
    }
}
