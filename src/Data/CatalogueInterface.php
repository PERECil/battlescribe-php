<?php

declare(strict_types=1);


namespace Battlescribe\Data;

interface CatalogueInterface extends IdentifierInterface, NameInterface
{
    /** @return Publication[]  */
    public function getPublications(): array;

    /** @return CatalogueLink[] */
    public function getCatalogueLinks(): array;
}
