<?php

declare(strict_types=1);

namespace Battlescribe\Roster;

use Battlescribe\Data\Cost;
use Battlescribe\Data\Identifier;

interface RosterInterface
{
    public function getName(): string;
    public function getBattleScribeVersion(): string;
    public function getGameSystemId(): Identifier;
    public function getGameSystemName(): string;
    public function getGameSystemRevision(): int;



    /** @return ForceInterface[] */
    public function getForces(): array;

    public function addForce(ForceInterface $force): void;
}
