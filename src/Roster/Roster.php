<?php

declare(strict_types=1);

namespace Battlescribe\Roster;

use Battlescribe\Data\Cost;
use Battlescribe\Data\CostInterface;
use Battlescribe\Data\ForceEntry;
use Battlescribe\Data\GameSystem;
use Battlescribe\Data\Identifier;
use Battlescribe\Data\IdentifierInterface;
use Battlescribe\Data\RootTrait;
use Battlescribe\Data\Traits\HasCostTrait;
use Battlescribe\Data\TreeInterface;
use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;
use Closure;

/** One-to-one mapping with the XML */
class Roster implements RosterInterface, TreeInterface, IdentifierInterface
{
    use RootTrait;

    use HasCostTrait;

    private const NAME = 'roster';

    /** @var ForceInterface[] */
    private array $forces;

    private Identifier $id;
    private string $name;
    private string $battleScribeVersion;
    private Identifier $gameSystemId;
    private string $gameSystemName;
    private int $gameSystemRevision;

    public function __construct(
        Identifier $id,
        string $name,
        string $battleScribeVersion,
        Identifier $gameSystemId,
        string $gameSystemName,
        int $gameSystemRevision,
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->battleScribeVersion = $battleScribeVersion;
        $this->gameSystemId = $gameSystemId;
        $this->gameSystemName = $gameSystemName;
        $this->gameSystemRevision = $gameSystemRevision;

        $this->costs = [];
        $this->forces = [];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBattleScribeVersion(): string
    {
        return $this->battleScribeVersion;
    }

    public function getGameSystemId(): Identifier
    {
        return $this->gameSystemId;
    }

    public function getGameSystemName(): string
    {
        return $this->gameSystemName;
    }

    public function getGameSystemRevision(): int
    {
        return $this->gameSystemRevision;
    }

    public function addForce(ForceInterface $force): void
    {
        $this->forces[] = $force;
    }

    /** @return Force[] */
    public function getForces(): array
    {
        return $this->forces;
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getChildren(): array
    {
        return $this->forces;
    }

    public function getParent(): ?TreeInterface
    {
        return null;
    }

    public function getRoot(): TreeInterface
    {
        return $this;
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
            $element->getAttribute('id')->asIdentifier(),
            $element->getAttribute('name')->asString(),
            $element->getAttribute('battleScribeVersion')->asString(),
            $element->getAttribute('gameSystemId')->asIdentifier(),
            $element->getAttribute('gameSystemName')->asString(),
            $element->getAttribute('gameSystemRevision')->asInt()
        );

        foreach($element->xpath('costs/cost') as $cost) {
            $result->addCost(Cost::fromXml($result, $cost));
        }

        foreach($element->xpath('forces/force') as $force) {
            $result->addForce(Force::fromXml($result, $force));
        }

        return $result;
    }
}
