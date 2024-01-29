<?php

declare(strict_types=1);

namespace FpDbTest\PatternString;

use FpDbTest\PatternString\Dto\PatternPositionDto;
use Exception;

class PatternResolver implements PatternResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(string $string, array $patterns): string
    {
        $patterns = array_values($patterns);

        usort(
            $patterns,
            fn (PatternPositionDto $positionDtoA, PatternPositionDto $positionDtoB) =>
            $positionDtoA->offsetStart <=> $positionDtoB->offsetStart
        );

        $pieces = [];
        for ($i = 0; $i < count($patterns); $i++) {
            if ($i === 0 && $patterns[$i]->offsetStart !== 0) {
                $pieces[0] = substr(
                    $string,
                    0,
                    $patterns[$i]->offsetStart
                );
            }

            $resolvedPatterValue = call_user_func($patterns[$i]->resolver);

            if (!is_scalar($resolvedPatterValue)) {
                throw new Exception(
                    sprintf(
                        'Resolver return wrong type %s for offset %d',
                        var_export($resolvedPatterValue, true),
                        $patterns[$i]->offsetStart,
                    )
                );
            }

            $pieces[$patterns[$i]->offsetStart] =
                $resolvedPatterValue .
                substr(
                    $string,
                    $patterns[$i]->offsetEnd,
                    isset($patterns[$i + 1]) ?
                        ($patterns[$i + 1]->offsetStart - $patterns[$i]->offsetEnd) :
                        null
                );
        }

        return implode('', $pieces);
    }
}
