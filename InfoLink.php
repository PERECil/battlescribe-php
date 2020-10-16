<?php

declare(strict_types=1);

namespace Battlescribe;

class InfoLink
{
    private const NAME = 'infoLink';

    private string $id;
    private string $name;
    private bool $hidden;
    private string $targetId;
    private InfoLinkType $type;

    public function __construct(
        string $id,
        string $name,
        bool $hidden,
        string $targetId,
        InfoLinkType $type
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->hidden = $hidden;
        $this->targetId = $targetId;
        $this->type = $type;
    }

    public static function fromXml(?SimpleXmlElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        if($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException( 'Received a '.$element->getName().', expected '.self::NAME);
        }

        return new self(
            $element->getAttribute('id')->asString(),
            $element->getAttribute('name')->asString(),
            $element->getAttribute('hidden')->asBoolean(),
            $element->getAttribute('targetId')->asString(),
            $element->getAttribute('type')->asEnum( InfoLinkType::class),
        );
    }
}