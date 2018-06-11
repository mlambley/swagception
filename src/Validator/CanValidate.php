<?php
namespace Swagception\Validator;

interface CanValidate
{
    public function validate($schema, $json, $context);
}
