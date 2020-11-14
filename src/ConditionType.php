<?php

declare(strict_types=1);

namespace Battlescribe;

use MyCLabs\Enum\Enum;

/**
 * @method static self AT_LEAST()
 * @method static self EQUAL_TO()
 * @method static self GREATER_THAN()
 * @method static self INSTANCE_OF()
 * @method static self LESS_THAN()
 * @method static self NOT_INSTANCE_OF()
 * @method static self NOT_EQUAL_TO()
 */
class ConditionType extends Enum
{
    public const AT_LEAST = 'atLeast';
    public const EQUAL_TO = 'equalTo';
    public const GREATER_THAN = 'greaterThan';
    public const INSTANCE_OF = 'instanceOf';
    public const LESS_THAN = 'lessThan';
    public const NOT_INSTANCE_OF = 'notInstanceOf';
    public const NOT_EQUAL_TO = 'notEqualTo';
}
