<?php

namespace FpDbTest\PostProcessor;

class PostProcessorRegistry implements PostProcessorRegistryInterface
{
    /**
     * @param PostProcessorInterface[] $postProcessors
     */
    public function __construct(private array $postProcessors)
    {
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        return $this->postProcessors;
    }
}
