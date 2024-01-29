<?php

declare(strict_types=1);

namespace FpDbTest\TemplateEngine;

use FpDbTest\TemplateEngine\Dto\TemplateDto;
use Exception;

interface TemplateEngineProcessorInterface
{
    /**
     * @param TemplateDto $templateDto
     * @param array $args
     * @return string
     * @throws Exception
     */
    public function process(TemplateDto $templateDto, array $args): string;
}
