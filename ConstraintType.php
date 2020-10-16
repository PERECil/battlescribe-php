<?php

declare(strict_types=1);

namespace Battlescribe;

use MyCLabs\Enum\Enum;

/**
 * @method static self MAX()
 * @method static self MIN()
 */
class ConstraintType extends Enum
{
    public const MAX = 'max';
    public const MIN = 'min';
}
