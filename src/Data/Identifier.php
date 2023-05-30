<?php

declare(strict_types=1);


namespace Battlescribe\Data;

use Exception;

class Identifier
{
    private string $value;
    private ?self $child;

    public function __construct(string $value, ?self $child = null)
    {
        $this->value = $value;
        $this->child = $child;
    }

    public function equals($other): bool
    {
        if(is_string($other)) {
            throw new Exception( 'Should this be an identifier?' );
        }

        if(!$other instanceof self) {
            return false;
        }

        if($this->value !== $other->value) {
            return false;
        }

        if($this->child === null) {
            return $other->child === null;
        } else  {
            return $this->child->equals($other->child);
        }
    }

    public static function generate(): self
    {
        return new Identifier(bin2hex(random_bytes(2)).'-'.bin2hex(random_bytes(2)).'-'.bin2hex(random_bytes(2)).'-'.bin2hex(random_bytes(2)));
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value . ($this->child !== null ? '::' . $this->child->__toString() : '');
    }

    public function getChild(): ?self
    {
        return $this->child;
    }

    public static function fromString(?string $identifier): ?self
    {
        if($identifier === null) {
            return null;
        }

        $chunks = array_reverse(explode('::', $identifier));

        $previousIdentifier = null;

        foreach($chunks as $chunk) {
            $previousIdentifier = new Identifier($chunk, $previousIdentifier);
        }

        return $previousIdentifier;
    }

    public function getLast(): self
    {
        if($this->child !== null) {
            return $this->child->getLast();
        } else {
            return $this;
        }
    }
}
