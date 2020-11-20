<?php

declare(strict_types=1);

namespace Battlescribe\Data;

class SharedSelectionEntry extends SelectionEntry
{
    private static array $instances = [];

    public function __construct(
        ?IdentifierInterface $parent,
        string $id,
        string $name,
        bool $hidden,
        bool $collective,
        bool $import,
        SelectionEntryType $type
    )
    {
        parent::__construct($parent, $id, $name, $hidden, $collective, $import, $type);

        self::$instances[ $id ] = $this;
    }

    public static function get(string $id): self
    {
        return self::$instances[ $id ];
    }
}
