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
    public function getSupported(mixed $value): ?ParamProcessorInterface;

    public function getByCode(string $code): ?ParamProcessorInterface;

    public function add(ParamProcessorInterface $processor): self;
}
