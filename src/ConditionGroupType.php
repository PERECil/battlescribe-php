<?php

declare(strict_types=1);

namespace Battlescribe;

use MyCLabs\Enum\Enum;

/**
 * @method static self AND()
 * @method static self OR()
 */
class ConditionGroupType extends Enum
{
    public const AND = 'and';
    public const OR = 'or';
}
