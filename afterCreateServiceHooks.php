<?php

declare(strict_types=1);

use FpDbTest\ParamProcessor\ArrayParamProcessor;
use FpDbTest\ParamProcessor\ParamProcessorRegistryInterface;
use FpDbTest\ParamProcessor\UnrecognizedParamProcessor;
use FpDbTest\ServiceLocator;

return [
    ParamProcessorRegistryInterface::class => static function (
        ServiceLocator $serviceLocator,
    ) {
        $serviceLocator->get(ParamProcessorRegistryInterface::class)
            ->add($serviceLocator->get(ArrayParamProcessor::class))
            ->add($serviceLocator->get(UnrecognizedParamProcessor::class));
    }
];
