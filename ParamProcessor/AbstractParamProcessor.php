<?php

declare(strict_types=1);

namespace FpDbTest\ParamProcessor;

use FpDbTest\ParamProcessor\Exception\WrongParamTypeException;

abstract class AbstractParamProcessor implements ParamProcessorInterface
{
    /**
     * @param mixed $value
     * @return void
     * @throws WrongParamTypeException
     */
    protected function throwErrorOnInvalidValue(mixed $value): void
    {
        if (!$this->isValueSupportedForceCast($value)) {
            $this->throwWrongParamTypeException($value);
        }
    }

    /**
     * @param mixed $value
     * @return void
     * @throws WrongParamTypeException
     */
    protected function throwWrongParamTypeException(mixed $value): void
    {
        throw new WrongParamTypeException(
            sprintf(
                'Value %s is not supported for %s',
                var_export($value, true),
                static::class
            )
        );
    }
}
