<?php

namespace FpDbTest\PostProcessor;

interface PostProcessorRegistryInterface
{
    /**
     * @return PostProcessorInterface[]
     */
    public function getAll(): array;
}
