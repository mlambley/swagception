<?php
namespace Swagception\Validator;

use Swagception\Exception;

/**
 * All have the common fields from the JSON Schema: enum, type, allOf, anyOf, oneOf, not, definitions
 * And extra fields from Swagger: format, title, description, default
 */
class Validator implements CanValidate
{
    public function validate($schema, $json, $context = '')
    {
        //If the json is null, then everything except type:null will fail, unless nullable is specified.
        $continueValidating = $this->validateNullable($schema, $json, $context);
        if (!$continueValidating) {
            return;
        }

        if (isset($schema->type)) {
            $this->validateType($schema, $json, $context);
        }

        if (isset($schema->enum)) {
            $this->validateEnum($schema, $json, $context);
        }

        if (isset($schema->allOf)) {
            $this->validateAllOf($schema, $json, $context);
        }

        if (isset($schema->anyOf)) {
            $this->validateAnyOf($schema, $json, $context);
        }

        if (isset($schema->oneOf)) {
            $this->validateOneOf($schema, $json, $context);
        }

        if (isset($schema->not)) {
            $this->validateNot($schema, $json, $context);
        }

        if (isset($schema->definitions)) {
            $this->validateDefinitions($schema, $json, $context);
        }
    }

    protected function validateNullable($schema, $json, $context)
    {
        //I have taken the liberty of borrowing the nullable property from Swagger 3.0 https://swagger.io/specification/
        //Otherwise the spec is plainly unworkable. You end up with hooks in odd places handling nullables in one of a million dodgy ways.

        //If we allow nullable, and the json is null or empty object, then pass.
        if (isset($schema->nullable) && $schema->nullable === true) {
            if (($json === null) || (is_object($json) && empty(get_object_vars($json)))) {
                return false;
            }
        } else if ($json === null) {
            //If nullable is not specified, only allow if type is null.
            if (!isset($schema->type) || $schema->type !== 'null') {
                throw new Exception\ValidationException(sprintf('%1$s is null. Set type:null or nullable:true to allow null fields.', $context));
            }
        }

        return true;
    }

    protected function validateType($schema, $json, $context)
    {
        //The value of this keyword MUST be either a string or an array.  If it is an array, elements of the array MUST be strings and MUST be unique.
        //String values MUST be one of the seven primitive types defined by the core specification.
        //SWAGGER says: the value must be a single type and not an array of types.

        //array  A JSON array.
        //boolean  A JSON boolean.
        //integer  A JSON number without a fraction or exponent part.
        //number  Any JSON number.  Number includes integer.
        //null  The JSON null value.
        //object  A JSON object.
        //string  A JSON string.

        $classMapping = [
            'array' => ArrayValidator::class,
            'boolean' => BooleanValidator::class,
            'integer' => IntegerValidator::class,
            'number' => NumericValidator::class,
            'null' => NullValidator::class,
            'object' => ObjectValidator::class,
            'string' => StringValidator::class
        ];

        if (isset($classMapping[$schema->type])) {
            $className = $classMapping[$schema->type];

            (new $className())
                ->validate($schema, $json, $context);
        } else {
            throw new Exception\ValidationException(sprintf('Unknown schema type %1$s.', $schema->type));
        }
    }

    protected function validateEnum($schema, $json, $context)
    {
        //The value of this keyword MUST be an array.  This array MUST have at least one element.  Elements in the array MUST be unique. Elements in the array MAY be of any type, including null.
        //An instance validates successfully against this keyword if its value is equal to one of the elements in this keyword's array value.
        if (!in_array($schema->enum, $json)) {
            throw new Exception\ValidationException(sprintf('%1$s must be one of "%2$s".', $context, implode('", "', $schema->enum)));
        }
    }

    protected function validateAllOf($schema, $json, $context)
    {
        //This keyword's value MUST be an array. This array MUST have at least one element. Elements of the array MUST be objects. Each object MUST be a valid JSON Schema.
        //An instance validates successfully against this keyword if it validates successfully against all schemas defined by this keyword's value.
        foreach ($schema->allOf as $subschema) {
            //No need to collate the error messages.
            (new Validator())
                ->validate($subschema, $json, $context);
        }
    }

    protected function validateAnyOf($schema, $json, $context)
    {
        //This keyword's value MUST be an array. This array MUST have at least one element. Elements of the array MUST be objects. Each object MUST be a valid JSON Schema.
        //An instance validates successfully against this keyword if it validates successfully against at least one schema defined by this keyword's value.
        $errors = [];
        foreach ($schema->anyOf as $subschema) {
            try {
                (new Validator())
                    ->validate($subschema, $json, $context);

                //Hey, one worked!
                return;
            } catch (Exception\ValidationException $e) {
                $errors[] = $e->getMessage();
            }
        }

        throw new Exception\ValidationException(sprintf('%1$s did not match any of the specified \'anyOf\' schemas. Error messages are "%2$s"', $context, implode('", "', $errors)));
    }

    protected function validateOneOf($schema, $json, $context)
    {
        //This keyword's value MUST be an array. This array MUST have at least one element. Elements of the array MUST be objects. Each object MUST be a valid JSON Schema.
        //An instance validates successfully against this keyword if it validates successfully against exactly one schema defined by this keyword's value.
        $errors = [];
        $passCount = 0;
        $failCount = 0;
        foreach ($schema->oneOf as $subschema) {
            try {
                (new Validator())
                    ->validate($subschema, $json, $context);
                $passCount++;
            } catch (Exception\ValidationException $e) {
                $failCount++;
                $errors[] = $e->getMessage();
            }
        }

        if ($passCount > 1) {
            //Yes it really is strictly "one of". It's a silly concept in the first place.
            throw new Exception\ValidationException(sprintf(
                '%1$s matched more than one of the \'oneOf\' schemas, but more than one matched. Did you mean to use \'anyOf\'? Error messages are "%2$s"',
                $context,
                implode('", "', $errors)
            ));
        } else if ($passCount === 0) {
            throw new Exception\ValidationException(sprintf('%1$s did not match one of the specified \'oneOf\' schemas. Error messages are "%2$s"', $context, implode('", "', $errors)));
        }
    }

    protected function validateNot($schema, $json, $context)
    {
        //This keyword's value MUST be an object. This object MUST be a valid JSON Schema.
        //An instance is valid against this keyword if it fails to validate successfully against the schema defined by this keyword.
        try {
            (new Validator())
                ->validate($schema->not, $json, $context);

            throw new Exception\ValidationException(sprintf('%1$s successfully passed the specified \'not\' schema.', $context));
        } catch (Exception\ValidationException $e) {
            //If you try to fail and succeed, which have you done?
        }
    }

    protected function validateDefinitions($schema, $json, $context)
    {
        //This keyword plays no role in validation per se. Its role is to provide a standardized location for schema authors to inline JSON Schemas into a more general schema.
    }
}