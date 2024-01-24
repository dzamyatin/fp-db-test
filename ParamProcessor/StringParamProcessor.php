<?php

namespace FpDbTest\ParamProcessor;

use FpDbTest\ParamProcessor\Exception\WrongParamTypeException;

class StringParamProcessor extends AbstractParamProcessor
{
    public function __construct(private EscapeStringConverterInterface $escapeStringConverter)
    {
    }

    public function getRegexpToCatchPattern(): string
    {
        return '\?s';
    }

    public function convertValue(mixed $value): string
    {
        $this->throwErrorOnInvalidValue($value);

        return is_null($value) ?
            'null' :
            sprintf("'%s'", $this->escapeStringConverter->escape($value));
    }

    public function isValueSupportedRecognizing(mixed $value): bool
    {
        return is_string($value) || is_null($value);
    }

    public function isValueSupportedForceCast(mixed $value): bool
    {
        return is_scalar($value) || is_null($value);
    }
}
