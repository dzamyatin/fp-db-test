<?php

namespace FpDbTest\ParamProcessor;

use FpDbTest\ParamProcessor\Exception\WrongParamTypeException;

class IdentifierParamProcessor extends AbstractParamProcessor
{
    public function __construct(private EscapeStringConverterInterface $escapeStringConverter)
    {
    }

    public function getRegexpToCatchPattern(): string
    {
        return '\?#';
    }

    public function convertValue(mixed $value): string
    {
        $this->throwErrorOnInvalidValue($value);

        $values = (array) $value;

        $result = [];
        foreach ($values as $value) {
            if (is_scalar($value) || is_null($value)) {
                $result[] = is_null($value) ?
                    'null' :
                    sprintf("`%s`", $this->escapeStringConverter->escape($value));
            }
        }

        return implode(', ', $result);
    }

    public function isValueSupportedRecognizing(mixed $value): bool
    {
        return false;
    }

    public function isValueSupportedForceCast(mixed $value): bool
    {
        return is_scalar($value) || is_null($value) || is_array($value);
    }
}