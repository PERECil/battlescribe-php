<?php

declare(strict_types=1);

namespace Battlescribe;

use MyCLabs\Enum\Enum;

/**
 * @method static self ADD()
 * @method static self APPEND()
 * @method static self DECREMENT()
 * @method static self INCREMENT()
 * @method static self REMOVE()
 * @method static self SET()
 * @method static self SET_PRIMARY()
 */
class ModifierType extends Enum
{
    public const ADD = 'add';
    public const APPEND = 'append';
    public const DECREMENT = 'decrement';
    public const INCREMENT = 'increment';
    public const REMOVE = 'remove';
    public const SET = 'set';
    public const SET_PRIMARY = 'set-primary';
}
