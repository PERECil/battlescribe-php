<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;

class DataIndexEntry
{
    private string $id;
    private string $name;
    private string $filePath;
    private DataIndexEntryType $type;
    private string $battlescribeVersion;
    private int $revision;

    public function __construct(string $id, string $name, string $filePath, DataIndexEntryType $type, string $battlescribeVersion, int $revision )
    {
        $this->id = $id;
        $this->name = $name;
        $this->filePath = $filePath;
        $this->type = $type;
        $this->battlescribeVersion = $battlescribeVersion;
        $this->revision = $revision;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getType(): DataIndexEntryType
    {
        return $this->type;
    }

    public function getBattlescribeVersion(): string
    {
        return $this->battlescribeVersion;
    }

    public function getRevision(): int
    {
        return $this->revision;
    }

    public static function fromXml(?SimpleXmlElementFacade $element): ?self
    {
        return new self(
            $element->getAttribute('dataId')->asString(),
            $element->getAttribute('dataName')->asString(),
            $element->getAttribute('filePath')->asString(),
            $element->getAttribute('dataType')->asEnum(DataIndexEntryType::class),
            $element->getAttribute('dataBattleScribeVersion')->asString(),
            $element->getAttribute('dataRevision')->asInt(),
        );
    }
}
