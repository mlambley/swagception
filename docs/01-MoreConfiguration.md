# Swagception

## More configuration
You can customise the default objects loaded by Swagception.

```php
$this->swaggerSchema
    //Specify your own schema, instead of loading from a file using withSchemaURI
    ->withSchema($otherSchema)

    //These can be specified in your spec, but can be overridden here
    ->withScheme('https')
    ->withHost('your.api.com')
    ->withBasePath('/api')
    
    //Only test paths which include one of the specified strings
    ->withFilters(['/api/entity/', '/api/something/'])
    
    //Specify your own URL retriever which implements \Swagception\URLRetriever\CanRetrieveURLs
    ->withURLRetriever($myURLRetriever)

    //Specify your own path handler which implements \Swagception\PathHandlerLoader\LoadsPathHandlers
    ->withPathHandlerLoader($myURLRetriever)
;

$this->swaggerSchema->getPathHandlerLoader()
    //Tell the system to not fall back to enum and x-example to generate URIs
    ->useDefaultPathHandler(false)

    //Configure your path handlers by calling a closure each time one is generated.
    ->onHandlerLoad(function($PathHandler) use ($ExtraVariable) {
        $PathHandler->setExtraVariable($ExtraVariable);
    })

    //You can also call a closure each time one is unloaded. Useful if you've created temporary data and need to clean it up.
    //The handlers are unloaded after every call to testPath.
    ->onHandlerUnload(function($PathHandler) {
        $PathHandler->unload();
    })
;

//Configure the URL Retriever. It is a \GuzzleHttp\Client unless you have overridden it.
$this->swaggerSchema->getURLRetriever()
    //Set GuzzleHttp constructor args. This example turns off ssl verification.
    ->withArgs(['verify' => false])

    //Set GuzzleHttp request options. This example sets headers.
    ->withOptions([
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer youroauth2tokenbiglongstring'
        ]
    ])
;
```
