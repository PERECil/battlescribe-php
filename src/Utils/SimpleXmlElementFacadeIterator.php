<?php

declare(strict_types=1);

namespace Battlescribe\Utils;

use ArrayIterator;

class SimpleXmlElementFacadeIterator extends ArrayIterator
{
    public function current(): ?SimpleXmlElementFacade
    {
        return new SimpleXmlElementFacade(parent::current());
    }
}
