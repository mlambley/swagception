<?php
namespace Swagception\Validator;

use Swagception\Exception;

class BooleanValidator implements CanValidate
{
    public function validate($schema, $json, $context)
    {
        //Check that the data is a boolean.
        if (!is_bool($json)) {
            throw new Exception\ValidationException(sprintf('%1$s is not a boolean.', $context));
        }
    }
}