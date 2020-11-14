<?php

declare(strict_types=1);

namespace Battlescribe;

use MyCLabs\Enum\Enum;

/**
 * @method static self PROFILE()
 * @method static self RULE()
 * @method static self INFO_GROUP()
 */
class InfoLinkType extends Enum
{
     public const PROFILE = 'profile';
     public const RULE = 'rule';
     public const INFO_GROUP = 'infoGroup';
}
