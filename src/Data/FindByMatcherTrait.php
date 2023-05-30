<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Closure;

trait FindByMatcherTrait
{
    /**
     * Gets the shallow list of children.
     * @return TreeInterface[]
     */
    public abstract function getChildren(): array;

    /** @inheritDoc */
    public function findByMatcher(Closure $matcher): array
    {
        $result = array_filter($this->getChildren(), $matcher);

        /** @var TreeInterface $child */
        foreach($this->getChildren() as $child) {
            $result = array_merge( $result, $child->findByMatcher($matcher));
        }

        return $result;
    }
}
