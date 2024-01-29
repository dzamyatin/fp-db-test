<?php

namespace FpDbTest\PostProcessor;

use FpDbTest\DatabaseInterface;

class ConditionPostProcessor implements PostProcessorInterface
{
    public function process(string $query, DatabaseInterface $database): string
    {
        return $query; //@TODO

        $matches = [];

        preg_match_all(
            '/\(((.|\n)*?)\)((.|\n)*?)\{((.|\n)*?)\}/',
            $query,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        $matches = array_filter($matches);

        if (!$matches) {
            return $query;
        }

        var_dump($matches); die();
    }
}
