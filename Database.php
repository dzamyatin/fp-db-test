<?php

namespace FpDbTest;

use FpDbTest\ParamProcessor\Exception\WrongParamTypeException;
use FpDbTest\ParamProcessor\ParamProcessorInterface;
use FpDbTest\ParamProcessor\ParamProcessorRegistryInterface;
use FpDbTest\PatternString\Dto\PatternPositionDto;
use FpDbTest\PatternString\PatternResolverInterface;
use FpDbTest\TemplateEngine\TemplateEngineMakerInterface;
use FpDbTest\TemplateEngine\TemplateEngineProcessorInterface;
use mysqli;

class Database implements DatabaseInterface
{
    const SKIP_SYMBOL = '[:☃☽♻:]';

    public function __construct(
        private mysqli $mysqli,
        private ParamProcessorRegistryInterface $paramProcessorRegistry,
        private PatternResolverInterface $patternResolver,
        private TemplateEngineMakerInterface $templateMaker,
        private TemplateEngineProcessorInterface $templateEngineProcessor
    ) {}

    /**
     * @param string $query
     * @param array<string|int|float|array> $args
     * @return string
     * @throws WrongParamTypeException
     */
    public function buildQuery(string $query, array $args = []): string
    {
        $args = array_values($args);

        $query = $this->applyParamProcessors($query, $args);

        return $query;
    }

    public function skip()
    {
        return self::SKIP_SYMBOL;
    }

    private function applyParamProcessors(string $query, array $args): string
    {
        $template = $this->templateMaker->make($query);

        $result = $this->templateEngineProcessor->process($template, $args);

        var_dump($result);
        die();

//        $paramProcessorsMap = self::mapPatternIdToParamProcessor(
//            $this->paramProcessorRegistry->getAll()
//        );
//
//        if (!$paramProcessorsMap) {
//            return $query;
//        }
//
//        $matches = [];
//        preg_match_all(
//            self::createParamProcessorsRegexpToSearchPatterns($paramProcessorsMap),
//            $query,
//            $matches,
//            PREG_OFFSET_CAPTURE |
//            PREG_PATTERN_ORDER |
//            PREG_UNMATCHED_AS_NULL
//        );
//
//        $matches = array_filter($matches);
//
//        if (!$matches) {
//            return $query;
//        }
//
//        list($argSequences) = $matches;
//        $offsetToArgMap = self::matchArgsToOffset(
//            $argSequences,
//            $args
//        );
//
//        return $this->resolveTemplate(
//            $query,
//            $offsetToArgMap,
//            $paramProcessorsMap,
//            $matches
//        );
    }

    /**
     * @param array<array> $argSequences
     * @param array $args
     * @return array<int, mixed>
     * @throws WrongParamTypeException
     */
    private static function matchArgsToOffset(array $argSequences, array $args): array
    {
        $offsetToArgMap = [];
        foreach ($argSequences as $argKey => $argSequence) {
            list($argPattern, $argOffset) = $argSequence;

            if (!array_key_exists($argKey, $args)) {
                throw new WrongParamTypeException(
                    sprintf(
                        'There is no argument num %d to fit pattern on offset %d for pattern %s',
                        $argKey,
                        $argOffset,
                        $argPattern
                    )
                );
            }

            $offsetToArgMap[$argOffset] = $args[$argKey];
        }

        return $offsetToArgMap;
    }

    /**
     * @param string $query
     * @param array $offsetToArgMap
     * @param ParamProcessorInterface[] $paramProcessorsMap
     * @param array $matches
     * @return string
     * @throws WrongParamTypeException
     */
    private function resolveTemplate(
        string $query,
        array $offsetToArgMap,
        array $paramProcessorsMap,
        array $matches,
    ): string {
        $patterns = [];
        foreach ($paramProcessorsMap as $processorKey => $processor) {
            $processorMatches = $matches[$processorKey] ?? [];

            foreach ($processorMatches as list($matchedPattern, $offset)) {
                if (is_null($matchedPattern)) {
                    continue;
                }

                $patterns[] = new PatternPositionDto(
                    $offset,
                    $offset + mb_strlen($matchedPattern),
                    static fn () => $processor->convertValue($offsetToArgMap[$offset])
                );
            }
        }

        return $this->patternResolver->resolve($query, $patterns);
    }

    /**
     * @param array<string, ParamProcessorInterface> $paramProcessors
     * @return string
     */
    private static function createParamProcessorsRegexpToSearchPatterns(array $paramProcessors): string
    {
        $regexp = '';

        foreach ($paramProcessors as $processorKey => $processor) {
            $regexp .= $regexp ? '|' : '';
            $regexp .= '(?<' . $processorKey . '>';
            $regexp .= $processor->getRegexpToCatchPattern();
            $regexp .= ')';
        }

        return '/' . $regexp . '/';
    }

    /**
     * @param ParamProcessorInterface[] $paramProcessors
     * @return array<string, ParamProcessorInterface>
     */
    private static function mapPatternIdToParamProcessor(array $paramProcessors): array
    {
        $result = [];
        foreach ($paramProcessors as $processor) {
            $result[self::getPatternNameFromProcessor($processor)] = $processor;
        }

        return $result;
    }

    private static function getPatternNameFromProcessor(ParamProcessorInterface $processor): string
    {
        return 'pattern' . spl_object_id($processor);
    }
}
