<?php
use AspectMock\Test as test;

class ObjectValidatorTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected $schema;
    
    protected function _before()
    {
        $this->schema = new \stdClass();
        $this->schema->minProperties = 2;
        $this->schema->maxProperties = 5;
    }

    protected function _after()
    {
        test::clean();
    }
    
    public function testIsAnObject()
    {
        $json = new \stdClass();
        $json->a = 'b';
        
        $reflect = new ReflectionClass('Swagception\\Validator\\ObjectValidator');
        $testMethods = [];
        foreach ($reflect->getMethods() as $reflectionMethod) {
            if (!in_array($reflectionMethod->name, ['validate'])) {
                $testMethods[$reflectionMethod->name] = null;
            }
        }
        
        $validateProxy = test::double('Swagception\Validator\ObjectValidator', $testMethods);
        $object = new Swagception\Validator\ObjectValidator();
        $object->validate($this->schema, $json, 'context');
    }
    
    public function testIsNotAnObject()
    {
        $json = ['a'];
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->expectExceptionMessage('context is not an object.');
        
        $reflect = new ReflectionClass('Swagception\\Validator\\ObjectValidator');
        $testMethods = [];
        foreach ($reflect->getMethods() as $reflectionMethod) {
            if (!in_array($reflectionMethod->name, ['validate'])) {
                $testMethods[$reflectionMethod->name] = null;
            }
        }
        
        $validateProxy = test::double('Swagception\Validator\ObjectValidator', $testMethods);
        $object = new Swagception\Validator\ObjectValidator();
        $object->validate($this->schema, $json, 'context');
    }
    
    public function testValidateMaxPropertiesTooHigh()
    {
        $json = new \stdClass();
        $json->a = 'a';
        $json->b = 'b';
        $json->c = 'c';
        $json->d = 'd';
        $json->e = 'e';
        $json->f = 'f';
        
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMaxProperties', [$this->schema, $json, 'context']);
    }

    public function testValidateMaxPropertiesEqual()
    {
        $json = new \stdClass();
        $json->a = 'a';
        $json->b = 'b';
        $json->c = 'c';
        $json->d = 'd';
        $json->e = 'e';
        
        $this->performMethod('validateMaxProperties', [$this->schema, $json, 'context']);
    }

    public function testValidateMaxPropertiesLess()
    {
        $json = new \stdClass();
        $json->a = 'a';
        $json->b = 'b';
        $json->c = 'c';
        $json->d = 'd';
        
        $this->performMethod('validateMaxProperties', [$this->schema, $json, 'context']);
    }
    
    public function testValidateMinPropertiesMore()
    {
        $json = new \stdClass();
        $json->a = 'a';
        $json->b = 'b';
        $json->c = 'c';
        
        $this->performMethod('validateMinProperties', [$this->schema, $json, 'context']);
    }

    public function testValidateMinPropertiesEqual()
    {
        $json = new \stdClass();
        $json->a = 'a';
        $json->b = 'b';
        
        $this->performMethod('validateMinProperties', [$this->schema, $json, 'context']);
    }

    public function testValidateMinPropertiesTooLow()
    {
        $json = new \stdClass();
        $json->a = 'a';
        
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMinProperties', [$this->schema, $json, 'context']);
    }
    
    public function testValidateRequiredPropertiesArePresent()
    {
        $this->schema->required = ['q', 'w', 'e'];
        
        $json = new \stdClass();
        $json->q = 'a';
        $json->w = 'b';
        $json->e = 'c';
        
        $this->performMethod('validateRequired', [$this->schema, $json, 'context']);
    }
    
    public function testValidateRequiredPropertiesAreNotPresent()
    {
        $this->schema->required = ['q', 'w', 'e'];
        
        $json = new \stdClass();
        $json->q = 'a';
        $json->w = 'b';
        
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateRequired', [$this->schema, $json, 'context']);
    }
    
    public function testValidateNothingRequiredWithData()
    {
        $this->schema->required = [];
        
        $json = new \stdClass();
        $json->q = 'a';
        $json->w = 'b';
        
        $this->performMethod('validateRequired', [$this->schema, $json, 'context']);
    }
    
    public function testValidateNothingRequiredNoData()
    {
        $this->schema->required = [];
        $json = new \stdClass();
        $this->performMethod('validateRequired', [$this->schema, $json, 'context']);
    }
    
    public function testValidateProperties()
    {
        $json = new \stdClass();
        $json->q = 'a';
        $json->w = 'b';
        $json->e = 'c';
        $json->r = 'd';
        
        $schema = $this->schema;
        $schema->properties = new \stdClass();
        $schema->properties->q = '';
        $schema->properties->w = '';
        $schema->properties->e = '';
        $schema->properties->r = '';
        
        $validateProxy = test::double('Swagception\Validator\Validator', ['validate' => null]);
        $this->performMethod('validateProperties', [$this->schema, $json, 'context']);
        $validateProxy->verifyInvokedMultipleTimes('validate', 4);
    }
    
    public function testValidateAllowAdditionalProperties()
    {
        $schema = $this->schema;
        $schema->properties = new \stdClass();
        $schema->properties->q = '';
        $schema->properties->w = '';
        $schema->additionalProperties = true;
        
        $json = new \stdClass();
        $json->q = 'a';
        $json->w = 'b';
        $json->e = 'c';
        $json->r = 'd';
        
        $validateProxy = test::double('Swagception\Validator\Validator', ['validate' => null]);
        $this->performMethod('validateAdditionalProperties', [$this->schema, $json, 'context']);
        $validateProxy->verifyNeverInvoked('validate');
    }
    
    public function testValidateUnspecifiedAdditionalProperties()
    {
        $schema = $this->schema;
        $schema->properties = new \stdClass();
        $schema->properties->q = '';
        $schema->properties->w = '';
        unset($schema->additionalProperties);
        
        $json = new \stdClass();
        $json->q = 'a';
        $json->w = 'b';
        $json->e = 'c';
        $json->r = 'd';
        
        $validateProxy = test::double('Swagception\Validator\Validator', ['validate' => null]);
        $this->performMethod('validateAdditionalProperties', [$this->schema, $json, 'context']);
        $validateProxy->verifyNeverInvoked('validate');
    }
    
    public function testValidateDisallowAdditionalPropertiesButArePresent()
    {
        $schema = $this->schema;
        $schema->properties = new \stdClass();
        $schema->properties->q = '';
        $schema->properties->w = '';
        $schema->additionalProperties = false;
        
        $json = new \stdClass();
        $json->q = 'a';
        $json->w = 'b';
        $json->e = 'c';
        $json->r = 'd';
        
        $this->expectException(Swagception\Exception\ValidationException::class);
        $validateProxy = test::double('Swagception\Validator\Validator', ['validate' => null]);
        $this->performMethod('validateAdditionalProperties', [$this->schema, $json, 'context']);
    }
    
    public function testValidateDisallowAdditionalPropertiesAndAreNotPresent()
    {
        $schema = $this->schema;
        $schema->properties = new \stdClass();
        $schema->properties->q = '';
        $schema->properties->w = '';
        $schema->additionalProperties = false;
        
        $json = new \stdClass();
        $json->q = 'a';
        $json->w = 'b';
        
        $validateProxy = test::double('Swagception\Validator\Validator', ['validate' => null]);
        $this->performMethod('validateAdditionalProperties', [$this->schema, $json, 'context']);
        $validateProxy->verifyNeverInvoked('validate');
    }
    
    public function testValidateAllowAdditionalPropertiesOfAnyType()
    {
        $schema = $this->schema;
        $schema->properties = new \stdClass();
        $schema->properties->q = '';
        $schema->properties->w = '';
        $schema->additionalProperties = new \stdClass();
        
        $json = new \stdClass();
        $json->q = 'a';
        $json->w = 'b';
        $json->e = 'c';
        $json->r = 'd';
        
        $validateProxy = test::double('Swagception\Validator\Validator', ['validate' => null]);
        $this->performMethod('validateAdditionalProperties', [$this->schema, $json, 'context']);
        $validateProxy->verifyNeverInvoked('validate');
    }
    
    public function testValidateAllowAdditionalPropertiesOfASpecifiedType()
    {
        $schema = $this->schema;
        $schema->properties = new \stdClass();
        $schema->properties->q = '';
        $schema->properties->w = '';
        $schema->additionalProperties = new \stdClass();
        $schema->additionalProperties->type = 'string';
        
        $json = new \stdClass();
        $json->q = 'a';
        $json->w = 'b';
        $json->e = 'c';
        $json->r = 'd';
        $json->t = 'd';
        
        $validateProxy = test::double('Swagception\Validator\Validator', ['validate' => null]);
        $this->performMethod('validateAdditionalProperties', [$this->schema, $json, 'context']);
        $validateProxy->verifyInvokedMultipleTimes('validate', 3);
    }
    
    public function testValidateAllowAdditionalPropertiesOfASpecifiedTypeWithoutProperties()
    {
        $schema = $this->schema;
        unset($schema->properties);
        $schema->additionalProperties = new \stdClass();
        $schema->additionalProperties->type = 'string';
        
        $json = new \stdClass();
        $json->q = 'a';
        $json->w = 'b';
        
        $validateProxy = test::double('Swagception\Validator\Validator', ['validate' => null]);
        $this->performMethod('validateAdditionalProperties', [$this->schema, $json, 'context']);
        $validateProxy->verifyInvokedMultipleTimes('validate', 2);
    }
    
    protected function performMethod($method, $args)
    {
        $object = new Swagception\Validator\ObjectValidator();
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }
}
