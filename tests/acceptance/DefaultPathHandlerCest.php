<?php
class DefaultPathHandlerCest
{
    use \Swagception\ContainerTrait;
    
    public function __construct()
    {
        $this->swaggerContainer = new \Swagception\Container\Container();

        $this->swaggerContainer->getSchema()
            ->withSchemaURI('file:///' . __DIR__ . '/../_support/Dummy/swagger.json')
        ;
        
        $this->swaggerContainer->withRequestRunner(new tests\Dummy\DummyRunner());
    }
    
    /**
     * @dataProvider _dataProvider
     */
    public function testSchema(AcceptanceTester $I, \Codeception\Scenario $S, Codeception\Example $data)
    {
        $path = $data[0];
        $I->wantTo('Default path handler: ' . $data[0]);
        $this->swaggerContainer->getSchema()->testPath($path);
    }
    
    public function _dataProvider()
    {
        return array_map(function ($val) {
            return [$val];
        }, $this->swaggerContainer->getSchema()->getPaths());
    }
}
