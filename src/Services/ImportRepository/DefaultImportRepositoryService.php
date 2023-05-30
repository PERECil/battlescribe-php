<?php

declare(strict_types=1);

namespace Battlescribe\Services\ImportRepository;

use Battlescribe\Data\Catalog;
use Battlescribe\Data\DataIndex;
use Battlescribe\Data\DataIndexEntryType;
use Battlescribe\Data\GameSystem;
use Battlescribe\Services\Download\DownloadService;
use Battlescribe\Services\ImportRelease\ImportReleaseService;
use Battlescribe\Services\ImportRelease\Release;
use Monolog\Logger;
use UnexpectedValueException;
use ZipArchive;

class DefaultImportRepositoryService implements ImportRepositoryService
{
    public const CACHE_PATH = __DIR__.'/../../../../../cache/battlescribe/';

    public function __construct()
    {
    }

    public function import(string $repository, Release $release): ?Repository
    {
        if(!$this->dataExists($repository, $release->getTag())) {
            $assets = $release->findAsset( '*.bsr' );

            if(count($assets) === 0) {
                throw new BsrNotFoundException($repository, $release);
            }

            if(count($assets) > 1) {
                throw new MultipleBsrFoundException($repository);
            }

            $this->unpack($repository, $release->getTag(), $assets[0]->getUrl());
        }

        $index = $this->index($repository, $release->getTag());

        // Import order is important here, as catalogs base themselves on the game system to work
        $gameSystem = $this->importGameSystem($repository, $index, $release->getTag());
        $catalogs = $this->importCatalogs($repository, $index, $release->getTag());

        return new Repository($release, $index, $gameSystem, $catalogs);
    }

    private function importGameSystem(string $repository, DataIndex $index, string $tag): GameSystem
    {
        foreach($index->getDataIndexEntries() as $dataIndexEntry) {
            if(DataIndexEntryType::GAME_SYSTEM()->equals($dataIndexEntry->getType())) {
                return GameSystem::fromFile( self::CACHE_PATH.$repository.'/'.$tag.'/'.$dataIndexEntry->getFilePath() );
            }
        }

        throw new UnexpectedValueException('GameSystem not found');
    }

    /**
     * @param string $repository
     * @param DataIndex $index
     * @param string $tag
     * @return Catalog[]
     */
    private function importCatalogs(string $repository, DataIndex $index, string $tag): array
    {
        $catalogs = [];

        foreach($index->getDataIndexEntries() as $dataIndexEntry) {
            if(DataIndexEntryType::CATALOG()->equals($dataIndexEntry->getType())) {
                $catalogs[] = CatalogueReference::fromFile( self::CACHE_PATH.$repository.'/'.$tag.'/'.$dataIndexEntry->getFilePath() );
            }
        }

        return $catalogs;
    }

    private function dataExists(string $repository, string $tagName): bool
    {
        $this->makeFolder( self::CACHE_PATH );
        $this->makeFolder(self::CACHE_PATH.$repository);

        $destinationFolder = self::CACHE_PATH.$repository.'/'.$tagName.'/';

        // Abort unpacking if the data has already been unpacked
        return file_exists($destinationFolder);
    }

    private function unpack(string $repository, string $tagName, string $url): void
    {
        $this->makeFolder( self::CACHE_PATH );
        $this->makeFolder(self::CACHE_PATH.$repository);

        $destinationFolder = self::CACHE_PATH.$repository.'/'.$tagName.'/';

        $this->makeFolder($destinationFolder);

        $destinationFile = self::CACHE_PATH.$repository.'/'.$tagName.'.zip';

        copy($url, $destinationFile);

        $archive = new ZipArchive();
        $archive->open($destinationFile);

        // Unzip error delete everything to be able to restart from scratch
        if($archive->extractTo($destinationFolder) === false) {
            unlink($destinationFile);
            unlink($destinationFolder);
            throw new ArchiveExtractionException();
        }
    }

    private function makeFolder(string $path): void
    {
        if(!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    private function index(string $repository, string $tagName): DataIndex
    {
        return DataIndex::fromFile(self::CACHE_PATH.$repository.'/'.$tagName.'/index.xml');
    }
}
