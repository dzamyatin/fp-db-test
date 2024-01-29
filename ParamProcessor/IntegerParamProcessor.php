<?php

namespace FpDbTest\ParamProcessor;

class IntegerParamProcessor extends AbstractParamProcessor
{
    private const CODE = 'INTEGER';

    public function getRegexpToCatchPattern(): string
    {
        return '\?d';
    }

    public static function getCode(): string
    {
        return self::CODE;
    }

    public function convertValue(mixed $value): string
    {
        $this->throwErrorOnInvalidValue($value);

        return is_null($value) ? 'null' : (int) $value;
    }

    public function isValueSupportedRecognizing(mixed $value): bool
    {
        return is_int($value) || is_bool($value) || is_null($value);
    }

    public function isValueSupportedForceCast(mixed $value): bool
    {
        return is_scalar($value) || is_null($value);
    }
}
