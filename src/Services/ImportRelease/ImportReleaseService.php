<?php

declare(strict_types=1);

namespace Battlescribe\Services\ImportRelease;

interface ImportReleaseService
{
    public function importRelease(string $repository, string $release = 'latest'): ?Release;
}
