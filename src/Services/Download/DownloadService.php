<?php

declare(strict_types=1);

namespace Battlescribe\Services\Download;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;

class DownloadService
{
    private ?Credentials $credentials;

    public function __construct()
    {
        $this->credentials = null;
    }

    public function withCredentials(Credentials $credentials): self
    {
        $this->credentials = $credentials;

        return $this;
    }

    public function download(string $url): string
    {
        $client = Psr18ClientDiscovery::find();

        $request = Psr17FactoryDiscovery::findServerRequestFactory()->createServerRequest('GET', $url);

        if($this->credentials !== null) {
            $request = $this->credentials->applyTo($request);
        }

        $response = $client->sendRequest($request);

        if($response->getStatusCode() !== 200) {
            throw new DownloadException($response);
        }

        return $response->getBody()->getContents();
    }
}
