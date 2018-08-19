# Swagception

## More configuration
You can customise the default objects loaded by Swagception.

```php
$this->swaggerContainer->getSchema()
    //Specify your own schema, instead of loading from a file using withSchemaURI
    ->withSchema($otherSchema)

    //These can be specified in your spec, but can be overridden here
    ->withScheme('https')
    ->withHost('your.api.com')
    ->withBasePath('/api')

    //Only test paths which include one of the specified strings
    ->withFilters(['/api/entity/', '/api/something/'])

    //Generate a \Swagception\Exception\ResponseEmptyException if a HTML response is empty
    ->withErrorOnEmpty(true)

    //Include an HTML reporter
    ->withAddedReporter(
        (new \Swagception\Reporter\HTMLReporter())
            ->withFileName(codecept_output_dir() . '/MyReport.html')
    )
;

$this->swaggerContainer->getPathHandlerLoader()
    //Tell the system to not fall back to enum and x-example to generate URIs
    ->useDefaultPathHandler(false)
;

$this->swaggerContainer->getHandlerContainer()
    //Configure your path handlers by calling a closure each time one is generated.
    ->onHandlerLoad(function($PathHandler) use ($ExtraVariable) {
        $PathHandler->setExtraVariable($ExtraVariable);
    })

    //You can also call a closure each time a handler is unloaded. Useful if you've created temporary data and need to clean it up.
    //This will be called automatically after the test suite finishes.
    ->onHandlerUnload(function($PathHandler) {
        $PathHandler->unload();
    })
;

$this->swaggerContainer->getRequestRunner()
    //Set GuzzleHttp constructor args. This example turns off ssl verification.
    ->withArgs(['verify' => false])
;

$this->swaggerContainer->getRequestGenerator()
    //Set headers to be sent with every API request.
    ->withStandardHeaders([
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer youroauth2tokenbiglongstring'
        ]
    ])
;
```

## Why the swagception extension?
There are limitations on what functionality is available from within a Codeception cest. This is why they included extensions.
The swagception extension, however, is not required for the actual validation itself. 
If you do not use reporters, and you do not require your path handlers to be unloaded, you can omit the extension.

## Override any class
Swagception is designed such that any core class can be replaced with one which implements the corresponding interface. 
Path handler loader, for example, can be replaced with anything which implements `\Swagception\PathHandlerLoader\LoadsPathHandlers`
by either calling `$container->withPathHandlerLoader` or by overriding `loadDefaultPathHandlerLoader`.
If you come across a class which cannot be customised, or you are in any way limited by the functionality provided here, please log me a ticket.
