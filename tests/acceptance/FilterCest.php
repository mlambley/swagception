<?php
class FilterCest
{
    use \Swagception\ContainerTrait;
    
    public function __construct()
    {
        //Configure the swagger schema object.
        $this->swaggerContainer = new \Swagception\Container\Container();

        $this->swaggerContainer->getSchema()
            ->withFilters(['/users/'])
            ->withSchemaURI('file:///' . __DIR__ . '/../_support/Dummy/swagger.json')
        ;
        
        $this->swaggerContainer->getPathHandlerLoader()
            //Will force an exception if a path handler is not loaded, and will not fall back to the default.
            ->useDefaultPathHandler(false)
            ->withNamespace('\\tests\\Dummy\\')
            ->withFilePath(__DIR__ . '/../_support/Dummy/')
        ;

        $this->swaggerContainer->withRequestRunner(new tests\Dummy\DummyRunner());
    }
    
    /**
     * @dataProvider _dataProvider
     */
    public function testSchema(AcceptanceTester $I, \Codeception\Scenario $S, Codeception\Example $data)
    {
        $I->wantTo('Filtered data: ' . $data[0]);
        $path = $data[0];
        if (strpos($path, '/comments/') === 0) {
            $I->fail(sprintf('Path %1$s was tested, although filters should have blocked it out.', $path));
        }
        $this->swaggerContainer->getSchema()->testPath($path);
    }
    
    public function _dataProvider()
    {
        return array_map(function ($val) {
            return [$val];
        }, $this->swaggerContainer->getSchema()->getPaths());
    }
}
