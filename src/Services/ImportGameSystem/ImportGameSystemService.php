<?php

declare(strict_types=1);


namespace Battlescribe\Services\ImportGameSystem;

use Battlescribe\Utils\SimpleXmlElementFacade;

class ImportGameSystemService
{
    public function importGameSystem(): void
    {
        $contents = str_replace( ' xmlns="http://www.battlescribe.net/schema/dataIndexSchema"', '', $contents );

        $element = simplexml_load_string( $contents);

        $xml = new SimpleXmlElementFacade($element);

        $gameSystemId = $xml->getAttribute('gameSystemId')->asString();
        $gameSystemRevision = $xml->getAttribute('gameSystemRevision')->asInt();

        $gameSystemEntry = $this->gameSystemDiscoveryService->discoverGameSystem($gameSystemId);

        $repository = $this->importRepositoryService->import($gameSystemEntry->getRepository());

        if($repository->getGameSystem()->getRevision() !== $gameSystemRevision) {
            throw new RevisionMismatchException();
        }
    }
}
