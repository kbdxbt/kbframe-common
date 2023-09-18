<?php

namespace Modules\Common\Rules\Concerns;

use Illuminate\Validation\Validator;

trait ValidatorAware
{
    protected Validator $validator;

    /**
     * Set the current validator.
     *
     * @return $this
     */
    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;

        return $this;
    }
}
