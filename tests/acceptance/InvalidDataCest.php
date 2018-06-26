<?php
class InvalidDataCest
{
    protected $swaggerSchema;
    
    /**
     * @dataProvider _dataProvider
     */
    public function testSchema(AcceptanceTester $I, \Codeception\Scenario $S, Codeception\Example $data)
    {
        $path = $data[0];
        $I->wantTo('Invalid response throws error: ' . $data[0]);
        
        $swaggerSchema = $this->swaggerSchema;
        $path = $data[0];
        $I->expectException(Swagception\Exception\ValidationException::class, function () use ($swaggerSchema, $path) {
            $swaggerSchema->testPath($path);
        });
    }
    
    public function _dataProvider()
    {
        $this->swaggerSchema = \Swagception\SwaggerSchema::Create()
            ->withSchemaURI('file:///' . __DIR__ . '/../_support/Dummy/swagger.json')
            ->withURLRetriever(new \tests\Dummy\DummyURLRetriever(\tests\Dummy\DummyURLRetriever::MODE_INVALID))
        ;
        
        $this->swaggerSchema->getPathHandlerLoader()
            //Will force an exception if a path handler is not loaded, and will not fall back to the default.
            ->useDefaultPathHandler(false)
            ->withNamespace('\\tests\\Dummy\\')
            ->withFilePath(__DIR__ . '/../_support/Dummy/')
        ;
        
        return array_map(function ($val) {
            return [$val];
        }, $this->swaggerSchema->getPaths());
    }
}
