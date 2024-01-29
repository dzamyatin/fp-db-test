<?php

declare(strict_types=1);

namespace FpDbTest\TemplateEngine;

use FpDbTest\TemplateEngine\Dto\TemplateDto;

interface TemplateEngineMakerInterface
{
    public function make(string $query): TemplateDto;
}
