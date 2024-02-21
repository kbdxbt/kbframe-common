<?php

namespace Modules\Common\Renderers;

/**
 * AutoFillHeight
 *
 * @author  slowlyo
 * @version 6.1.0
 */
class AutoFillHeight extends BaseRenderer
{
    public function __construct()
    {


    }

    /**
     *
     */
    public function height($value = '')
    {
        return $this->set('height', $value);
    }

    /**
     *
     */
    public function maxHeight($value = '')
    {
        return $this->set('maxHeight', $value);
    }


}
