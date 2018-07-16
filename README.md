# Swagception
Validate your API against Swagger 2.0 using Codeception

## How to Install
```
composer require --dev mlambley/swagception
```

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

```php
namespace My\API\PathHandlers

/**
 * @path /api/entity/{entityID}/other/
 * @path /api/entity/{entityID}/other/{otherID}
 */
class MyPathHandler implements \Swagception\PathHandler\HandlesPath
{
    public function convertPath($path)
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

## Swagception extension
Now that you have some real data at your disposal, it's time to configure your codeception environment for Swagception.
You only need to set up one extension. More information about codeception extensions is available [here](https://codeception.com/docs/08-Customization#Extension).
Put the following in your codeception.yml or suite.yml file:
```yaml
extensions:
  enabled:
    - \Swagception\Extension\Swagception
```

## Cest structure
With the full functionality of Swagception enabled, it's time to link everything together in your Codeception cest. 
The recommended way is to feed the paths (end points) into a cest data provider. Your terminal will then output a line for each path.
```php
class MyCest
{
    use \Swagception\Schema;

    public function __construct()
    {
        //Configure the swagger schema object.
        $this->swaggerSchema = \Swagception\SwaggerSchema::Create()
            //Path to your existing Swagger specification
            ->withSchemaURI('/path/to/swagger.json')
        ;

        //Configure the path handler loader.
        $this->swaggerSchema->getPathHandlerLoader()
            //Set this if you are using your own path handlers, and not relying upon enum and x-example.
            ->withNamespace('My\\API\\PathHandlers')

            //Set this if your path handler classes have not been loaded into the system yet.
            ->withFilePath('/path/to/pathhandlers/')
        ;
    }

    /**
     * @dataProvider _pathProvider
     */
    public function path(MyTester $I, \Codeception\Scenario $S, \Codeception\Example $data)
    {
        $path = $data[0];
        $this->swaggerSchema->testPath($path);
    }

    protected function _pathProvider()
    {
        //Will return an array of arrays.
        return array_map(function($val) {
            return [$val];
        }, $this->swaggerSchema->getPaths());
    }
}
```

Alternatively, you can loop through them in a single function.
```php
public function paths(MyTester $I, \Codeception\Scenario $S)
{
    foreach ($this->swaggerSchema->getPaths() as $path) {
        $this->swaggerSchema->testPath($path);
    }
}
```

Or, if you already have the json and the schema objects, you can call the validation method directly.
```php
(new \Swagception\Validator\Validator())
    ->validate($schema, $json);
```

## More settings
See more [configuration options](/docs/01-MoreConfiguration.md).

## Did this library work for you?
Show your support by starring this project at [Github](https://github.com/mlambley/swagception/)

## Did this library not work for you?
Log me a [github issue](https://github.com/mlambley/swagception/issues) detailing how it didn't work for you. Your assistance is appreciated.
