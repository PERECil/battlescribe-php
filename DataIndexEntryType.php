<?php

declare(strict_types=1);

namespace Battlescribe;

use MyCLabs\Enum\Enum;

/**
 * @method static self GAME_SYSTEM()
 * @method static self CATALOG()
 */
class DataIndexEntryType extends Enum
{
    public const GAME_SYSTEM = 'gamesystem';
    public const CATALOG = 'catalogue';
}
