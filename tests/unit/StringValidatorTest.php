<?php
use AspectMock\Test as test;

class StringValidatorTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected $schema;
    
    protected function _before()
    {
        $this->schema = new \stdClass();
        $this->schema->maxLength = 5;
        $this->schema->minLength = 3;
    }

    protected function _after()
    {
        test::clean();
    }
    
    public function testValidByte()
    {
        $json = 'JG2VnCZGfo4R4d0sdQoBAHhPjhIB94v/wRoRKQWGRHgrhGSQJxCS+0pCZbEhAAOw00==';
        $this->performMethod('validateByte', [$this->schema, $json, 'context']);
        $json = 'JG2VnCZGfo4R4d0sdQoBAHhPjhIB94v/wRoRKQWGRHgrhGSQJxCS+0pCZbEhAAOw00C=';
        $this->performMethod('validateByte', [$this->schema, $json, 'context']);
        $json = 'JG2VnCZGfo4R4d0sdQoBAHhPjhIB94v/wRoRKQWGRHgrhGSQJxCS+0pCZbEhAAOw00C+';
        $this->performMethod('validateByte', [$this->schema, $json, 'context']);
    }
    
    public function testInvalidByte()
    {
        $json = 'JG2VnC';
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateByte', [$this->schema, $json, 'context']);
    }
    
    public function testValidDate()
    {
        $json = '2018-05-31';
        $this->performMethod('validateDate', [$this->schema, $json, 'context']);
    }
    
    public function testInvalidDate()
    {
        $json = '31-05-2018';
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateDate', [$this->schema, $json, 'context']);
    }
    
    public function testValidDatetime()
    {
        $json = '2002-10-02T15:00:00.05Z';
        $this->performMethod('validateDatetime', [$this->schema, $json, 'context']);
    }
    
    public function testInvalidDatetime()
    {
        $json = '2002-10-02 15:00';
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateDatetime', [$this->schema, $json, 'context']);
    }
    
    public function testPassword()
    {
        $json = 'any';
        $this->performMethod('validatePassword', [$this->schema, $json, 'context']);
    }
    
    public function testValidateMaxLengthTooHigh()
    {
        $json = 'abcdef';
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMaxLength', [$this->schema, $json, 'context']);
    }

    public function testValidateMaxLengthEqual()
    {
        $json = 'abcde';
        $this->performMethod('validateMaxLength', [$this->schema, $json, 'context']);
    }

    public function testValidateMaxLengthLess()
    {
        $json = 'abcd';
        $this->performMethod('validateMaxLength', [$this->schema, $json, 'context']);
    }
    
    public function testValidateMinLengthMore()
    {
        $json = 'abcd';
        $this->performMethod('validateMinLength', [$this->schema, $json, 'context']);
    }
    
    public function testValidateMinLengthEqual()
    {
        $json = 'abc';
        $this->performMethod('validateMinLength', [$this->schema, $json, 'context']);
    }

    public function testValidateMinLengthTooLow()
    {
        $json = 'ab';
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMinLength', [$this->schema, $json, 'context']);
    }

    public function testValidPattern()
    {
        $this->schema->pattern = '^\d{4}$';
        $json = '1234';
        $this->performMethod('validatePattern', [$this->schema, $json, 'context']);
        
        $this->schema->pattern = '^[A-Za-z0-9+\\/]{5}$';
        $json = 'aB0+/';
        $this->performMethod('validatePattern', [$this->schema, $json, 'context']);
    }
    
    public function testInvalidPattern()
    {
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->schema->pattern = '^\d{4}$';
        $json = '123a';
        $this->performMethod('validatePattern', [$this->schema, $json, 'context']);
    }
    
    protected function performMethod($method, $args)
    {
        $object = new Swagception\Validator\StringValidator();
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }
}
