<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;
use Closure;

class DataIndex implements TreeInterface
{
    use RootTrait;

    private const NAME = 'dataIndex';

    private string $basePath;
    private string $name;
    private string $battlescribeVersion;

    /** @var DataIndexEntry[] */
    private array $dataIndexEntries;

    public function __construct(string $basePath, string $name, string $battlescribeVersion)
    {
        $this->basePath = $basePath;
        $this->name = $name;
        $this->battlescribeVersion = $battlescribeVersion;
        $this->dataIndexEntries = [];
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return array_merge(
            $this->dataIndexEntries,
        );
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBattlescribeVersion(): string
    {
        return $this->battlescribeVersion;
    }

    public function addDataIndexEntry(DataIndexEntry $dataIndexEntry): void
    {
        $this->dataIndexEntries[] = $dataIndexEntry;
    }

    /**
     * @return DataIndexEntry[]
     */
    public function getDataIndexEntries(): array
    {
        return $this->dataIndexEntries;
    }

    public static function fromFile(string $file): ?self
    {
        $basePath = dirname($file);

        $data  = file_get_contents($file);

        $data = str_replace( ' xmlns="http://www.battlescribe.net/schema/dataIndexSchema"', '', $data );

        $element = simplexml_load_string( $data);

        $xml = new SimpleXmlElementFacade($element);

        return self::fromXml($basePath, $xml);
    }

    public static function fromXml(string $basePath, ?SimpleXMLElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        if($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException( 'Received a '.$element->getName().', expected '.self::NAME);
        }

        $result = new self(
            $basePath,
            $element->getAttribute('name')->asString(),
            $element->getAttribute('battleScribeVersion')->asString()
        );

        foreach($element->xpath('dataIndexEntries/dataIndexEntry') as $dataIndexEntry) {
            $result->addDataIndexEntry(DataIndexEntry::fromXml($result, $dataIndexEntry));
        }

        return $result;
    }
}
