<?php
use AspectMock\Test as test;

class ArrayValidatorTest extends \Codeception\Test\Unit
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
    
    public function testValidateArray()
    {
        $json = ['a'];
        
        $reflect = new ReflectionClass('Swagception\\Validator\\ArrayValidator');
        $testMethods = [];
        foreach ($reflect->getMethods() as $reflectionMethod) {
            if (!in_array($reflectionMethod->name, ['validate', 'isArray'])) {
                $testMethods[$reflectionMethod->name] = null;
            }
        }
        
        $validateProxy = test::double('Swagception\Validator\ArrayValidator', $testMethods);
        $object = new Swagception\Validator\ArrayValidator();
        $object->validate($this->schema, $json, 'context');
    }

    public function testValidateNotArray()
    {
        $json = 'a';
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->expectExceptionMessage('context is not an array.');
        
        $reflect = new ReflectionClass('Swagception\\Validator\\ArrayValidator');
        $testMethods = [];
        foreach ($reflect->getMethods() as $reflectionMethod) {
            if (!in_array($reflectionMethod->name, ['validate', 'isArray'])) {
                $testMethods[$reflectionMethod->name] = null;
            }
        }
        
        $validateProxy = test::double('Swagception\Validator\ArrayValidator', $testMethods);
        $object = new Swagception\Validator\ArrayValidator();
        $object->validate($this->schema, $json, 'context');
    }
    
    public function testMaxItemsTooMany()
    {
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMaxItems', [$this->schema, ['a', 'b', 'c', 'd'], 'context']);
    }
    
    public function testMaxItemsEqual()
    {
        $this->performMethod('validateMaxItems', [$this->schema, ['a', 'b', 'c'], 'context']);
    }
    
    public function testMaxItemsLess()
    {
        $this->performMethod('validateMaxItems', [$this->schema, ['a', 'b'], 'context']);
    }
    
    public function testMinItemsTooFew()
    {
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateMinItems', [$this->schema, ['a', 'b'], 'context']);
    }
    
    public function testMinItemsEqual()
    {
        $this->performMethod('validateMinItems', [$this->schema, ['a', 'b', 'c'], 'context']);
    }
    
    public function testMinItemsMore()
    {
        $this->performMethod('validateMinItems', [$this->schema, ['a', 'b', 'c', 'd'], 'context']);
    }
    
    public function testUniqueItemsWithDuplicates()
    {
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateUniqueItems', [$this->schema, ['a', 'a', 'c', 'd'], 'context']);
    }
    
    public function testUniqueItemsNoDuplicates()
    {
        $this->performMethod('validateUniqueItems', [$this->schema, ['a', 'b', 'c', 'd'], 'context']);
    }
    
    public function testValidateItems()
    {
        $validateProxy = test::double('Swagception\Validator\Validator', ['validate' => null]);
        $this->performMethod('validateItems', [$this->schema, ['a', 'b', 'c', 'd'], 'context']);
        $validateProxy->verifyInvokedMultipleTimes('validate', 4);
    }
    
    public function testIsAnArray()
    {
        $this->assertTrue($this->performMethod('isArray', [['a']]));
    }
    
    public function testIsAStructNotAnArray()
    {
        $this->assertFalse($this->performMethod('isArray', [['a' => 'b']]));
    }
    
    public function testIsAnObjectNotAnArray()
    {
        $arr = new \stdClass();
        $arr->a = 'b';
        
        $this->assertFalse($this->performMethod('isArray', [$arr]));
    }
    
    public function testIsAStringNotAnArray()
    {
        $this->assertFalse($this->performMethod('isArray', ['abcd']));
    }
    
    public function testIsEmptyArray()
    {
        $this->assertTrue($this->performMethod('isArray', [[]]));
    }
    
    protected function performMethod($method, $args)
    {
        $object = new Swagception\Validator\ArrayValidator();
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }
}