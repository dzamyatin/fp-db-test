<?php

declare(strict_types=1);

namespace FpDbTest\TemplateEngine\Dto;

class TemplatePartDto
{
    public function __construct(
        private readonly string $string,
        private readonly ?string $paramProcessorCode,
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
     * @return string|null
     */
    public function getParamProcessorCode(): ?string
    {
        return $this->paramProcessorCode;
    }

    /**
     * @return bool
     */
    public function isParam(): bool
    {
        return (bool) $this->paramProcessorCode;
    }
}
