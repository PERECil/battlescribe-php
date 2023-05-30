<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Query\NameQuery;

interface ProfileInterface extends IdentifierInterface, HideableInterface, TreeInterface, NameInterface
{
    /** @return Characteristic[] */
    public function getCharacteristics(): array;

    public function getTypeId(): ?string;

    public function getTypeName(): ?string;
}
