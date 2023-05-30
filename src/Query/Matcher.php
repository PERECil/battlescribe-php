<?php

declare(strict_types=1);


namespace Battlescribe\Query;

use Battlescribe\Data\CategoryEntryInterface;
use Battlescribe\Data\Identifier;
use Battlescribe\Data\IdentifierInterface;
use Battlescribe\Data\NameInterface;
use Battlescribe\Data\TreeInterface;
use Closure;

class Matcher
{
    public static function allOf(Closure ...$conditions): Closure
    {
        return fn($e) => array_reduce($conditions, fn(bool $carry, Closure $c) => $carry && $c($e), true);
    }

    public static function type(string $type): Closure
    {
        return fn($e) => $e instanceof $type;
    }

    public static function id(string|Identifier $id): Closure
    {
        if(!($id instanceof Identifier)) {
            $id = new Identifier($id);
        }

        return fn($e) => $e instanceof IdentifierInterface && $e->getId()->equals($id);
    }

    public static function name(string $name): Closure
    {
        return fn($e) => $e instanceof NameInterface && $e->getName() === $name;
    }

    public static function isPrimaryCategory(): Closure
    {
        return fn($e) => $e instanceof CategoryEntryInterface && $e->isPrimary() === true;
    }
}
