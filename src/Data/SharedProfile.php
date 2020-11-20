<?php

declare(strict_types=1);

namespace Battlescribe\Data;

class SharedProfile extends Profile
{
    /** @psalm-var array<string,SharedProfile> */
    private static array $instances = [];

    public function __construct(string $id, string $name, bool $hidden, string $typeId, string $typeName)
    {
        parent::__construct($id, $name, $hidden, $typeId, $typeName);

        self::$instances[ $id ] = $this;
    }

    public static function get(string $id): self
    {
        return self::$instances[ $id ];
    }
}
