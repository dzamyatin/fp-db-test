<?php

declare(strict_types=1);

namespace FpDbTest\TemplateEngine;

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
    public function process(TemplateDto $templateDto, array $args): string
    {
        $blockString = '';
        foreach ($templateDto->getBlocks() as $block) {
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

                $partString .= $processor->convertValue(array_shift($args));
            }

            $blockString .= $partString;
        }

        return $blockString;
    }
}
