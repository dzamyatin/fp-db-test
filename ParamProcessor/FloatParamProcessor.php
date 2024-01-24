<?php

namespace FpDbTest\ParamProcessor;

class FloatParamProcessor extends AbstractParamProcessor
{
    public function getRegexpToCatchPattern(): string
    {
        return '\?f';
    }

    public function convertValue(mixed $value): string
    {
        $this->throwErrorOnInvalidValue($value);

        return is_null($value) ? 'null' : (float) $value;
    }

    public function isValueSupportedRecognizing(mixed $value): bool
    {
        return is_float($value) || is_null($value);
    }

    public function isValueSupportedForceCast(mixed $value): bool
    {
        return is_scalar($value) || is_null($value);
    }
}
