<?php

declare(strict_types=1);

namespace FpDbTest\PatternString\Dto;

use Closure;

final class PatternPositionDto
{
    public function __construct(
        public readonly int $offsetStart,
        public readonly int $offsetEnd,
        public readonly Closure $resolver
    ) {
    }
}
