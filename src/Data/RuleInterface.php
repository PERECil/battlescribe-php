<?php

declare(strict_types=1);

namespace Battlescribe\Data;

interface RuleInterface extends IdentifierInterface, TreeInterface, NameInterface
{
    public function getDescription(): ?string;
}
