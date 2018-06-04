<?php
namespace Swagception\Validator;

use Swagception\Exception;

class IntegerValidator extends NumberBase implements CanValidate
{
    public function validate($schema, $json, $context)
    {
        if (isset($schema->format)) {
            //int32	signed 32 bits
            //int64	signed 64 bits
            $functionMapping = [
                'int32' => 'validateInt32',
                'int64' => 'validateInt64'
            ];
            if (isset($functionMapping[$schema->type])) {
                $this->{$functionMapping[$schema->type]}($schema, $json, $context);
                $this->validateNumericFields($schema, $json, $context);

                return;
            }
            //The format property is an open string-valued property, and can have any value to support documentation needs.
            //Therefore, allow unknown formats and fall back to regular checking.
        }

        //Check that the data is an integer.
        if (!is_int($json)) {
            throw new Exception\ValidationException(sprintf('%1$s is not an integer.', $context));
        }

        $this->validateNumericFields($schema, $json, $context);
    }

    protected function validateInt32($schema, $json, $context)
    {
        if (!is_int($json)) {
            throw new Exception\ValidationException(sprintf('%1$s is not an integer.', $context));
        }

        if ($json > 2147483647 || $json < -2147483648) {
             throw new Exception\ValidationException(sprintf('%1$s is not a 32-bit integer (between -2147483648 and 2147483647).', $context));
        }
    }

    protected function validateInt64($schema, $json, $context)
    {
        $error64 = sprintf('%1$s is not a 64-bit integer (between -9223372036854775807 and 9223372036854775807).', $context);
        $maxInt64 = 9223372036854775807;
        $minInt64 = -9223372036854775807;

        //PHP is a funny bitch. If an int overflows it changes to a float, and any comparison you make returns false.
        if (is_float($json) && !($json < $maxInt64) && !($json > $maxInt64) && !($json === $maxInt64)) {
            throw new Exception\ValidationException($error64);
        }

        if (is_float($json) && !($json < $minInt64) && !($json > $minInt64) && !($json === $minInt64)) {
            throw new Exception\ValidationException($error64);
        }

        if (!is_int($json)) {
            throw new Exception\ValidationException(sprintf('%1$s is not an integer.', $context));
        }

        //Assuming that at some stage there are ints greater than 64bit, do a regular comparison.
        if ($json > $maxInt64 || $json < $minInt64) {
             throw new Exception\ValidationException($error64);
        }
    }
}