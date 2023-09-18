<?php

namespace Modules\Common\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Modules\Common\Rules\Concerns\ValidatorAware;

final class DefaultRule extends Rule implements ImplicitRule, ValidatorAwareRule
{
    use ValidatorAware;

    /**
     * @var null|mixed
     */
    protected $default;

    public function __construct($default)
    {
        $this->default = $default;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($value === null) {
            $data = $this->validator->getData();
            $data[$attribute] = $this->default;
            $this->validator->setData($data);
        }

        return true;
    }
}
