<?php

namespace FpDbTest\ParamProcessor;

use FpDbTest\ParamProcessor\Exception\WrongParamTypeException;

class ArrayParamProcessor extends AbstractParamProcessor
{
    public function __construct(private ParamProcessorRegistryInterface $paramProcessorRegistry)
    {
    }

    public function getRegexpToCatchPattern(): string
    {
        return '\?a';
    }

    public function convertValue(mixed $value): string
    {
        $arrayValues = (array) $value;

        $this->throwErrorOnInvalidValue($arrayValues);

        $result = [];
        foreach ($arrayValues as $arrayValue) {
            if ($processor = $this->paramProcessorRegistry->getSupported($arrayValue)) {
                $result[] = $processor->convertValue($arrayValue);
                continue;
            }

            $this->throwWrongParamTypeException($value);
        }

        if (!$result) {
            throw new WrongParamTypeException(
                'An empty array found while trying to convert value by array param processor'
            );
        }

        return implode(', ', $result);
    }

    public function isValueSupportedRecognizing(mixed $value): bool
    {
        return is_array($value);
    }

    public function isValueSupportedForceCast(mixed $value): bool
    {
        return is_array($value) || is_scalar($value) || is_null($value);
    }
}
