<?php

declare(strict_types=1);

namespace Battlescribe\Data;

class SharedInfoGroup extends InfoGroup
{
    /** @psalm-var array<string,SharedInfoGroup> */
    private static array $instances = [];

    public function __construct(Identifier $id, string $name, bool $hidden)
    {
        parent::__construct($id->getValue(), $name, $hidden);

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
