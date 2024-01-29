<?php

namespace FpDbTest\ParamProcessor;

class StringParamProcessor extends AbstractParamProcessor
{
    private const CODE = 'STRING';

    public function __construct(private EscapeStringConverterInterface $escapeStringConverter)
    {
    }

    public static function getCode(): string
    {
        return self::CODE;
    }

    public function getRegexpToCatchPattern(): string
    {
        return '\?s';
    }

    public function convertValue(mixed $value): string
    {
        $this->throwErrorOnInvalidValue($value);

        return is_null($value) ?
            'NULL' :
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
