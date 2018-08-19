<?php
class InvalidDataCest
{
    use \Swagception\ContainerTrait;
    
    public function __construct()
    {
        //Configure the swagger schema object.
        $this->swaggerContainer = new \Swagception\Container\Container();

        $this->swaggerContainer->getSchema()
            ->withSchemaURI('file:///' . __DIR__ . '/../_support/Dummy/swagger.json')
        ;
        
        $this->swaggerContainer->getPathHandlerLoader()
            //Will force an exception if a path handler is not loaded, and will not fall back to the default.
            ->useDefaultPathHandler(false)
            ->withNamespace('\\tests\\Dummy\\')
            ->withFilePath(__DIR__ . '/../_support/Dummy/')
        ;
        
        $this->swaggerContainer->withRequestRunner(new tests\Dummy\DummyRunner(\tests\Dummy\DummyRunner::MODE_INVALID));
    }
    
    /**
     * @dataProvider _dataProvider
     */
    public function testSchema(AcceptanceTester $I, \Codeception\Scenario $S, Codeception\Example $data)
    {
        $path = $data[0];
        $I->wantTo('Invalid response throws error: ' . $data[0]);
        
        $swaggerSchema = $this->swaggerContainer->getSchema();
        $path = $data[0];
        $I->expectException(Swagception\Exception\ValidationException::class, function () use ($swaggerSchema, $path) {
            $swaggerSchema->testPath($path);
        });
    }
    
    public function _dataProvider()
    {
        return array_map(function ($val) {
            return [$val];
        }, $this->swaggerContainer->getSchema()->getPaths());
    }
}
