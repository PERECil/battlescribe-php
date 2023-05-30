<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;
use UnexpectedValueException;

class InfoLink implements IdentifierInterface, TreeInterface
{
    use LeafTrait;

    private const NAME = 'infoLink';

    private Identifier $id;
    private ?string $name;
    private bool $hidden;
    private Identifier $targetId;
    private InfoLinkType $type;

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        ?string $name,
        bool $hidden,
        Identifier $targetId,
        InfoLinkType $type
    )
    {
        $this->parent = $parent;

        $this->id = $id;
        $this->name = $name;
        $this->hidden = $hidden;
        $this->targetId = $targetId;
        $this->type = $type;
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    /** @return ProfileReference|RuleReference|InfoGroupReference */
    public function getLinkedObject()
    {
        switch($this->type->getValue())
        {
            case InfoLinkType::PROFILE: return new ProfileReference($this, $this->targetId);
            case InfoLinkType::RULE: return new RuleReference($this, $this->targetId);
            case InfoLinkType::INFO_GROUP: return new InfoGroupReference($this, $this->targetId);
            default:
                throw new UnexpectedValueException();
        }
    }

    public static function fromXml(?TreeInterface $parent, ?SimpleXmlElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        if($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException( 'Received a '.$element->getName().', expected '.self::NAME);
        }

        return new self(
            $parent,
            $element->getAttribute('id')->asIdentifier(),
            $element->getAttribute('name')->asString(),
            $element->getAttribute('hidden')->asBoolean(),
            $element->getAttribute('targetId')->asIdentifier(),
            $element->getAttribute('type')->asEnum( InfoLinkType::class),
        );
    }
}
