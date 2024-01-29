<?php

namespace FpDbTest\ParamProcessor;

class FloatParamProcessor extends AbstractParamProcessor
{
    private const CODE = 'FLOAT';

    public function getRegexpToCatchPattern(): string
    {
        return '\?f';
    }

    public static function getCode(): string
    {
        return self::CODE;
    }

    public function convertValue(mixed $value): string
    {
        $this->throwErrorOnInvalidValue($value);

        return is_null($value) ? 'NULL' : (float) $value;
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
