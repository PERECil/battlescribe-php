<?php

declare(strict_types=1);

namespace Battlescribe\Services\ImportRelease;

class CachedImportReleaseService implements ImportReleaseService
{
    public const CACHE_PATH = __DIR__.'/../../../../../cache/battlescribe/';

    public function importRelease(string $repository, string $release = 'latest'): ?Release
    {
        $this->makeFolder( self::CACHE_PATH );
        $this->makeFolder(self::CACHE_PATH.$repository);

        if($release === 'latest') {
            $release = $this->findLatestRelease($repository);
        }

        if($release === null) {
            return null;
        }

        $result = new Release($release);

        foreach(scandir(self::CACHE_PATH.$repository.'/'.$release.'/') as $file) {
            if($file !== '.' && $file !== '..') {
                $result->addAsset(new Asset($file, basename($file)));
            }
        }

        return $result;
    }

    private function findLatestRelease(string $repository): ?string
    {
        $latestRelease = null;

        foreach(scandir(self::CACHE_PATH.$repository ) as $file) {
            if($file !== '.' && $file !== '..' && is_dir(self::CACHE_PATH.$repository.'/'.$file)) {
                if(file_exists( self::CACHE_PATH.$repository.'/'.$file.'/index.xml' ) && $this->compareVersions($latestRelease, $file) === -1 ) {
                    $latestRelease = $file;
                }
            }
        }

        return $latestRelease;
    }

    private function makeFolder(string $path): void
    {
        if(!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    private function compareVersions(?string $current, ?string $candidate): int
    {
        if($current === null) {
            return -1;
        }

        return version_compare( preg_replace( '%^v%', '', $current ), preg_replace( '%^v%', '', $candidate ) );
    }
}
