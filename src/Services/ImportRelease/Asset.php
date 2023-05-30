<?php

declare(strict_types=1);

namespace Battlescribe\Services\ImportRelease;

use stdClass;

class Asset
{
    private string $url;

    private string $name;

    public function __construct(string $url, string $name)
    {
        $this->url = $url;
        $this->name = $name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function fromJson(?stdClass $json): ?self
    {
        if($json === null) {
            return null;
        }

        return new Asset(
            $json->browser_download_url,
            $json->name
        );
    }
}
