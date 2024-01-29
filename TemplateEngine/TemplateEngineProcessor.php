<?php

declare(strict_types=1);

namespace FpDbTest\TemplateEngine;

use FpDbTest\DatabaseInterface;
use FpDbTest\ParamProcessor\ParamProcessorRegistryInterface;
use FpDbTest\TemplateEngine\Dto\TemplateDto;
use Exception;

class TemplateEngineProcessor implements TemplateEngineProcessorInterface
{
    public function __construct(
        private ParamProcessorRegistryInterface $paramProcessorRegistry
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(TemplateDto $templateDto, array $args, DatabaseInterface $database): string
    {
        $blockString = '';
        foreach ($templateDto->getBlocks() as $block) {
            $skipBlock = false;
            $partString = '';
            foreach ($block->getParts() as $part) {
                if (is_null($part->getParamProcessorCode())) {

                    $partString .= $part->getString();
                    continue;
                }

                $processor = $this->paramProcessorRegistry->getByCode($part->getParamProcessorCode());

                if (is_null($processor)) {
                    throw new Exception(
                        sprintf(
                            'There is no match of param processor for %s',
                            $part->getParamProcessorCode()
                        )
                    );
                }

                $arg = array_shift($args);
                $skipBlock = $arg === $database->skip();
                $partString .= $processor->convertValue($arg);
            }

            $blockString .= $skipBlock ? '' : trim($partString, '{}');
        }

        return $blockString;
    }
}
