<?php

declare(strict_types=1);

namespace FpDbTest\TemplateEngine;

use FpDbTest\DatabaseInterface;
use FpDbTest\TemplateEngine\Dto\TemplateDto;
use Exception;

interface TemplateEngineProcessorInterface
{
    /**
     * @param TemplateDto $templateDto
     * @param array $args
     * @param DatabaseInterface $database
     * @return string
     * @throws Exception
     */
    public function process(TemplateDto $templateDto, array $args, DatabaseInterface $database): string;
}
