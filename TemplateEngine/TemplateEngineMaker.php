<?php

declare(strict_types=1);

namespace FpDbTest\TemplateEngine;

use FpDbTest\ParamProcessor\Exception\WrongParamTypeException;
use FpDbTest\ParamProcessor\ParamProcessorInterface;
use FpDbTest\ParamProcessor\ParamProcessorRegistryInterface;
use FpDbTest\PatternString\Dto\PatternPositionDto;
use FpDbTest\TemplateEngine\Dto\TemplateChunkDto;
use FpDbTest\TemplateEngine\Dto\TemplatePositionDto;
use FpDbTest\PatternString\PatternResolverInterface;
use FpDbTest\TemplateEngine\Dto\TemplateBlockDto;
use FpDbTest\TemplateEngine\Dto\TemplateDto;
use FpDbTest\TemplateEngine\Dto\TemplatePartDto;

class TemplateEngineMaker implements TemplateEngineMakerInterface
{
    public function __construct(
        private PatternResolverInterface $patternResolver,
        private ParamProcessorRegistryInterface $paramProcessorRegistry,
    ) {
    }

    public function make(string $query): TemplateDto
    {
        $paramProcessorsMap = self::mapPatternIdToParamProcessor(
            $this->paramProcessorRegistry->getAll()
        );

        if (!$paramProcessorsMap) {
            return self::getEmpty($query);
        }

        $sequences = self::createSequencesFromPattern(
            self::createParamProcessorsRegexpToSearchPatterns($paramProcessorsMap),
            $query
        );

        $blocks = $this->getBlocks($query, $sequences);

//        var_dump($blocks);die();


//
//        list($argSequences) = $matches;
//        $offsetToArgMap = self::matchArgsToOffset(
//            $argSequences,
//            $args
//        );
//
//        $this->resolveTemplate(
//            $query,
//            $offsetToArgMap,
//            $paramProcessorsMap,
//            $matches
//        );


        return new TemplateDto([]);
    }

    /**
     * @param string $query
     * @param TemplatePositionDto[] $sequences
     * @return TemplateBlockDto[]
     */
    private function getBlocks(string $query, array $argumentSequences): array
    {
        $matches = self::createSequencesFromPattern('/\{((.|\n)*?)\}/', $query);

        $chunks = self::splitToChunks($query, $matches);

        var_dump($chunks);die();

        return [
            new TemplateBlockDto([])
        ];
    }

    /**
     * @param string $string
     * @param TemplatePositionDto[] $positions
     * @return TemplateChunkDto[]
     */
    private static function splitToChunks(string $string, array $positions): array
    {
        $positions = array_values($positions);

        usort(
            $positions,
            fn (TemplatePositionDto $positionDtoA, TemplatePositionDto $positionDtoB) =>
                $positionDtoA->offsetStart <=> $positionDtoB->offsetStart
        );

        $pieces = [];
        for ($i = 0; $i < count($positions); $i++) {
            $previousOffset = $positions[$i - 1]->offsetEnd ?? 0;
            if ($previousOffset !== $positions[$i]->offsetStart) {
                $pieces[] = self::createChunk(
                    $string,
                    $previousOffset,
                    $positions[$i]->offsetStart
                );
            }

            $pieces[] = self::createChunk(
                $string,
                $positions[$i]->offsetStart,
                $positions[$i]->offsetEnd
            );

            if ($i === (count($positions) - 1) && $positions[$i]->offsetEnd !== mb_strlen($string)) {
                $pieces[] = self::createChunk(
                    $string,
                    $positions[$i]->offsetEnd,
                    mb_strlen($string)
                );
            }
        }

        return $pieces;
    }

    private static function createChunk(
        string $string,
        int $offsetStart,
        int $offsetEnd
    ): TemplateChunkDto {
        return new TemplateChunkDto(
            $offsetStart,
            $offsetEnd,
            substr(
                $string,
                $offsetStart,
                $offsetEnd - $offsetStart
            )
        );
    }

        /**
     * @return TemplatePositionDto[]
     */
    private static function createSequencesFromPattern(string $regexp, string $query): array
    {
        preg_match_all(
            $regexp,
            $query,
            $matches,
            PREG_OFFSET_CAPTURE |
            PREG_PATTERN_ORDER |
            PREG_UNMATCHED_AS_NULL
        );

        $matches = array_filter($matches);

        return array_map(
            function (array $match) {
                list($matchedPattern, $offset) = $match;
                return new TemplatePositionDto(
                    $offset,
                    $offset + mb_strlen($matchedPattern),
                );
            },
            $matches[0] ?? []
        );
    }

    private static function getEmpty(string $query): TemplateDto
    {
        return new TemplateDto(
            [
                new TemplateBlockDto(
                    [
                        new TemplatePartDto($query, 0)
                    ]
                )
            ]
        );
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
}
