<?php
class FilterCest
{
    protected $swaggerSchema;
    
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
        $this->swaggerSchema->testPath($path);
    }
    
    public function _dataProvider()
    {
        $this->swaggerSchema = \Swagception\SwaggerSchema::Create()
            ->withFilters(['/users/'])
            ->withSchemaURI('file:///' . __DIR__ . '/../_support/Dummy/swagger.json')
            ->withURLRetriever(new \tests\Dummy\DummyURLRetriever())
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
