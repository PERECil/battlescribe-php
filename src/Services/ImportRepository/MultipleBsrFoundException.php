<?php

declare(strict_types=1);

namespace Battlescribe\Services\ImportRepository;

use Exception;

class MultipleBsrFoundException extends Exception
{
    public function __construct(string $repository)
    {
        parent::__construct( "Too many BSR files not found in ".$repository);
    }
}
