<?php

declare(strict_types=1);

namespace FpDbTest\TemplateEngine;

use FpDbTest\ParamProcessor\ParamProcessorInterface;
use FpDbTest\ParamProcessor\ParamProcessorRegistryInterface;
use FpDbTest\TemplateEngine\Dto\TemplateChunkDto;
use FpDbTest\TemplateEngine\Dto\TemplatePositionDto;
use FpDbTest\TemplateEngine\Dto\TemplateBlockDto;
use FpDbTest\TemplateEngine\Dto\TemplateDto;
use FpDbTest\TemplateEngine\Dto\TemplatePartDto;
use FpDbTest\TemplateEngine\Dto\TemplatePositionParamProcessorAwareDto;

class TemplateEngineMaker implements TemplateEngineMakerInterface
{
    public function __construct(
        private ParamProcessorRegistryInterface $paramProcessorRegistry,
    ) {
    }

    public function make(string $query): TemplateDto
    {
        return new TemplateDto($this->createBlocks($query));
    }

    /**
     * @param string $query
     * @return TemplateBlockDto[]
     */
    private function createBlocks(string $query): array {
        $matches = self::createSequencesFromPattern('/\{((.|\n)*?)\}/', $query);
        $chunks = self::splitToChunks($query, $matches);

        $result = [];
        foreach ($chunks as $chunk) {
            $result[] = new TemplateBlockDto(
                $this->createParts($chunk->content)
            );
        }

        return $result;
    }

    /**
     * @param string $string
     * @return TemplatePartDto[]
     */
    private function createParts(string $string): array
    {
        $sequences = $this->createParamSequences($string);

        $chunks = self::splitToChunks($string, $sequences);

        $result = [];
        foreach ($chunks as $chunk) {
            $paramProcessorCode = null;
            for ($i = 0; $i < count($sequences); $i++) {
                if ($sequences[$i]->offsetStart === $chunk->offsetStart) {
                    $paramProcessorCode = $sequences[$i]->processorCode;
                    break;
                }
            }

            $result[] = new TemplatePartDto(
                $chunk->content,
                $paramProcessorCode
            );
        }

        return $result;
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

        if (!$pieces) {
            $pieces[] = self::createChunk(
                $string,
                0,
                mb_strlen($string)
            );
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
     * @param string $string
     * @return TemplatePositionParamProcessorAwareDto[]
     */
    private function createParamSequences(string $string): array
    {
        $paramProcessorsMap = self::mapPatternIdToParamProcessor(
            $this->paramProcessorRegistry->getAll()
        );

        if (!$paramProcessorsMap) {
            return [];
        }

        preg_match_all(
            self::createParamProcessorsRegexpToSearchPatterns($paramProcessorsMap),
            $string,
            $matches,
            PREG_OFFSET_CAPTURE |
            PREG_PATTERN_ORDER |
            PREG_UNMATCHED_AS_NULL
        );

        $matches = array_filter($matches);

        $offsetToProcessorMap = [];
        foreach ($paramProcessorsMap as $processorId => $processor) {
            if (!$processorMatches = array_filter($matches[$processorId] ?? [])) {
                continue;
            }

            foreach ($processorMatches as list($pattern, $offset)) {
                if (is_null($pattern)) {
                    continue;
                }

                $offsetToProcessorMap[$offset] = $processor::getCode();
            }
        }

        return array_map(
            static function (array $match) use ($offsetToProcessorMap) {
                list($matchedPattern, $offset) = $match;
                return new TemplatePositionParamProcessorAwareDto(
                    $offset,
                    $offset + mb_strlen($matchedPattern),
                    $offsetToProcessorMap[$offset]
                );
            },
            $matches[0] ?? []
        );
    }

    /**
     * @param string $regexp
     * @param string $query
     * @return array
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
}
