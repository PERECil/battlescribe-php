<?php

declare(strict_types=1);

namespace Battlescribe\Services\ImportRepository;

use Battlescribe\Data\Catalog;
use Battlescribe\Data\CatalogueInterface;
use Battlescribe\Data\FindByMatcherTrait;
use Battlescribe\Data\Identifier;
use Battlescribe\Utils\SimpleXmlElementFacade;
use Exception;

/**
 * Lazy-load version of the Catalogue object, allows to keep a reference to the file, but do not load everything
 */
class CatalogueReference implements CatalogueInterface
{
    use FindByMatcherTrait;

    private string $fileName;
    private Identifier $identifier;
    private ?Catalog $catalog;

    public function __construct(string $fileName, Identifier $identifier)
    {
        $this->fileName = $fileName;
        $this->identifier = $identifier;
        $this->catalog = null;
    }

    public function getId(): Identifier
    {
        return $this->identifier;
    }

    public function getName(): string
    {
        return $this->catalog->getName();
    }

    public static function fromFile(string $file): ?self
    {
        $data  = file_get_contents($file, false, null, 0, 1024);

        if(preg_match('%<catalogue id="([0-9a-f-]+)"%', $data, $matches) !== 1) {
            throw new Exception($data);
        }

        return new self($file, Identifier::fromString($matches[1]) );
    }

    public function isLoaded(): bool
    {
        return $this->catalog !== null;
    }

    public function load(): void
    {
        $binaryFile = $this->fileName.'.bin';

        if(file_exists($binaryFile)) {
            $this->catalog = unserialize(file_get_contents($binaryFile));
        } else {
            $this->catalog = Catalog::fromFile($this->fileName);
            file_put_contents($binaryFile, serialize($this->catalog));
        }
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return $this->catalog->getChildren();
    }

    /** @inheritDoc */
    public function getCatalogueLinks(): array
    {
        return $this->catalog->getCatalogueLinks();
    }

    /** @inheritDoc */
    public function getPublications(): array
    {
        return $this->catalog->getPublications();
    }
}
