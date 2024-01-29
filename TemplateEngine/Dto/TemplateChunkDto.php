<?php

declare(strict_types=1);

namespace FpDbTest\TemplateEngine\Dto;

final class TemplateChunkDto
{
    public function __construct(
        public readonly int $offsetStart,
        public readonly int $offsetEnd,
        public readonly string $content
    ) {
    }
}
