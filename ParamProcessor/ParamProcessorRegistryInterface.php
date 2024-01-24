<?php

namespace FpDbTest\ParamProcessor;

interface ParamProcessorRegistryInterface
{
    /**
     * @return ParamProcessorInterface[]
     */
    public function getAll(): array;

    /**
     * @param mixed $value
     * @return ParamProcessorInterface|null
     */
    public function getSupported($value): ?ParamProcessorInterface;

    public function add(ParamProcessorInterface $processor): void;
}
