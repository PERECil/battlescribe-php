<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;

class InfoGroup implements InfoGroupInterface
{
    use BranchTrait;

    private const NAME = 'infoGroup';

    private Identifier $id;
    private string $name;
    private bool $hidden;

    /** @var ProfileInterface[] */
    private array $profiles;

    public function __construct(?TreeInterface $parent, Identifier $id, string $name, bool $hidden)
    {
        $this->parent = $parent;

        $this->id = $id;
        $this->name = $name;
        $this->hidden = $hidden;

        $this->profiles = [];
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return array_merge(
            $this->profiles
        );
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function addProfile(ProfileInterface $profile)
    {
        $this->profiles[] = $profile;
    }

    public function getProfiles(): array
    {
        return $this->profiles;
    }

    public static function fromXml(?TreeInterface $parent, ?SimpleXMLElementFacade $element): ?self
    {
        if ($element === null) {
            return null;
        }

        if ($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException('Received a ' . $element->getName() . ', expected ' . self::NAME);
        }

        $result = new self(
            $parent,
            $element->getAttribute('id')->asString(),
            $element->getAttribute('name')->asString(),
            $element->getAttribute('hidden')->asBoolean(),
        );

        foreach ($element->xpath('profiles/profile') as $profile) {
            $result->addProfile(Profile::fromXml($result, $profile));
        }

        return $result;
    }
}
