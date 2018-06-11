<?php
namespace Swagception\Validator;

use Swagception\Exception;

class NullValidator implements CanValidate
{
    public function validate($schema, $json, $context)
    {
        //Check that the data is null.
        if ($json !== null) {
            throw new Exception\ValidationException(sprintf('%1$s is not null.', $context));
        }
    }
}
