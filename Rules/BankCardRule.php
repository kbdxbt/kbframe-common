<?php

namespace Modules\Common\Rules;

final class BankCardRule extends RegexRule
{
    protected function pattern(): string
    {
        /** @lang PhpRegExp */
        return '/[\x{4e00}-\x{9fa5}]+/u';
    }
}
