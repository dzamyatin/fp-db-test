<?php

namespace FpDbTest\ParamProcessor;

interface EscapeStringConverterInterface
{
    public function escape(string $value): string;
}
