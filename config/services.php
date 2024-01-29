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
use FpDbTest\PatternString\PatternResolver;
use FpDbTest\PatternString\PatternResolverInterface;
use FpDbTest\ServiceLocator;
use FpDbTest\TemplateEngine\TemplateEngineMaker;
use FpDbTest\TemplateEngine\TemplateEngineMakerInterface;
use FpDbTest\TemplateEngine\TemplateEngineProcessor;
use FpDbTest\TemplateEngine\TemplateEngineProcessorInterface;

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
            $serviceLocator->get(TemplateEngineMakerInterface::class),
            $serviceLocator->get(TemplateEngineProcessorInterface::class),
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
    PatternResolverInterface::class => static function (): PatternResolverInterface {
        return new PatternResolver();
    },
    TemplateEngineMakerInterface::class => static function (ServiceLocator $serviceLocator): TemplateEngineMakerInterface {
        return new TemplateEngineMaker(
            $serviceLocator->get(ParamProcessorRegistryInterface::class),
        );
    },
    TemplateEngineProcessorInterface::class => static function (ServiceLocator $serviceLocator): TemplateEngineProcessorInterface {
        return new TemplateEngineProcessor(
            $serviceLocator->get(ParamProcessorRegistryInterface::class),
        );
    },
];
