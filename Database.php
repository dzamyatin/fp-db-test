<?php

namespace FpDbTest;

use FpDbTest\ParamProcessor\Exception\WrongParamTypeException;
use FpDbTest\TemplateEngine\TemplateEngineMakerInterface;
use FpDbTest\TemplateEngine\TemplateEngineProcessorInterface;
use Exception;

class Database implements DatabaseInterface
{
    const SKIP_SYMBOL = '[:☃☽♻:]';

    public function __construct(
        private TemplateEngineMakerInterface $templateMaker,
        private TemplateEngineProcessorInterface $templateEngineProcessor
    ) {}

    /**
     * @param string $query
     * @param array<string|int|float|array> $args
     * @return string
     * @throws WrongParamTypeException|Exception
     */
    public function buildQuery(string $query, array $args = []): string
    {
        $args = array_values($args);

        $template = $this->templateMaker->make($query);

        return $this->templateEngineProcessor->process(
            $template,
            $args,
            $this
        );
    }

    public function skip()
    {
        return self::SKIP_SYMBOL;
    }
}
