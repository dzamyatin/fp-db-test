<?php

declare(strict_types=1);

namespace FpDbTest\TemplateEngine\Dto;

class TemplatePartDto
{
    public function __construct(
        private readonly string $string,
        private readonly int $argsQuantity,
    ) {
    }

    /**
     * @return string
     */
    public function getString(): string
    {
        return $this->string;
    }

    /**
     * @return int
     */
    public function getArgsQuantity(): int
    {
        return $this->argsQuantity;
    }
}
