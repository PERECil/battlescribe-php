<?php

declare(strict_types=1);

namespace Battlescribe;

use Illuminate\Support\Facades\Log;
use JsonSerializable;
use SimpleXMLElement;
use UnexpectedValueException;

class Condition implements JsonSerializable
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

    /**
     * @param ModifiableInterface[] $selections
     * @param ModifiableInterface $entry
     * @return bool
     */
    public function isValid(array $selections, ModifiableInterface $entry): bool
    {
        // Count the number of selection having a specific id
        if($this->field === "selections" && $this->scope === "force") {

            $count = 0;

            foreach($selections as $selection) {
                if($selection->getId() === $this->childId) {
                    $count++;
                }
            }

            switch($this->conditionType) {
                case ConditionType::EQUAL_TO:
                    return $count == $this->value;
                case ConditionType::AT_LEAST:
                    return $count >= $this->value;
                default:
                    throw new UnexpectedValueException( "Unhandled condition type ".$this->conditionType->getValue());
            }
        }

        // Go back in the tree, looking for the child id (in categories it seems?)
        if($this->field === "selections" && $this->scope === "ancestor") {

            // Tested - Guardian defender with non specialist should remove combat
            $found = false;

            while(($entry = self::getClosestSelectionFrom($entry)) !== null) {

                foreach($entry->getCategoryLinks() as $categoryLink) {
                    if($categoryLink->getTargetId() === $this->childId) {
                        $found = true;
                    }
                }

            }

            switch ($this->conditionType) {
                case ConditionType::INSTANCE_OF: return $found;
                case ConditionType::NOT_INSTANCE_OF: return !$found;
                default:
                    throw new UnexpectedValueException( "Unhandled condition type ".$this->conditionType->getValue());
            }
        }

        // From the dev of Battlescribe himself:
        // - "Direct parent" refers to the parent only - not any "antecedent"
        // - "Instance of" is really meant to go on shared items, so that your conditions can say
        //                 "if this shared item is linked to X" (in conjunction with "direct parent".
        // https://www.reddit.com/r/BattleScribe/comments/3t8bdg/direct_parent_not_working/cxi0ykw?context=3
        if($this->field === "selections" && $this->scope === "parent") {

            $count = 0;

            if($entry->getParent()->getId() === $this->childId) {
                $count++;
            }

            switch($this->conditionType) {
                case ConditionType::INSTANCE_OF: return $count === 1;
                case ConditionType::NOT_INSTANCE_OF: return $count === 0;
                case ConditionType::EQUAL_TO: return $count == $this->value;
                case ConditionType::AT_LEAST: return $count >= $this->value;
                default:
                    throw new UnexpectedValueException( "Unhandled condition type ".$this->conditionType->getValue());
            }
        }

        // Find an instance of category that is primary by it's field
        if($this->field === "selections" && $this->scope === "primary-category" && ConditionType::INSTANCE_OF()->equals($this->conditionType)) {

            // Tested - Guardian specialist should remove leader from specialism
            // Tested - Guardian leader removes anything else but leader from specialism
            $selection = self::getSelectionFrom($entry);

            // TODO Replace this with category entries shared id
            foreach($selection->getCategoryLinks() as $categoryLink) {
                if($categoryLink->isPrimary() && $categoryLink->getTargetId() === $this->childId) {
                    return true;
                }
            }

            return false;
        }

        // Find an instance of category that is primary by it's field
        if($this->field === "selections" && $this->scope === "primary-category" && ConditionType::NOT_INSTANCE_OF()->equals($this->conditionType)) {

            $selection = self::getSelectionFrom($entry);

            // TODO Replace this with category entries shared id
            foreach($selection->getCategoryLinks() as $categoryLink) {
                if($categoryLink->isPrimary() && $categoryLink->getTargetId() === $this->childId) {
                    return false;
                }
            }

            return true;
        }

        if($this->field === "selections" && ($scope = self::findFrom($entry, $this->scope)) !== null) {
            $selections = self::countSelections($scope, $this->childId);

            switch($this->conditionType) {
                case ConditionType::INSTANCE_OF: return $selections === 1;
                case ConditionType::NOT_INSTANCE_OF: return $selections === 0;
                case ConditionType::EQUAL_TO: return $selections == $this->value;
                case ConditionType::AT_LEAST: return $selections >= $this->value;
                default:
                    throw new UnexpectedValueException( "Unhandled condition type ".$this->conditionType->getValue());
            }
        }

        throw new UnexpectedValueException( "Unhandled condition state on field ".$this->field." with scope ".$this->scope." for child ".$this->childId );
    }

    public static function countSelections(ModifiableInterface $entry, string $id): int
    {
        $selections = 0;

        if($entry->getSelectedEntryId() === $id) {
            $selections += 1;
        }

        foreach($entry->getChildren() as $child) {
            $selections += self::countSelections($child, $id);
        }

        return $selections;
    }

    public static function findFrom(ModifiableInterface $entry, string $scope): ?SelectionEntryInterface
    {
        do {
            $entry = $entry->getParent();
        } while($entry !== null && ($entry->getSharedId() !== $scope));

        return $entry;

        /*
        if($entry->getId() === $scope) {
            return $entry;
        }

        foreach($entry->getChildren() as $children) {
            if( ($result = self::findFrom($children, $scope)) !== null) {
                return $result;
            }
        }

        return null;
        */
    }

    public static function getClosestSelectionFrom(TreeInterface $entry): ?SelectionEntryInterface
    {
        do {
            $entry = $entry->getParent();
        } while($entry !== null && !($entry instanceof SelectionEntryInterface));

        return $entry;
    }

    public static function getSelectionFrom(TreeInterface $entry): TreeInterface
    {
        // Rewind back the tree until the parent's parent is null (meaning that the parent is the roster)
        while($entry->getParent()->getParent() !== null) {
            $entry = $entry->getParent();
        }

        return $entry;
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

    public function jsonSerialize(): array
    {
        return [
            'field' => $this->field,
            'scope' => $this->scope,
            'value' => $this->value,
            'percent_value' => $this->percentValue,
            'shared' => $this->shared,
            'include_child_selections' => $this->includeChildSelections,
            'include_child_forces' => $this->includeChildForces,
            'child_id' => $this->childId,
            'condition_type' => $this->conditionType,
        ];
    }
}
