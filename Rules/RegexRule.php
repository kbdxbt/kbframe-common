<?php

namespace Modules\Common\Rules;

use Illuminate\Contracts\Validation\UncompromisedVerifier;

abstract class RegexRule extends Rule implements UncompromisedVerifier
{
    /**
     * REGEX pattern of rule
     *
     * @return string
     */
    abstract protected function pattern();

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return (bool) preg_match($this->pattern(), $value);
    }

    /**
     * Verify that the given data has not been compromised in data leaks.
     *
     * @param  mixed  $data
     * @return bool
     */
    public function verify($data)
    {
        return (bool) preg_match($this->pattern(), $data);
    }

    public function getPattern()
    {
        return $this->pattern();
    }
}
