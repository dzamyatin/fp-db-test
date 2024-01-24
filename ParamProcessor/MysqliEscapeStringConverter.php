<?php

namespace FpDbTest\ParamProcessor;

use mysqli;

class MysqliEscapeStringConverter implements EscapeStringConverterInterface
{
    public function __construct(private mysqli $mysqli)
    {
    }

    public function escape(string $value): string
    {
        return $this->mysqli->escape_string($value);
    }
}
