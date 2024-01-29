<?php

declare(strict_types=1);

namespace FpDbTest\TemplateEngine\Dto;

class TemplateDto
{
    /**
     * @param TemplateBlockDto[] $blocks
     */
    public function __construct(
        private readonly array $blocks
    ) {
    }

    /**
     * @return TemplateBlockDto[]
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }
}
