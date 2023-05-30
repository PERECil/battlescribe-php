<?php

declare(strict_types=1);


namespace Battlescribe\Services\Download;

use Psr\Http\Message\ServerRequestInterface;

class OAuthCredentials implements Credentials
{
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    function applyTo(ServerRequestInterface $request): ServerRequestInterface
    {
        return $request->withAddedHeader('Authorization', 'token '.$this->token);
    }
}
