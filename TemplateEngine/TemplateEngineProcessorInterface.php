<?php

declare(strict_types=1);

namespace FpDbTest\TemplateEngine;

use FpDbTest\TemplateEngine\Dto\TemplateDto;

interface TemplateEngineProcessorInterface
{
    public function process(TemplateDto $templateDto): string;
}
