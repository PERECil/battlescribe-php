<?php

declare(strict_types=1);

namespace Battlescribe\Roster;

use Battlescribe\Data\ConstraintInterface;

class ConstraintError implements ErrorInterface
{
    private ConstraintInterface $constraint;

    public function __construct(ConstraintInterface $constraint) {
        $this->constraint = $constraint;
    }
}
