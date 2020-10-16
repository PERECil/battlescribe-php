<?php

declare(strict_types=1);

namespace Battlescribe;

use SimpleXMLElement;

class Condition
{
    private const NAME = 'condition';

    private string $field;
    private string $scope;
    private float $value;
    private bool $percentValue;
    private bool $shared;
    private bool $includeChildSelections;
    private bool $includeChildForces;
    private string $childId;
    private ConditionType $conditionType;

    public function __construct(
        string $field,
        string $scope,
        float $value,
        bool $percentValue,
        bool $shared,
        bool $includeChildSelections,
        bool $includeChildForces,
        string $childId,
        ConditionType $conditionType
    )
    {
        $this->field = $field;
        $this->scope = $scope;
        $this->value = $value;
        $this->percentValue = $percentValue;
        $this->shared = $shared;
        $this->includeChildSelections = $includeChildSelections;
        $this->includeChildForces = $includeChildForces;
        $this->childId = $childId;
        $this->conditionType = $conditionType;
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

    public function isIncludeChildSelections(): bool
    {
        return $this->includeChildSelections;
    }

    public function isIncludeChildForces(): bool
    {
        return $this->includeChildForces;
    }

    public function getChildId(): string
    {
        return $this->childId;
    }

    public function getConditionType(): ConditionType
    {
        return $this->conditionType;
    }

    public static function fromXml(?SimpleXMLElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        if($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException( 'Received a '.$element->getName().', expected '.self::NAME);
        }

        return new self(
            $element->getAttribute('field')->asString(),
            $element->getAttribute('scope')->asString(),
            $element->getAttribute('value')->asFloat(),
            $element->getAttribute('percentValue')->asBoolean(),
            $element->getAttribute('shared')->asBoolean(),
            $element->getAttribute('includeChildSelections')->asBoolean(),
            $element->getAttribute('includeChildForces')->asBoolean(),
            $element->getAttribute('childId')->asString(),
            $element->getAttribute('type')->asEnum(ConditionType::class),
        );
    }
}
