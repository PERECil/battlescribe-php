<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use MyCLabs\Enum\Enum;

/**
 * @method static self SELECTION_ENTRY()
 * @method static self SELECTION_ENTRY_GROUP()
 */
class EntryLinkType extends Enum
{
    public const SELECTION_ENTRY = 'selectionEntry';
    public const SELECTION_ENTRY_GROUP = 'selectionEntryGroup';
}
