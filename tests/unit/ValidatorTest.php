<?php
use AspectMock\Test as test;

class ValidatorTest extends \Codeception\Test\Unit
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
    
    public function testNullableWithNull()
    {
        $schema = new \stdClass();
        $schema->nullable = true;
        $this->assertFalse($this->performMethod('validateNullable', [$schema, null, 'context']));
        $this->assertFalse($this->performMethod('validateNullable', [$schema, new \stdClass(), 'context']));
    }
    
    public function testNullableWithoutNull()
    {
        $schema = new \stdClass();
        $schema->nullable = true;
        $this->assertTrue($this->performMethod('validateNullable', [$schema, 'any', 'context']));
    }
    
    public function testNotNullableWithNull()
    {
        $schema = new \stdClass();
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateNullable', [$schema, null, 'context']);
        $schema->nullable = false;
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateNullable', [$schema, null, 'context']);
    }
    
    public function testNotNullableWithoutNull()
    {
        $schema = new \stdClass();
        $this->assertTrue($this->performMethod('validateNullable', [$schema, 'any', 'context']));
    }
    
    public function testValidEnum()
    {
        $schema = new \stdClass();
        $schema->enum = ['a', 'b', 'c'];
        $this->performMethod('validateEnum', [$schema, 'c', 'context']);
    }
    
    public function testInvalidEnum()
    {
        $schema = new \stdClass();
        $schema->enum = ['a', 'b', 'c'];
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateEnum', [$schema, 'd', 'context']);
    }
    
    public function testValidAllOf()
    {
        $schema1 = new \stdClass();
        $schema1->type = 'integer';
        $schema1->format = 'int32';
        
        $schema2 = new \stdClass();
        $schema2->type = 'integer';
        $schema2->format = 'int64';
        
        $schema = new \stdClass();
        $schema->allOf = array();
        $schema->allOf[] = $schema1;
        $schema->allOf[] = $schema2;
        
        //This is a valid int32 and int64.
        $json = 300;
        
        $this->performMethod('validateAllOf', [$schema, $json, 'context']);
    }
    
    public function testInvalidAllOf()
    {
        $schema1 = new \stdClass();
        $schema1->type = 'integer';
        $schema1->format = 'int32';
        
        $schema2 = new \stdClass();
        $schema2->type = 'string';
        
        $schema = new \stdClass();
        $schema->allOf = array();
        $schema->allOf[] = $schema1;
        $schema->allOf[] = $schema2;
        
        $json = 300;
        
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateAllOf', [$schema, $json, 'context']);
    }
    
    public function testValidAnyOf()
    {
        $schema1 = new \stdClass();
        $schema1->type = 'integer';
        $schema1->format = 'int32';
        
        $schema2 = new \stdClass();
        $schema2->type = 'string';
        
        $schema = new \stdClass();
        $schema->anyOf = array();
        $schema->anyOf[] = $schema1;
        $schema->anyOf[] = $schema2;
        
        $json = 300;
        
        $this->performMethod('validateAnyOf', [$schema, $json, 'context']);
    }
    
    public function testInvalidAnyOf()
    {
        $schema1 = new \stdClass();
        $schema1->type = 'string';
        $schema1->format = 'byte';
        
        $schema2 = new \stdClass();
        $schema2->type = 'string';
        
        $schema = new \stdClass();
        $schema->anyOf = array();
        $schema->anyOf[] = $schema1;
        $schema->anyOf[] = $schema2;
        
        $json = 300;
        
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateAnyOf', [$schema, $json, 'context']);
    }
    
    public function testValidOneOf()
    {
        $schema1 = new \stdClass();
        $schema1->type = 'integer';
        $schema1->format = 'int32';
        
        $schema2 = new \stdClass();
        $schema2->type = 'string';
        
        $schema = new \stdClass();
        $schema->oneOf = array();
        $schema->oneOf[] = $schema1;
        $schema->oneOf[] = $schema2;
        
        $json = 300;
        
        $this->performMethod('validateOneOf', [$schema, $json, 'context']);
    }
    
    public function testInvalidOneOfZeroMatches()
    {
        $schema1 = new \stdClass();
        $schema1->type = 'string';
        $schema1->format = 'byte';
        
        $schema2 = new \stdClass();
        $schema2->type = 'string';
        
        $schema = new \stdClass();
        $schema->oneOf = array();
        $schema->oneOf[] = $schema1;
        $schema->oneOf[] = $schema2;
        
        $json = 300;
        
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateOneOf', [$schema, $json, 'context']);
    }
    
    public function testInvalidOneOfMoreThanOneMatch()
    {
        $schema1 = new \stdClass();
        $schema1->type = 'integer';
        $schema1->format = 'int32';
        
        $schema2 = new \stdClass();
        $schema2->type = 'integer';
        $schema2->format = 'int64';
        
        $schema = new \stdClass();
        $schema->oneOf = array();
        $schema->oneOf[] = $schema1;
        $schema->oneOf[] = $schema2;
        
        $json = 300;
        
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateOneOf', [$schema, $json, 'context']);
    }
    
    public function testValidNot()
    {
        $schema1 = new \stdClass();
        $schema1->type = 'string';
        
        $schema = new \stdClass();
        $schema->not = $schema1;
        
        $json = 300;
        
        $this->performMethod('validateNot', [$schema, $json, 'context']);
    }
    
    public function testInvalidNot()
    {
        $schema1 = new \stdClass();
        $schema1->type = 'integer';
        $schema1->format = 'int32';
        
        $schema = new \stdClass();
        $schema->not = $schema1;
        
        $json = 300;
        
        $this->expectException(Swagception\Exception\ValidationException::class);
        $this->performMethod('validateNot', [$schema, $json, 'context']);
    }
    
    protected function performMethod($method, $args)
    {
        $object = new Swagception\Validator\Validator();
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }
}
