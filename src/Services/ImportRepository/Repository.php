<?php

declare(strict_types=1);

namespace Battlescribe\Services\ImportRepository;

use Battlescribe\Data\Catalog;
use Battlescribe\Data\DataIndex;
use Battlescribe\Data\DataIndexEntryType;
use Battlescribe\Data\FindByMatcherTrait;
use Battlescribe\Data\GameSystem;
use Battlescribe\Data\Identifier;
use Battlescribe\Data\Publication;
use Battlescribe\Services\ImportRelease\Release;
use Closure;
use UnexpectedValueException;

class Repository
{
    use FindByMatcherTrait;

    private Release $release;
    private DataIndex $dataIndex;
    private GameSystem $gameSystem;

    /** @var CatalogueReference[] */
    private array $catalogs;

    public function __construct(Release $release, DataIndex $dataIndex, GameSystem $gameSystem, array $catalogs = [])
    {
        $this->release = $release;
        $this->dataIndex = $dataIndex;
        $this->gameSystem = $gameSystem;
        $this->catalogs = $catalogs;
    }

    public function getRelease(): Release
    {
        return $this->release;
    }

    public function getDataIndex(): DataIndex
    {
        return $this->dataIndex;
    }

    public function getGameSystem(): GameSystem
    {
        return $this->gameSystem;
    }

    public function getCatalogs(): array
    {
        return $this->catalogs;
    }

    public function addCatalog(CatalogueReference $catalog): void
    {
        $this->catalogs[] = $catalog;
    }

    public function getChildren(): array
    {
        return array_merge(
            [ $this->gameSystem ],
            array_filter( $this->catalogs, fn(CatalogueReference $c) => $c->isLoaded()),
        );
    }

    public function findCatalogById(Identifier $id): ?CatalogueReference
    {
        foreach($this->catalogs as $catalog) {
            if($catalog->getId()->equals($id)) {
                return $catalog;
            }
        }

        return null;
    }

    public function findPublicationById(Identifier $id): ?Publication
    {
        static $cache = [];

        if(array_key_exists($id->getValue(), $cache)) {
            return $cache[$id->getValue()];
        }

        foreach($this->gameSystem->getPublications() as $publication) {
            if($publication->getId()->equals($id)) {
                $cache[$id->getValue()] = $publication;
                return $publication;
            }
        }

        foreach($this->catalogs as $catalog) {
            if($catalog->isLoaded()) {
                foreach($catalog->getPublications() as $publication) {
                    if($publication->getId()->equals($id)) {
                        $cache[$id->getValue()] = $publication;
                        return $publication;
                    }
                }
            }
        }

        return null;
    }

    public static function fromDataIndex(Release $release, DataIndex $index): Repository
    {
        $gameSystem = null;
        $catalogs = [];

        foreach($index->getDataIndexEntries() as $dataIndexEntry) {
            switch($dataIndexEntry->getType()) {
                case DataIndexEntryType::CATALOG:
                    $catalogs[] = CatalogueReference::fromFile( $index->getBasePath().'/'.$dataIndexEntry->getFilePath() );
                    break;
                case DataIndexEntryType::GAME_SYSTEM:
                    $gameSystem = GameSystem::fromFile($index->getBasePath().'/'.$dataIndexEntry->getFilePath());
                    break;
                default:
                    throw new UnexpectedValueException( 'Unhandled data index entry type "'.$dataIndexEntry->getType().'"');
            }
        }

        if($gameSystem === null) {
            throw new UnexpectedValueException( 'Missing game system in index' );
        }

        return new Repository($release, $index, $gameSystem, $catalogs);
    }
}
