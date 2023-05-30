<?php

declare(strict_types=1);

namespace Battlescribe\Data;

class SharedSelectionEntryGroup extends SelectionEntryGroup
{
    private static array $instances = [];

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        string $name,
        bool $hidden,
        bool $collective,
        bool $import,
        ?Identifier $defaultSelectionEntryId
    )
    {
        parent::__construct($parent, $id, $name, $hidden, $collective, $import, $defaultSelectionEntryId);

        self::$instances[ $id->getValue() ] = $this;
    }

    public function __wakeup(): void
    {
        self::$instances[ $this->getId()->getValue() ] = $this;
    }

    public static function get(Identifier $id): self
    {
        return self::$instances[ $id->getValue() ];
    }
}
