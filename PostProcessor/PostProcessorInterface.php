<?php

namespace FpDbTest\PostProcessor;

use FpDbTest\DatabaseInterface;

interface PostProcessorInterface
{
    public function process(string $query, DatabaseInterface $database): string;
}
