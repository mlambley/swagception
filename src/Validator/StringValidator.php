<?php
namespace Swagception\Validator;

use Swagception\Exception;

class StringValidator implements CanValidate
{
    public function validate($schema, $json, $context)
    {
        //Check that the data is a string.
        if (!is_string($json)) {
            throw new Exception\ValidationException(sprintf('%1$s is not a string.', $context));
        }

        if (isset($schema->format)) {
            $this->validateFormat($schema, $json, $context);
        }

        if (isset($schema->maxLength)) {
            $this->validateMaxLength($schema, $json, $context);
        }

        if (isset($schema->minLength)) {
            $this->validateMinLength($schema, $json, $context);
        }

        if (isset($schema->pattern)) {
            $this->validatePattern($schema, $json, $context);
        }
    }

    protected function validateFormat($schema, $json, $context)
    {
        //byte base64 encoded characters
        //binary any sequence of octets
        //date As defined by full-date - RFC3339
        //date-time As defined by date-time - RFC3339
        $functionMapping = [
            'byte' => 'validateByte',
            'binary' => 'validateBinary',
            'date' => 'validateDate',
            'date-time' => 'validateDateTime',
            'password' => 'validatePassword'
        ];
        if (isset($functionMapping[$schema->type])) {
            $this->{$functionMapping[$schema->type]}($schema, $json, $context);
        }
        //The format property is an open string-valued property, and can have any value to support documentation needs.
        //Therefore, allow unknown formats and fall back to regular checking.
    }

    protected function validateByte($schema, $json, $context)
    {
        if (preg_match('/^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$/', $json) !== 1) {
            throw new Exception\ValidationException(sprintf('%1$s "%2$s" is not valid byte (Base64) data.', $context, $json));
        }
    }

    protected function validateBinary($schema, $json, $context)
    {
        if (!is_binary($json)) {
            //Is it even possible to have binary data in a json string?
            throw new Exception\ValidationException(sprintf('%1$s is not binary data.', $context));
        }
    }

    protected function validateDate($schema, $json, $context)
    {
        //Eg. 2018-05-31
        $format = 'YY-MM-DD';
        $date = DateTime::createFromFormat($format, $json);
        if (!$date || ($date->format($format) !== $json)) {
            throw new Exception\ValidationException(sprintf('%1$s "%2$s" is not a valid date (YYYY-MM-DD).', $context, $json));
        }
    }

    protected function validateDatetime($schema, $json, $context)
    {
        //2002-10-02T10:00:00-05:00
        //2002-10-02T15:00:00Z
        //2002-10-02T15:00:00.05Z
        $rfc3339DateTimeRegex = '/^(\d{4})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])T([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]|60)(\.[0-9]+)?(Z|(\+|-)([01][0-9]|2[0-3]):([0-5][0-9]))$/';

        if (preg_match($rfc3339DateTimeRegex, $json) !== 1) {
            throw new Exception\ValidationException(sprintf('%1$s "%2$s" is not a valid date-time (See RFC3339).', $context, $json));
        }
    }

    protected function validatePassword($schema, $json, $context)
    {
        //There is no special format here. Just used to hint UIs the input needs to be obscured.
    }

    protected function validateMaxLength($schema, $json, $context)
    {
        //A string instance is valid against this keyword if its length is less than, or equal to, the value of this keyword.
        if (mb_strlen($json) > $schema->maxLength) {
            throw new Exception\ValidationException(sprintf('%1$s "%2$s" has too many characters (max length is %3$s).', $context, $json, $schema->maxLength));
        }
    }

    protected function validateMinLength($schema, $json, $context)
    {
        //A string instance is valid against this keyword if its length is greater than, or equal to, the value of this keyword.
        if (mb_strlen($json) < $schema->minLength) {
            throw new Exception\ValidationException(sprintf('%1$s "%2$s" has too few characters (min length is %3$s).', $context, $json, $schema->minLength));
        }
    }

    protected function validatePattern($schema, $json, $context)
    {
        //A string instance is considered valid if the regular expression matches the instance successfully.
        //Regular expressions are not implicitly anchored.

        $regex = $schema->pattern;

        //Double-escape backslashes because PHP that's why.
        $regex = str_replace('\\', '\\\\', $regex);

        //Add in the forward slashes.
        $regex = '/' . $regex . '/';

        if (preg_match($regex, $json) !== 1) {
            throw new Exception\ValidationException(sprintf('%1$s "%2$s" does not match the pattern "%3$s"', $context, $json, $schema->pattern));
        }
    }
}
