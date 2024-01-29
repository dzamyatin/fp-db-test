<?php

declare(strict_types=1);

namespace FpDbTest\TemplateEngine\Dto;

class TemplatePositionDto
{
    public function __construct(
        public readonly int $offsetStart,
        public readonly int $offsetEnd,
    ) {
    }
}
