<?php

declare(strict_types=1);

namespace FpDbTest\TemplateEngine\Dto;

final class TemplatePositionParamProcessorAwareDto extends TemplatePositionDto
{
    public function __construct(
        int $offsetStart,
        int $offsetEnd,
        public readonly string $processorCode
    ) {
        parent::__construct($offsetStart, $offsetEnd);
    }
}
