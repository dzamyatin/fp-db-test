<?php

namespace FpDbTest\ParamProcessor;

use FpDbTest\ParamProcessor\Exception\WrongParamTypeException;

class UnrecognizedParamProcessor implements ParamProcessorInterface
{
    public function __construct(private ParamProcessorRegistryInterface $paramProcessorRegistry)
    {
    }

    public function getRegexpToCatchPattern(): string
    {
        return '\?';
    }

    public function convertValue(mixed $value): string
    {
        return $this->paramProcessorRegistry->getSupported($value)?->convertValue($value) ??
            throw new WrongParamTypeException(
                sprintf(
                    'Value %s is not supported for any param processor',
                    var_export($value, true)
                )
            );
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
