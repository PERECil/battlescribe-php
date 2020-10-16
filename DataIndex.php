<?php

declare(strict_types=1);

namespace Battlescribe;

class DataIndex
{
    private const NAME = 'dataIndex';

    private string $name;
    private string $battlescribeVersion;

    /** @var DataIndexEntry[] */
    private array $dataIndexEntries;

    public function __construct(string $name, string $battlescribeVersion)
    {
        $this->name = $name;
        $this->battlescribeVersion = $battlescribeVersion;
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
        $data  = file_get_contents($file);

        $data = str_replace( ' xmlns="http://www.battlescribe.net/schema/dataIndexSchema"', '', $data );

        $element = simplexml_load_string( $data);

        $xml = new SimpleXmlElementFacade($element);

        return self::fromXml($xml);
    }

    public static function fromXml(?SimpleXMLElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        if($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException( 'Received a '.$element->getName().', expected '.self::NAME);
        }

        $result = new self(
            $element->getAttribute('name')->asString(),
            $element->getAttribute('battleScribeVersion')->asString()
        );

        foreach($element->xpath('dataIndexEntries/dataIndexEntry') as $dataIndexEntry) {
            $result->addDataIndexEntry(DataIndexEntry::fromXml($dataIndexEntry));
        }

        return $result;
    }
}
