<?php
class DefaultPathHandlerCest
{
    /** @var \Swagception\SwaggerSchema */
    protected $swaggerSchema;

    /**
     * @dataProvider _dataProvider
     * @throws \Swagception\Exception\ValidationException
     */
    public function testSchema(AcceptanceTester $I, \Codeception\Scenario $S, Codeception\Example $data)
    {
        $path = $data['path'];
        $code = $data['code'];

        $method = $data['method'];
        $I->wantTo("Default path handler: {$method} {$path} | Status Code: {$code}");
        $this->swaggerSchema->testPath($path, $method, $code);
    }
    
    public function _dataProvider()
    {
        $this->swaggerSchema = \Swagception\SwaggerSchema::Create()
            ->withSchemaURI('file:///' . __DIR__ . '/../_support/Dummy/swagger.json')
            ->withURLRetriever(new \tests\Dummy\DummyURLRetriever())
        ;

        return $this->swaggerSchema->getPaths();
    }
}
