<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;

class InfoGroup implements InfoGroupInterface
{
    private const NAME = 'infoGroup';

    private string $id;
    private string $name;
    private bool $hidden;

    /** @var ProfileInterface[] */
    private array $profiles;

    public function __construct(string $id, string $name, bool $hidden)
    {
        $this->id = $id;
        $this->name = $name;
        $this->hidden = $hidden;

        $this->profiles = [];
    }

    public function getId(): string
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

    public static function fromXml(?SimpleXMLElementFacade $element): ?self
    {
        if ($element === null) {
            return null;
        }

        if ($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException('Received a ' . $element->getName() . ', expected ' . self::NAME);
        }

        $result = new self(
            $element->getAttribute('id')->asString(),
            $element->getAttribute('name')->asString(),
            $element->getAttribute('hidden')->asBool(),
        );

        foreach ($element->xpath('profiles/profile') as $profile) {
            $result->addProfile(Profile::fromXml($profile));
        }

        return $result;
    }
}
