<?php

declare(strict_types=1);

use FpDbTest\Database;
use FpDbTest\DatabaseInterface;
use FpDbTest\DatabaseTest;
use FpDbTest\ParamProcessor\ArrayParamProcessor;
use FpDbTest\ParamProcessor\EscapeStringConverterInterface;
use FpDbTest\ParamProcessor\FloatParamProcessor;
use FpDbTest\ParamProcessor\IdentifierParamProcessor;
use FpDbTest\ParamProcessor\IntegerParamProcessor;
use FpDbTest\ParamProcessor\MysqliEscapeStringConverter;
use FpDbTest\ParamProcessor\ParamProcessorRegistry;
use FpDbTest\ParamProcessor\ParamProcessorRegistryInterface;
use FpDbTest\ParamProcessor\StringParamProcessor;
use FpDbTest\ParamProcessor\UnrecognizedParamProcessor;
use FpDbTest\PostProcessor\ConditionPostProcessor;
use FpDbTest\PostProcessor\PostProcessorRegistry;
use FpDbTest\PostProcessor\PostProcessorRegistryInterface;
use FpDbTest\ServiceLocator;

return [
    mysqli::class => static function (): mysqli {
        $mysqli = @new mysqli('mysql', 'root', 'password', 'database', 3306);
        if ($mysqli->connect_errno) {
            throw new Exception($mysqli->connect_error);
        }

        return $mysqli;
    },
    DatabaseInterface::class => static function (ServiceLocator $serviceLocator): DatabaseInterface {
        return new Database(
            $serviceLocator->get(mysqli::class),
            $serviceLocator->get(ParamProcessorRegistryInterface::class),
            $serviceLocator->get(PostProcessorRegistryInterface::class),
        );
    },
    DatabaseTest::class => static function (ServiceLocator $serviceLocator): DatabaseTest {
        return new DatabaseTest($serviceLocator->get(DatabaseInterface::class));
    },
    ParamProcessorRegistryInterface::class => static function (ServiceLocator $serviceLocator): ParamProcessorRegistryInterface {
        return new ParamProcessorRegistry(
            [
                $serviceLocator->get(FloatParamProcessor::class),
                $serviceLocator->get(IntegerParamProcessor::class),
                $serviceLocator->get(StringParamProcessor::class),
                $serviceLocator->get(IdentifierParamProcessor::class),
            ]
        );
    },
    FloatParamProcessor::class => static function (): FloatParamProcessor {
        return new FloatParamProcessor();
    },
    IntegerParamProcessor::class => static function (): IntegerParamProcessor {
        return new IntegerParamProcessor();
    },
    EscapeStringConverterInterface::class => static function (ServiceLocator $serviceLocator): EscapeStringConverterInterface {
        return new MysqliEscapeStringConverter($serviceLocator->get(mysqli::class));
    },
    StringParamProcessor::class => static function (ServiceLocator $serviceLocator): StringParamProcessor {
        return new StringParamProcessor(
            $serviceLocator->get(EscapeStringConverterInterface::class)
        );
    },
    IdentifierParamProcessor::class => static function (ServiceLocator $serviceLocator): IdentifierParamProcessor {
        return new IdentifierParamProcessor(
            $serviceLocator->get(EscapeStringConverterInterface::class)
        );
    },
    ArrayParamProcessor::class => static function (ServiceLocator $serviceLocator): ArrayParamProcessor {
        return new ArrayParamProcessor(
            $serviceLocator->get(ParamProcessorRegistryInterface::class),
            $serviceLocator->get(EscapeStringConverterInterface::class)
        );
    },
    UnrecognizedParamProcessor::class => static function (ServiceLocator $serviceLocator): UnrecognizedParamProcessor {
        return new UnrecognizedParamProcessor(
            $serviceLocator->get(ParamProcessorRegistryInterface::class)
        );
    },
    ConditionPostProcessor::class => static function (): ConditionPostProcessor {
        return new ConditionPostProcessor();
    },
    PostProcessorRegistryInterface::class => static function (ServiceLocator $serviceLocator): PostProcessorRegistryInterface {
        return new PostProcessorRegistry(
            [
                $serviceLocator->get(ConditionPostProcessor::class)
            ],
        );
    },
];
