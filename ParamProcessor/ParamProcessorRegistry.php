<?php

namespace FpDbTest\ParamProcessor;

class ParamProcessorRegistry implements ParamProcessorRegistryInterface
{
    private array $processorsByName = [];

    /**
     * @param ParamProcessorInterface[] $processors
     */
    public function __construct(array $processors)
    {
        foreach ($processors as $processor) {
            $this->add($processor);
        }
    }

    /**
     * @inheritdoc
     */
    public function add(ParamProcessorInterface $processor): self
    {
        $this->processorsByName[$processor::getCode()] = $processor;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAll(): array
    {
        return $this->processorsByName;
    }

    /**
     * @inheritdoc
     */
    public function getSupported(mixed $value): ?ParamProcessorInterface
    {
        foreach ($this->processorsByName as $processor) {
            if ($processor->isValueSupportedRecognizing($value)) {
                return $processor;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getByCode(string $code): ?ParamProcessorInterface
    {
        foreach ($this->processorsByName as $processor) {
            if ($processor::getCode() === $code) {
                return $processor;
            }
        }

        return null;
    }
}
