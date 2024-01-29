<?php

namespace FpDbTest\PatternString;

use FpDbTest\PatternString\Dto\PatternPositionDto;

interface PatternResolverInterface
{
    /**
     * @param string $string
     * @param PatternPositionDto[] $patterns
     * @return string
     */
    public function resolve(string $string, array $patterns): string;
}
