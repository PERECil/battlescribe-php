<?php

declare(strict_types=1);


namespace Battlescribe\Roster;

use Battlescribe\Data\Cost;
use Battlescribe\Data\GameSystem;
use Battlescribe\Data\Identifier;
use Battlescribe\Data\IdentifierInterface;
use Battlescribe\Data\RootTrait;
use Battlescribe\Data\Traits\HasCostTrait;
use Battlescribe\Data\TreeInterface;

/** In memory version of the roster */
class RosterInstance implements RosterInterface, TreeInterface, IdentifierInterface
{
    use RootTrait;

    use HasCostTrait;

    private GameSystem $gameSystem;
    private string $name;

    /** @var ForceInstance[] */
    private array $forces;

    /** @var ErrorInterface[] */
    private array $errors;

    private int $selectedCount;
    private ?int $minimumSelectedCount;
    private ?int $maximumSelectedCount;

    public function __construct(GameSystem $gameSystem, string $name)
    {
        $this->gameSystem = $gameSystem;
        $this->name = $name;

        $this->forces = [];
        $this->costs = [];
    }

    public static function fromGameSystem(GameSystem $gameSystem, string $name): self
    {
        return new self($gameSystem, $name);
    }

    public static function fromRosterInterface(GameSystem $gameSystem, RosterInterface $rosterInterface): self
    {
        $result = new self($gameSystem, $rosterInterface->getName());

        foreach($rosterInterface->getForces() as $force) {
            $result->addForce($force);
        }

        foreach($rosterInterface->getCosts() as $cost) {
            $result->addCost(clone $cost);
        }

        return $result;
    }

    public function getGameSystem(): ?GameSystem
    {
        return $this->gameSystem;
    }

    public function addCost(Cost $cost): void
    {
        $this->costs[] = clone $cost;
    }

    public function addForce(ForceInterface $force): void
    {
        $result = new ForceInstance($this, $force);

        foreach($force->getSelections() as $selection) {
            $result->addSelection($selection);
        }

        $this->forces[] = $result;
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return array_merge(
            $this->forces,
            $this->costs
        );
    }

    public function addError(ErrorInterface $error): void
    {
        $this->errors[] = $error;
    }

    /** @return ErrorInterface[] */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBattleScribeVersion(): string
    {
        return $this->gameSystem->getBattleScribeVersion();
    }

    public function getGameSystemId(): Identifier
    {
        return $this->gameSystem->getId();
    }

    public function getGameSystemName(): string
    {
        return $this->gameSystem->getName();
    }

    public function getGameSystemRevision(): int
    {
        return $this->gameSystem->getRevision();
    }

    public function getForces(): array
    {
        return $this->forces;
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getSharedId(): string
    {
        return '0000-0000-0000-0000';
    }

    public function getSelections(): array
    {
        $result = [];

        foreach( $this->forces as $force) {
            $result = array_merge($result, $force->getSelections());
        }

        return $result;
    }
}
