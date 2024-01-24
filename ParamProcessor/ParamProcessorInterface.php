<?php

namespace FpDbTest\ParamProcessor;

use FpDbTest\ParamProcessor\Exception\WrongParamTypeException;

interface ParamProcessorInterface
{
    public function getRegexpToCatchPattern(): string;

    public function isValueSupportedRecognizing(mixed $value): bool;

    public function isValueSupportedForceCast(mixed $value): bool;

    /**
     * @param mixed $value
     * @return string
     * @throws WrongParamTypeException
     */
    public function convertValue(mixed $value): string;
}
