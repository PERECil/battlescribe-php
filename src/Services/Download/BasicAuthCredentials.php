<?php

declare(strict_types=1);


namespace Battlescribe\Services\Download;

use Psr\Http\Message\ServerRequestInterface;

class BasicAuthCredentials implements Credentials
{
    private string $token;

    public function __construct(string $user, ?string $password)
    {
        $nonCodedToken = $user;

        if($password !== null) {
            $nonCodedToken .= ':'.$password;
        }

        $this->token = base64_encode($nonCodedToken);
    }

    function applyTo(ServerRequestInterface $request): ServerRequestInterface
    {
        return $request->withAddedHeader('Authorization', 'Basic '.$this->token);
    }
}
