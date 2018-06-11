<?php
namespace Swagception\Validator;

use Swagception\Exception;

/**
 * Arrays have additional fields: items, maxItems, minItems, uniqueItems
 */
class ArrayValidator implements CanValidate
{
    public function validate($schema, $json, $context)
    {
        //Check that the data is an array (and not a struct).
        if (!$this->isArray($json)) {
            throw new Exception\ValidationException(sprintf('%1$s is not an array.', $context));
        }

        if (isset($schema->maxItems)) {
            $this->validateMaxItems($schema, $json, $context);
        }

        if (isset($schema->minItems)) {
            $this->validateMinItems($schema, $json, $context);
        }

        if (isset($schema->uniqueItems)) {
            $this->validateUniqueItems($schema, $json, $context);
        }

        $this->validateItems($schema, $json, $context);
    }

    protected function validateMaxItems($schema, $json, $context)
    {
        //The value of this keyword MUST be an integer.  This integer MUST be greater than, or equal to, 0.
        //An array instance is valid against "maxItems" if its size is less than, or equal to, the value of this keyword.

        if (count($json) > $schema->maxItems) {
            throw new Exception\ValidationException(sprintf('%1$s has too many items. %2$s found but at most %3$s expected.', $context, count($json), $schema->maxItems));
        }
    }

    protected function validateMinItems($schema, $json, $context)
    {
        //The value of this keyword MUST be an integer.  This integer MUST be greater than, or equal to, 0.
        //An array instance is valid against "minItems" if its size is greater than, or equal to, the value of this keyword.

        if (count($json) < $schema->minItems) {
            throw new Exception\ValidationException(sprintf('%1$s has too few items. %2$s found but at least %3$s expected.', $context, count($json), $schema->minItems));
        }
    }

    protected function validateUniqueItems($schema, $json, $context)
    {
        //The value of this keyword MUST be a boolean.
        // If this keyword has boolean value false, the instance validates successfully.  If it has boolean value true, the instance validates successfully if all of its elements are unique.

        if ($schema->uniqueItems === true) {
            //Not going to go too deep here.
            if (count(array_unique($json)) !== count($json)) {
                throw new Exception\ValidationException(sprintf('All of the items in %1$s must be unique.', $context));
            }
        }
    }

    protected function validateItems($schema, $json, $context)
    {
        //Validate each item in the array.
        foreach ($json as $index => $item) {
            (new Validator())
                ->validate($schema->items, $item, $context . '/' . $index);
        }
    }

    protected function isArray($arr)
    {
        return is_array($arr) && (empty($arr) || is_int(key($arr)));
    }
}
