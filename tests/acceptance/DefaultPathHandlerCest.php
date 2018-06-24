<?php
class DefaultPathHandlerCest
{
    protected $swaggerSchema;
    
    /**
     * @dataProvider _dataProvider
     */
    public function testSchema(AcceptanceTester $I, \Codeception\Scenario $S, Codeception\Example $data)
    {
        $path = $data[0];
        $I->wantTo('Default path handler: ' . $data[0]);
        $this->swaggerSchema->testPath($path);
    }
    
    public function _dataProvider()
    {
        $this->swaggerSchema = \Swagception\SwaggerSchema::Create()
            ->withSchemaURI('file:///' . __DIR__ . '/../_support/Dummy/swagger.json')
            ->withURLRetriever(new \tests\Dummy\DummyURLRetriever())
        ;
        
        return array_map(function ($val) {
            return [$val];
        }, $this->swaggerSchema->getPaths());
    }
}
