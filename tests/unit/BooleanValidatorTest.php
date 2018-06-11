<?php
use AspectMock\Test as test;

class BooleanValidatorTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected $schema;
    
    protected function _before()
    {
        $this->schema = new \stdClass();
        $this->schema->maxItems = 3;
        $this->schema->minItems = 3;
        $this->schema->uniqueItems = true;
        $this->schema->items = null;
    }

    protected function _after()
    {
        test::clean();
    }

    public function testValidateBoolean()
    {
        $object = new Swagception\Validator\BooleanValidator();
        $object->validate($this->schema, true, 'context');
    }

    public function testValidateNotBoolean()
    {
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->expectExceptionMessage('context is not a boolean.');
        
        $object = new Swagception\Validator\BooleanValidator();
        $object->validate($this->schema, 1, 'context');
    }
}
