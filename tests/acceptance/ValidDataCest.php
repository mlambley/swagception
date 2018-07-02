<?php
class ValidDataCest
{
    protected $swaggerSchema;
    
    /**
     * @dataProvider _dataProvider
     */
    public function testSchema(AcceptanceTester $I, \Codeception\Scenario $S, Codeception\Example $data)
    {
        $path = $data['path'];
        $method = $data['method'];
        $code = $data['code'];
        $I->wantTo("Valid data: {$method} {$path} | Status Code: {$code}");
        $this->swaggerSchema->testPath($path, $method, $code);
    }
    
    public function _dataProvider()
    {
        $this->swaggerSchema = \Swagception\SwaggerSchema::Create()
            ->withSchemaURI('file:///' . __DIR__ . '/../_support/Dummy/swagger.json')
            ->withURLRetriever(new \tests\Dummy\DummyURLRetriever())
        ;
        
        $this->swaggerSchema->getPathHandlerLoader()
            //Will force an exception if a path handler is not loaded, and will not fall back to the default.
            ->useDefaultPathHandler(false)
            ->withNamespace('\\tests\\Dummy\\')
            ->withFilePath(__DIR__ . '/../_support/Dummy/')
        ;
        
        return $this->swaggerSchema->getPaths();
    }
}
