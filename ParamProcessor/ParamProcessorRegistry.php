<?php

namespace FpDbTest\ParamProcessor;

class ParamProcessorRegistry implements ParamProcessorRegistryInterface
{
    /**
     * @param ParamProcessorInterface[] $processors
     */
    public function __construct(private array $processors)
    {
    }

    /**
     * @inheritdoc
     */
    public function add(ParamProcessorInterface $processor): self
    {
        $this->processors[] = $processor;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAll(): array
    {
        return $this->processors;
    }

    /**
     * @inheritdoc
     */
    public function getSupported($value): ?ParamProcessorInterface
    {
        foreach ($this->processors as $processor) {
            if ($processor->isValueSupportedRecognizing($value)) {
                return $processor;
            }
        }

        return null;
    }
}
