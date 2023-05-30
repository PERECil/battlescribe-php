<?php

declare(strict_types=1);


namespace Battlescribe\Data\Traits;

use Battlescribe\Data\Cost;
use Battlescribe\Data\CostInterface;
use Battlescribe\Data\Identifier;
use Exception;

trait HasCostTrait
{
    /** @var CostInterface[] */
    private array $costs;

    /** @return Cost[] */
    public function getCosts(): array
    {
        return $this->costs;
    }

    public function addCost(Cost $cost): void
    {
        foreach($this->costs as $candidate) {
            if($candidate->getName() === $cost->getName()) {
                throw new Exception('A cost for the unit '.$cost->getName().' already exists');
            }
        }

        $this->costs[] = $cost;
    }

    public function findCost(Identifier $id): ?Cost
    {
        foreach($this->costs as $cost) {
            if($cost->getTypeId()->equals($id)) {
                return $cost;
            }
        }

        return null;
    }
}
