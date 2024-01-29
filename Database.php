<?php

namespace FpDbTest;

use Exception;
use FpDbTest\ParamProcessor\Exception\WrongParamTypeException;
use FpDbTest\ParamProcessor\ParamProcessorInterface;
use FpDbTest\ParamProcessor\ParamProcessorRegistryInterface;
use mysqli;

class Database implements DatabaseInterface
{
    public function __construct(
        private mysqli $mysqli,
        private ParamProcessorRegistryInterface $paramProcessorRegistry
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
        throw new Exception();
    }

    private function applyParamProcessors(string $query, array $args): string
    {
        $paramProcessorsMap = self::mapPatternIdToParamProcessor(
            $this->paramProcessorRegistry->getAll()
        );

        if (!$paramProcessorsMap) {
            return $query;
        }

        $matches = [];
        preg_match_all(
            self::createParamProcessorsRegexpToSearchPatterns($paramProcessorsMap),
            $query,
            $matches,
            PREG_OFFSET_CAPTURE |
            PREG_PATTERN_ORDER |
            PREG_UNMATCHED_AS_NULL
        );

        $matches = array_filter($matches);

        if (!$matches) {
            return $query;
        }

        list($argSequences) = $matches;
        $offsetToArgMap = self::matchArgsToOffset(
            $argSequences,
            $args
        );

        $result = self::splitQueryIntoPieces(
            $query,
            array_keys($offsetToArgMap),
            $offsetToArgMap,
            $paramProcessorsMap,
            $matches
        );

        return implode('', $result);
    }

    /**
     * @param array<array> $argSequences
     * @param array $args
     * @return array<int, mixed>
     * @throws WrongParamTypeException
     */
    public static function matchArgsToOffset(array $argSequences, array $args): array
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
     * @param int[] $chunkPositions
     * @param array $offsetToArgMap
     * @param ParamProcessorInterface[] $paramProcessorsMap
     * @param array $matches
     * @return array<int, string>
     */
    private static function splitQueryIntoPieces(
        string $query,
        array $chunkPositions,
        array $offsetToArgMap,
        array $paramProcessorsMap,
        array $matches,
    ): array {
        $chunkPositions = array_values($chunkPositions);
        sort($chunkPositions);

        $lostPart = '';
        $pieces = [];
        for ($i = 0; $i < count($chunkPositions); $i++) {
            if ($i === 0 && $chunkPositions[$i] !== 0) {
                $lostPart = substr(
                    $query,
                    0,
                    $chunkPositions[$i]
                );
            }

            $pieces[$chunkPositions[$i]] = substr(
                $query,
                $chunkPositions[$i],
                ($chunkPositions[$i + 1] ?? mb_strlen($query)) - $chunkPositions[$i]
            );
        }

        $result = [];
        foreach ($paramProcessorsMap as $processorKey => $processor) {
            $processorMatches = $matches[$processorKey] ?? [];

            foreach ($processorMatches as list($matchedPattern, $offset)) {
                if (is_null($matchedPattern)) {
                    continue;
                }

                $result[$offset] = $processor->convertValue($offsetToArgMap[$offset]) .
                    substr($pieces[$offset], mb_strlen($matchedPattern));
            }
        }

        ksort($result);

        array_unshift($result, $lostPart);

        return $result;
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
