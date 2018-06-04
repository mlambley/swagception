<?php
use AspectMock\Test as test;

class NullValidatorTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected $schema;
    
    protected function _before()
    {
        $this->schema = new \stdClass();
    }

    protected function _after()
    {
        test::clean();
    }

    public function testValidateNull()
    {
        $object = new Swagception\Validator\NullValidator();
        $object->validate($this->schema, null, 'context');
    }

    public function testValidateNotNull()
    {
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->expectExceptionMessage('context is not null.');
        
        $object = new Swagception\Validator\NullValidator();
        $object->validate($this->schema, new \stdClass(), 'context');
    }
}