<?php

declare(strict_types=1);

namespace Battlescribe;

class SharedCategoryEntry extends CategoryEntry
{
    /** @psalm-var array<string,SharedCategoryEntry> */
    private static array $instances = [];

    public function __construct(
        string $id,
        string $name,
        bool $hidden
    )
    {
        parent::__construct($id, $name, $hidden);

        self::$instances[ $id ] = $this;
    }

    public static function get(string $id): self
    {
        return self::$instances[ $id ];
    }
}
