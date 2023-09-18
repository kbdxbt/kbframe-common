<?php

namespace Modules\Common\Rules;

final class ChineseWordRule extends RegexRule
{
    protected function pattern(): string
    {
        /** @lang PhpRegExp */
        return '/^(?:[\u4e00-\u9fa5·]{2,16})$/';
    }
}
