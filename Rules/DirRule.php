<?php

namespace Modules\Common\Rules;

final class DirRule extends RegexRule
{
    protected function pattern(): string
    {
        /** @lang PhpRegExp */
        return '/\.|\\\|\\/|\:|\*|\?|\"|\<|\>|\|/';
    }
}
