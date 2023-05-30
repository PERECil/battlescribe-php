<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;

class Constraint implements ConstraintInterface
{
    use BranchTrait;

    public const NAME = 'constraint';

    private Identifier $id;
    private string $field;
    private string $scope;
    private float $value;
    private bool $percentValue;
    private bool $shared;
    private bool $includeChildSelections;
    private bool $includeChildForces;
    private ConstraintType $type;

    /** @var Condition[] */
    private array $conditions;

    /** @var ConditionGroup[] */
    private array $conditionGroups;

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        string $field,
        string $scope,
        float $value,
        bool $percentValue,
        bool $shared,
        bool $includeChildSelections,
        bool $includeChildForces,
        ConstraintType $type
    )
    {
        $this->parent = $parent;

        $this->id = $id;
        $this->field = $field;
        $this->scope = $scope;
        $this->value = $value;
        $this->percentValue = $percentValue;
        $this->shared = $shared;
        $this->includeChildSelections = $includeChildSelections;
        $this->includeChildForces = $includeChildForces;
        $this->type = $type;

        $this->conditions = [];
        $this->conditionGroups = [];
    }

    /** @inheritDoc */
    public function getChildren(): array
    {
        return array_merge(
            $this->conditions,
            $this->conditionGroups
        );
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function isPercentValue(): bool
    {
        return $this->percentValue;
    }

    public function isShared(): bool
    {
        return $this->shared;
    }

    public function includeChildSelections(): bool
    {
        return $this->includeChildSelections;
    }

    public function includeChildForces(): bool
    {
        return $this->includeChildForces;
    }

    public function getType(): ConstraintType
    {
        return $this->type;
    }

    /**
     * @return Condition[]
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function addCondition(Condition $condition): void
    {
        $this->conditions[] = $condition;
    }

    /**
     * @return ConditionGroup[]
     */
    public function getConditionGroups(): array
    {
        return $this->conditionGroups;
    }

    public function addConditionGroup(ConditionGroup $conditionGroup): void
    {
        $this->conditionGroups[] = $conditionGroup;
    }

    public static function fromXml(?TreeInterface $parent, ?SimpleXMLElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        if($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException( 'Received a '.$element->getName().', expected '.self::NAME);
        }

        $result = new self(
            $parent,
            $element->getAttribute('id')->asIdentifier(),
            $element->getAttribute('field')->asString(),
            $element->getAttribute('scope')->asString(),
            $element->getAttribute('value')->asFloat(),
            $element->getAttribute('percentValue')->asBoolean(),
            $element->getAttribute('shared')->asBoolean(),
            $element->getAttribute('includeChildSelections')->asBoolean(),
            $element->getAttribute('includeChildForces')->asBoolean(),
            $element->getAttribute('type')->asEnum(ConstraintType::class)
        );

        foreach($element->xpath('conditions/condition') as $condition) {
            $result->addCondition(Condition::fromXml($result, $condition));
        }

        foreach($element->xpath('conditionGroups/conditionGroup') as $conditionGroup) {
            $result->addConditionGroup(ConditionGroup::fromXml($result, $conditionGroup));
        }

        return $result;
    }
}
