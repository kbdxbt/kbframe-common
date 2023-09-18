<?php

namespace Modules\Common\Rules\Concerns;

trait DataAware
{
    protected array $data;

    /**
     * Set the data under validation.
     *
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }
}
