<?php

declare(strict_types=1);

namespace Battlescribe\Services\ImportRepository;

use Battlescribe\Services\ImportRelease\Release;

class CachedImportRepositoryService implements ImportRepositoryService
{
    private const CACHE_PATH = __DIR__.'/../../../../../cache/battlescribe/';
    private const CACHE_FILE = '/repository.bin';

    private DefaultImportRepositoryService $defaultImportRepositoryService;

    public function __construct(DefaultImportRepositoryService $defaultImportRepositoryService)
    {
        $this->defaultImportRepositoryService = $defaultImportRepositoryService;
    }

    public function import(string $repository, Release $release): ?Repository
    {
        $cacheFile = $this->getCacheFile($repository, $release->getTag());

        if(file_exists($cacheFile)) {
            return unserialize(file_get_contents($cacheFile));
        }

        $repository = $this->defaultImportRepositoryService->import($repository, $release);

        file_put_contents($cacheFile, serialize($repository));

        return $repository;
    }

    private function getCacheFile(string $repository, string $releaseTag = 'latest'): string
    {
        return self::CACHE_PATH.$repository.'/'.$releaseTag.self::CACHE_FILE;
    }
}
