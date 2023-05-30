<?php

declare(strict_types=1);

namespace Battlescribe\Services\ImportRepository;

use Battlescribe\Services\ImportRelease\Release;

interface ImportRepositoryService
{
    public function import(string $repository, Release $release): ?Repository;
}
