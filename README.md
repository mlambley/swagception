# Swagception
Validate your API against Swagger 2.0 using Codeception

## How to Install
```
composer require --dev mlambley/swagception
```

## Alpha release
This library still needs tests written before it can be properly released.

## What is Swagger?
Swagger 2.0 (aka Open API 2.0) defines the structure of your API, including end points and the structure of input and output data.
See [their website](https://swagger.io/) for more information.

## What is Swagception?
If you have an existing Swagger 2.0 specification, you can use it to validate your API using this tool.
This tool is designed to work with [Codeception](https://codeception.com/). Specifically, it automates the generation of acceptance tests for your API.
It will not validate your specification itself. It will only validate that your API matches your existing specification.

## Why Swagception?
I could not find any API validator which uses Swagger and is designed specifically for Codeception.
Also, this library aims to fully take into account the features of the [Swagger 2.0 specification](https://swagger.io/docs/specification/2-0/basic-structure/).

## Acceptance test generation
The paths in your Swagger specification will look something like `/api/entity/{entityID}/other/{otherID}`
How then do we generate an actual url using real entity ids which will produce a real response?
By default, this library will do this using the `enum` and `x-example` fields. However, it is more useful to be able to specify your own ids.
You can create a handler for each entity. Use annotations to link them to one or more paths. Then, all you need to do is tell the system where to find them.

```
namespace My\API\PathHandlers

/**
 * @path /api/entity/{entityID}/other/
 * @path /api/entity/{entityID}/other/{otherID}
 */
class MyPathHandler implements \Swagception\PathHandler\HandlesPath
{
    public function convertPath($path, $method = 'get')
    {
        //Replace {entityID} with a real id
        if (strpos($path, '{entityID}') !== false) {
            $path = str_replace('{entityID}', $this->getEntityID(), $path);
        }

        //Replace {otherID} with a real id
        if (strpos($path, '{otherID}') !== false) {
            $path = str_replace('{otherID}', $this->getOtherID(), $path);
        }

        return $path;
    }

    protected function getEntityID()
    {
        //$ids = 100 valid entity ids. Pulled from either the database or the api.
        return $ids[mt_rand(0, 99)];
    }

    protected function getOtherID()
    {
        //$ids = 100 valid other ids. Pulled from either the database or the api.
        return $ids[mt_rand(0, 99)];
    }
}
```

## Cest structure
Now that you have some real data at your disposal, it's time to link it all together in your Codeception cest.
The recommended way is to feed the paths (end points) into a cest data provider. Your terminal will then output a line for each path.
```
class MyCest
{
    protected $swaggerSchema;

    public function __construct()
    {
        //Configure the swagger schema wrapper.
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

        //Configure the path handler loader.
        $this->swaggerSchema->getPathHandlerLoader()
            //Set this if you are using your own path handlers, and not relying upon enum and x-example.
            ->withNamespace('My\\API\\PathHandlers')

            //Set this if your path handler classes have not been loaded into the system yet.
            ->withFilePath('/path/to/pathhandlers/')
        ;

        //Configure the URL Retriever.
        $this->swaggerSchema->getURLRetriever()
            //Set GuzzleHttp constructor args. This example turns off ssl verification.
            ->withArgs(['verify' => false])

            //Set GuzzleHttp request options. This example sets headers.
            ->withOptions([
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ])
        ;

    }

    /**
     * @dataProvider _pathProvider
     */
    public function path(MyTester $I, \Codeception\Scenario $S, \Codeception\Example $data)
    {
        $path = $data[0];
        $this->swaggerSchema->testPath($I, $path);
    }

    protected function _pathProvider()
    {
        $pathList = array();
        foreach ($this->swaggerSchema->paths as $path => $pathData) {
            foreach ($pathData as $action => $actionData) {
                //We only check get requests here.
                if ($action !== 'get') {
                    continue;
                }

                $pathList[] = array($this->swaggerSchema->convertPath($path));
            }
        }

        return $pathList;
    }
}

```

Alternatively, you can loop through them in a single function.
```
public function paths(MyTester $I, \Codeception\Scenario $S)
{
    foreach ($this->schema->paths as $path => $pathData) {
        foreach ($pathData as $action => $actionData) {
            //We only check get requests here.
            if ($action !== 'get') {
                continue;
            }

            $this->swaggerSchema->testPath($I, $path);
        }
    }
}
```

## Did this library work for you?
Show your support by starring me at [Github](https://github.com/mlambley/swagception/)

## Did this library not work for you?
Log me a [github issue](https://github.com/mlambley/swagception/issues) detailing why it didn't work for you.
It could be that a small change will make a big difference.

