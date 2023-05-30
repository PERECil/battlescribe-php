<?php

declare(strict_types=1);


namespace Battlescribe\Services\Download;

use Psr\Http\Message\ServerRequestInterface;

interface Credentials
{
    function applyTo(ServerRequestInterface $request): ServerRequestInterface;
}
