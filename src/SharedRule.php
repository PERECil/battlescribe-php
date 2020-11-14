<?php

declare(strict_types=1);


namespace Battlescribe;

class SharedRule extends Rule
{
    /** @psalm-var array<string,SharedRule> */
    private static array $instances = [];

    public function __construct(string $id, string $name, bool $hidden, ?string $description)
    {
        parent::__construct($id, $name, $hidden, $description);

        self::$instances[ $id ] = $this;
    }

    public static function get(string $id): self
    {
        return self::$instances[ $id ];
    }
}
