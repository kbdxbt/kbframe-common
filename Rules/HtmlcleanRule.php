<?php

namespace Modules\Common\Rules;

final class HtmlcleanRule extends Rule
{
    public function passes($attribute, $value)
    {
        return strip_tags($value) == $value;
    }
}
