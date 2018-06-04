<?php
use AspectMock\Test as test;

class NumericValidatorTest extends \Codeception\Test\Unit
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
        $this->schema->maximum = 100.0;
        $this->schema->minimum = 10.0;
    }

    protected function _after()
    {
        test::clean();
    }
    
    public function testValidateIntegersAreNumeric()
    {
        $json = 15;
        
        $reflect = new ReflectionClass('Swagception\\Validator\\NumericValidator');
        $testMethods = [];
        foreach ($reflect->getMethods() as $reflectionMethod) {
            if (!in_array($reflectionMethod->name, ['validate', 'isNumeric'])) {
                $testMethods[$reflectionMethod->name] = null;
            }
        }
        
        $validateProxy = test::double('Swagception\Validator\NumericValidator', $testMethods);
        $object = new Swagception\Validator\NumericValidator();
        $object->validate($this->schema, $json, 'context');
    }
    
    public function testValidateFloatsAreNumeric()
    {
        $json = 15.5;
        
        $reflect = new ReflectionClass('Swagception\\Validator\\NumericValidator');
        $testMethods = [];
        foreach ($reflect->getMethods() as $reflectionMethod) {
            if (!in_array($reflectionMethod->name, ['validate', 'isNumeric'])) {
                $testMethods[$reflectionMethod->name] = null;
            }
        }
        
        $validateProxy = test::double('Swagception\Validator\NumericValidator', $testMethods);
        $object = new Swagception\Validator\NumericValidator();
        $object->validate($this->schema, $json, 'context');
    }

    public function testValidateLooksLikeANumber()
    {
        $json = '123';
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->expectExceptionMessage('context is not a number.');
        
        $reflect = new ReflectionClass('Swagception\\Validator\\NumericValidator');
        $testMethods = [];
        foreach ($reflect->getMethods() as $reflectionMethod) {
            if (!in_array($reflectionMethod->name, ['validate', 'isNumeric'])) {
                $testMethods[$reflectionMethod->name] = null;
            }
        }
        
        $validateProxy = test::double('Swagception\Validator\NumericValidator', $testMethods);
        $object = new Swagception\Validator\NumericValidator();
        $object->validate($this->schema, $json, 'context');
    }

    public function testValidateIsAMultipleOf()
    {
        $this->performMethod('validateMultipleOf', [$this->schema, 75.0, 'context']);
    }

    public function testValidateIsNotAMultipleOf()
    {
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMultipleOf', [$this->schema, 74.0, 'context']);
    }

    public function testValidateMaximumTooHigh()
    {
        unset($this->schema->exclusiveMaximum);
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMaximum', [$this->schema, 101.0, 'context']);
    }

    public function testValidateMaximumEqual()
    {
        unset($this->schema->exclusiveMaximum);
        $this->performMethod('validateMaximum', [$this->schema, 100.0, 'context']);
    }

    public function testValidateMaximumLess()
    {
        unset($this->schema->exclusiveMaximum);
        $this->performMethod('validateMaximum', [$this->schema, -1.0, 'context']);
    }

    public function testValidateMinimumTooSmall()
    {
        unset($this->schema->exclusiveMinimum);
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMinimum', [$this->schema, 9.0, 'context']);
    }

    public function testValidateMinimumEqual()
    {
        $this->performMethod('validateMinimum', [$this->schema, 10.0, 'context']);
    }

    public function testValidateMinimumMore()
    {
        $this->performMethod('validateMinimum', [$this->schema, 9999.0, 'context']);
    }
    
    public function testValidateExclusiveMaximumTooHigh()
    {
        $this->schema->exclusiveMaximum = true;
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMaximum', [$this->schema, 101.0, 'context']);
    }

    public function testValidateExclusiveMaximumEqual()
    {
        $this->schema->exclusiveMaximum = true;
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMaximum', [$this->schema, 100.0, 'context']);
    }

    public function testValidateExclusiveMaximumLess()
    {
        $this->schema->exclusiveMaximum = true;
        $this->performMethod('validateMaximum', [$this->schema, 99.0, 'context']);
    }

    public function testValidateExclusiveMinimumTooSmall()
    {
        $this->schema->exclusiveMinimum = true;
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMinimum', [$this->schema, 9.0, 'context']);
    }

    public function testValidateExclusiveMinimumEqual()
    {
        $this->schema->exclusiveMinimum = true;
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMinimum', [$this->schema, 10.0, 'context']);
    }

    public function testValidateExclusiveMinimumMore()
    {
        $this->schema->exclusiveMinimum = true;
        $this->performMethod('validateMinimum', [$this->schema, 11.0, 'context']);
    }

    public function testIsAFloat()
    {
        $this->performMethod('validateFloat', [$this->schema, 11.0, 'context']);
    }

    public function testIsNotAFloat()
    {
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateFloat', [$this->schema, 11, 'context']);
    }

    public function testIsADouble()
    {
        $this->performMethod('validateFloat', [$this->schema, 11.0, 'context']);
    }

    public function testIsNotADouble()
    {
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateFloat', [$this->schema, 11, 'context']);
    }
    
    protected function performMethod($method, $args)
    {
        $object = new Swagception\Validator\NumericValidator();
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }
}