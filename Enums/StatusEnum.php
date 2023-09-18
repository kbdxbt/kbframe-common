<?php

namespace Modules\Common\Enums;

use Modules\Core\Enums\BaseEnum;

/**
 * @method static static ENABLED()
 * @method static static DISABLED()
 * @method static static DELETE()
 */
final class StatusEnum extends BaseEnum
{
    public const ENABLED = 1;

    public const DISABLED = 0;

    public const DELETE = -1;
}
