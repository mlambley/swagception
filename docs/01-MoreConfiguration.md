# Swagception

## More configuration
You can customise the default objects loaded by Swagception.

```php
$this->swaggerSchema = \Swagception\SwaggerSchema::Create()
    //Path to your existing Swagger specification
    ->withSchemaURI('/path/to/swagger.json')

    //Optional: Tell the system to not fall back to enum and x-example to generate URIs
    ->useDefaultPathHandler(false)

    //Optional: These can be specified in your spec, but can be overridden here
    ->withScheme('https')
    ->withHost('your.api.com')
    ->withBasePath('/api')

    //Optional: Configure your path handlers by calling a closure each time one is generated.
    ->applyToPathHandlers(function($PathHandler) use ($ExtraVariable) {
        $PathHandler->setExtraVariable($ExtraVariable);
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
