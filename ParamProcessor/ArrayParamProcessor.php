<?php

namespace FpDbTest\ParamProcessor;

use FpDbTest\ParamProcessor\Exception\WrongParamTypeException;

class ArrayParamProcessor extends AbstractParamProcessor
{
    private const CODE = 'ARRAY';

    public function __construct(
        private ParamProcessorRegistryInterface $paramProcessorRegistry,
        private EscapeStringConverterInterface $escapeStringConverter,
    ) {
    }

    public static function getCode(): string
    {
        return self::CODE;
    }

    public function getRegexpToCatchPattern(): string
    {
        return '\?a';
    }

    public function convertValue(mixed $value): string
    {
        $convertedValues = $this->convertValues((array) $value);

        if (count(array_filter($convertedValues, 'is_int')) === count($convertedValues)) {
            return implode(', ', $convertedValues);
        }

        $result = '';
        foreach ($convertedValues as $key => $value) {
            $result .= $result ? ', ' : '';
            $result .= sprintf(
                '`%s` = ' . $value,
                $this->escapeStringConverter->escape($key)
            );
        }

        return $result;
    }

    public function isValueSupportedRecognizing(mixed $value): bool
    {
        return is_array($value);
    }

    public function isValueSupportedForceCast(mixed $value): bool
    {
        return is_array($value) || is_scalar($value) || is_null($value);
    }

    private function convertValues(array $arrayValues): array
    {
        $this->throwErrorOnInvalidValue($arrayValues);

        $result = [];
        foreach ($arrayValues as $key => $arrayValue) {
            if ($processor = $this->paramProcessorRegistry->getSupported($arrayValue)) {
                $result[$key] = $processor->convertValue($arrayValue);
                continue;
            }

            $this->throwWrongParamTypeException($arrayValue);
        }

        if (!$result) {
            throw new WrongParamTypeException(
                'An empty array found while trying to convert value by array param processor'
            );
        }

        return $result;
    }
}
