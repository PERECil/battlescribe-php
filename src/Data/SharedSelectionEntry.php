<?php

declare(strict_types=1);

namespace Battlescribe\Data;

class SharedSelectionEntry extends SelectionEntry
{
    private static array $instances = [];

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        string $name,
        bool $hidden,
        bool $collective,
        bool $import,
        SelectionEntryType $type
    )
    {
        parent::__construct($parent, $id, $name, $hidden, $collective, $import, $type);

        self::$instances[ $id->getValue() ] = $this;
    }

    public function __wakeup(): void
    {
        self::$instances[ $this->getId()->getValue() ] = $this;
    }

    public static function get(Identifier $id): ?self
    {
        return self::$instances[ $id->getValue() ] ?? null;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
