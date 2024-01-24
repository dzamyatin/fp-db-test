<?php

namespace FpDbTest;

use Exception;
use mysqli;

class Database implements DatabaseInterface
{
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function buildQuery(string $query, array $args = []): string
    {
        //\?[a-z#]{0,1}|\(\?[a-z#]{0,1}\)\{.+\}
        //\?[a-z#]{0,1}|\(\s{0,}\?[a-z#]{0,1}\s{0,}\)\{.+\}
        //\?[a-z#]{0,1}|\(\s{0,}\?[a-z#]{0,1}\s{0,}\)\{.+\}

        $matches = [];
        preg_match_all(
            '/(?<pattern1>\?d)|(?<pattern2>\?a)/',
            '?a asd ?d asd',
            $matches,
            PREG_OFFSET_CAPTURE |
            PREG_PATTERN_ORDER |
            PREG_UNMATCHED_AS_NULL
        );

        var_dump($matches);

        die();

        return '';
    }

    public function skip()
    {
        throw new Exception();
    }
}
