<?php

declare(strict_types=1);

namespace Battlescribe;

use MyCLabs\Enum\Enum;

/**
 * @method static self MODEL()
 * @method static self UNIT()
 * @method static self UPGRADE()
 */
class SelectionEntryType extends Enum
{
    public const MODEL = 'model';
    public const UNIT = 'unit';
    public const UPGRADE = 'upgrade';
}
