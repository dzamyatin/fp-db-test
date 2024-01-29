<?php

declare(strict_types=1);

namespace FpDbTest\TemplateEngine\Dto;

class TemplateBlockDto
{
    /**
     * @param TemplatePartDto[] $parts
     */
    public function __construct(
        private readonly array $parts
    ) {
    }

    /**
     * @return TemplatePartDto[]
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    public function getArgumentCount(): int
    {
        $count = 0;
        foreach ($this->parts as $part) {
            if ($part->isParam()) {
                $count++;
            }
        }

        return $count;
    }
}
