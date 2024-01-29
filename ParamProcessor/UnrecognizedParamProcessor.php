<?php

namespace FpDbTest\ParamProcessor;

class UnrecognizedParamProcessor extends AbstractParamProcessor
{
    private const CODE = 'UNRECOGNIZED';

    public function __construct(private ParamProcessorRegistryInterface $paramProcessorRegistry)
    {
    }

    public static function getCode(): string
    {
        return self::CODE;
    }

    public function getRegexpToCatchPattern(): string
    {
        return '\?';
    }

    public function convertValue(mixed $value): string
    {
        return $this->paramProcessorRegistry->getSupported($value)?->convertValue($value) ??
            $this->throwWrongParamTypeException($value);
    }

    public function isValueSupportedRecognizing(mixed $value): bool
    {
        return false;
    }

    public function isValueSupportedForceCast(mixed $value): bool
    {
        return true;
    }
}
