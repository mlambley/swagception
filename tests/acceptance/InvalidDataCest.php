<?php
class InvalidDataCest
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
        $I->wantTo("Invalid response throws error: {$method} {$path} | Status Code: {$code}");
        $swaggerSchema = $this->swaggerSchema;
        $I->expectException(Swagception\Exception\ValidationException::class, function () use ($swaggerSchema, $path, $method, $code) {
            $swaggerSchema->testPath($path, $method, $code);
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

        return array_filter($this->swaggerSchema->getPaths(), function ($item) {
            return $item['method'] === 'get';
        });
    }
}
