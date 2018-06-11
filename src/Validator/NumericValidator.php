<?php
namespace Swagception\Validator;

use Swagception\Exception;

class NumericValidator extends NumberBase implements CanValidate
{
    public function validate($schema, $json, $context)
    {
        if (isset($schema->format)) {
            $functionMapping = [
                'float' => 'validateFloat',
                'double' => 'validateDouble'
            ];
            if (isset($functionMapping[$schema->type])) {
                $this->{$functionMapping[$schema->type]}($schema, $json, $context);
                $this->validateNumericFields($schema, $json, $context);

                return;
            }
            //The format property is an open string-valued property, and can have any value to support documentation needs.
            //Therefore, allow unknown formats and fall back to regular checking.
        }

        //Check that the data is a number.
        if (!$this->isNumeric($json)) {
            throw new Exception\ValidationException(sprintf('%1$s is not a number.', $context));
        }

        $this->validateNumericFields($schema, $json, $context);
    }

    protected function validateFloat($schema, $json, $context)
    {
        //Check that the data is a float.
        if (!is_float($json)) {
            throw new Exception\ValidationException(sprintf('%1$s is not a float.', $context));
        }
    }

    protected function validateDouble($schema, $json, $context)
    {
        //Do doubles even exist anymore? Check for a float instead.
        $this->validateFloat($schema, $json, $context);
    }
}
