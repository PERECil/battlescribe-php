<?php

declare(strict_types=1);


namespace Battlescribe\Services\ImportRepository;

use Battlescribe\Services\ImportRelease\Release;
use Exception;

class BsrNotFoundException extends Exception
{
    public function __construct(string $repository, Release $release)
    {
        parent::__construct( "BSR File not found in ".$repository." for release ".$release->getTag());
    }
}
