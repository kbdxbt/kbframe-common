<?php

namespace Modules\Common\Enums;

use Modules\Core\Enums\BaseEnum;

/**
 * @method static static OK()
 * @method static static WARNING()
 * @method static static FAILING()
 */
final class HealthCheckStateEnum extends BaseEnum
{
    public const OK = '<info>ok</info>';

    public const WARNING = '<comment>warning</comment>';

    public const FAILING = '<error>failing</error>';
}
