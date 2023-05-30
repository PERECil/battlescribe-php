<?php

declare(strict_types=1);

namespace Battlescribe\Services\ImportRelease;

use stdClass;

class Release
{
    private string $tag;

    /** @var Asset[] */
    private array $assets;

    public function __construct(string $tag)
    {
        $this->assets = [];
        $this->tag = $tag;
    }

    public function addAsset(Asset $asset) {
        $this->assets[] = $asset;
    }

    public function getAsset(string $name): ?Asset
    {
        foreach($this->assets as $asset) {
            if($asset->getName() === $name) {
                return $asset;
            }
        }

        return null;
    }

    /**
     * @param string $pattern
     * @return Asset[]
     */
    public function findAsset(string $pattern): array
    {
        $result = [];

        foreach($this->assets as $asset) {
            if(fnmatch($pattern, $asset->getName())) {
                $result[] = $asset;
            }
        }

        return $result;
    }

    public function getAssets(): array
    {
        return $this->assets;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public static function fromJson(?stdClass $json): ?self
    {
        if($json === null) {
            return null;
        }

        $release = new Release(
            $json->tag_name
        );

        foreach($json->assets as $asset) {
            $release->addAsset(Asset::fromJson($asset));
        }

        return $release;
    }
}
