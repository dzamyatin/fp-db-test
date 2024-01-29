<?php

declare(strict_types=1);

namespace FpDbTest\TemplateEngine\Dto;

class TemplatePartDto
{
    public function __construct(
        private readonly string $string,
        private readonly bool $param,
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
     * @return bool
     */
    public function isParam(): bool
    {
        return $this->param;
    }
}
