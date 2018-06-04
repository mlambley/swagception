<?php
use AspectMock\Test as test;

class IntegerValidatorTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected $schema;
    
    protected function _before()
    {
        $this->schema = new \stdClass();
        $this->schema->multipleOf = 15;
        $this->schema->maximum = 100;
        $this->schema->minimum = 10;
    }

    protected function _after()
    {
        test::clean();
    }
    
    public function testValidateInteger()
    {
        $json = 15;
        
        $reflect = new ReflectionClass('Swagception\\Validator\\IntegerValidator');
        $testMethods = [];
        foreach ($reflect->getMethods() as $reflectionMethod) {
            if (!in_array($reflectionMethod->name, ['validate', 'isNumeric'])) {
                $testMethods[$reflectionMethod->name] = null;
            }
        }
        
        $validateProxy = test::double('Swagception\Validator\IntegerValidator', $testMethods);
        $object = new Swagception\Validator\IntegerValidator();
        $object->validate($this->schema, $json, 'context');
    }

    public function testValidateNotInteger()
    {
        $json = 15.5;
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->expectExceptionMessage('context is not an integer.');
        
        $reflect = new ReflectionClass('Swagception\\Validator\\IntegerValidator');
        $testMethods = [];
        foreach ($reflect->getMethods() as $reflectionMethod) {
            if (!in_array($reflectionMethod->name, ['validate', 'isNumeric'])) {
                $testMethods[$reflectionMethod->name] = null;
            }
        }
        
        $validateProxy = test::double('Swagception\Validator\IntegerValidator', $testMethods);
        $object = new Swagception\Validator\IntegerValidator();
        $object->validate($this->schema, $json, 'context');
    }

    public function testValidateIsAMultipleOf()
    {
        $this->performMethod('validateMultipleOf', [$this->schema, 75, 'context']);
    }

    public function testValidateIsNotAMultipleOf()
    {
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMultipleOf', [$this->schema, 74, 'context']);
    }

    public function testValidateMaximumTooHigh()
    {
        unset($this->schema->exclusiveMaximum);
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMaximum', [$this->schema, 101, 'context']);
    }

    public function testValidateMaximumEqual()
    {
        unset($this->schema->exclusiveMaximum);
        $this->performMethod('validateMaximum', [$this->schema, 100, 'context']);
    }

    public function testValidateMaximumLess()
    {
        unset($this->schema->exclusiveMaximum);
        $this->performMethod('validateMaximum', [$this->schema, -1, 'context']);
    }

    public function testValidateMinimumTooSmall()
    {
        unset($this->schema->exclusiveMinimum);
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMinimum', [$this->schema, 9, 'context']);
    }

    public function testValidateMinimumEqual()
    {
        $this->performMethod('validateMinimum', [$this->schema, 10, 'context']);
    }

    public function testValidateMinimumMore()
    {
        $this->performMethod('validateMinimum', [$this->schema, 9999, 'context']);
    }
    
    public function testValidateExclusiveMaximumTooHigh()
    {
        $this->schema->exclusiveMaximum = true;
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMaximum', [$this->schema, 101, 'context']);
    }

    public function testValidateExclusiveMaximumEqual()
    {
        $this->schema->exclusiveMaximum = true;
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMaximum', [$this->schema, 100, 'context']);
    }

    public function testValidateExclusiveMaximumLess()
    {
        $this->schema->exclusiveMaximum = true;
        $this->performMethod('validateMaximum', [$this->schema, 99, 'context']);
    }

    public function testValidateExclusiveMinimumTooSmall()
    {
        $this->schema->exclusiveMinimum = true;
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMinimum', [$this->schema, 9, 'context']);
    }

    public function testValidateExclusiveMinimumEqual()
    {
        $this->schema->exclusiveMinimum = true;
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMinimum', [$this->schema, 10, 'context']);
    }

    public function testValidateExclusiveMinimumMore()
    {
        $this->schema->exclusiveMinimum = true;
        $this->performMethod('validateMinimum', [$this->schema, 11, 'context']);
    }

    public function testValidateInt32TooSmall()
    {
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateInt32', [$this->schema, -2147483649, 'context']);
    }
    
    public function testValidateInt32Lowest()
    {
        $this->performMethod('validateInt32', [$this->schema, -2147483648, 'context']);
    }
    
    public function testValidateInt32Highest()
    {
        $this->performMethod('validateInt32', [$this->schema, 2147483647, 'context']);
    }
    
    public function testValidateInt32TooHigh()
    {
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateInt32', [$this->schema, 2147483648, 'context']);
    }

    public function testValidateInt64TooSmall()
    {
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateInt64', [$this->schema, -9223372036854775808, 'context']);
    }

    public function testValidateInt64Lowest()
    {
        $this->performMethod('validateInt64', [$this->schema, -9223372036854775807, 'context']);
    }

    public function testValidateInt64Highest()
    {
        $this->performMethod('validateInt64', [$this->schema, 9223372036854775807, 'context']);
    }

    public function testValidateInt64TooHigh()
    {
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateInt64', [$this->schema, 9223372036854775808, 'context']);
    }
    
    protected function performMethod($method, $args)
    {
        $object = new Swagception\Validator\IntegerValidator();
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }
}