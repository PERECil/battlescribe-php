<?php

declare(strict_types=1);

namespace Battlescribe\Data;

class SharedCategoryEntry extends CategoryEntry
{
    /** @psalm-var array<string,SharedCategoryEntry> */
    private static array $instances = [];

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        string $name,
        bool $hidden
    )
    {
        parent::__construct($parent, $id, $name, $hidden);

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
