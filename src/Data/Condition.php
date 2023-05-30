<?php

declare(strict_types=1);

namespace Battlescribe\Data;

use Battlescribe\Query\Matcher;
use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;
use UnexpectedValueException;

class Condition implements TreeInterface
{
    use LeafTrait;

    private const NAME = 'condition';

    private string $field;
    private string $scope;
    private float $value;
    private bool $percentValue;
    private bool $shared;
    private bool $includeChildSelections;
    private bool $includeChildForces;
    private Identifier $childId;
    private ConditionType $conditionType;

    public function __construct(
        ?TreeInterface $parent,
        string $field,
        string $scope,
        float $value,
        bool $percentValue,
        bool $shared,
        bool $includeChildSelections,
        bool $includeChildForces,
        Identifier $childId,
        ConditionType $conditionType
    )
    {
        $this->parent = $parent;

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

            // Don't ask me why we look for categories, I don't know.
            $selectionEntries = $entry->getRoot()->findByMatcher(function(TreeInterface $selectionEntry) {

                if($selectionEntry instanceof SelectionEntryInterface) {
                    foreach($selectionEntry->getCategoryEntries() as $categoryEntry) {
                        if($categoryEntry->getSharedId()->equals($this->childId)) {
                            return $selectionEntry->getSelectedCount() > 0;
                        }
                    }
                }

                return false;
            });

            $count = count($selectionEntries);

            switch($this->conditionType) {
                case ConditionType::EQUAL_TO:
                    return $count == $this->value;
                case ConditionType::AT_LEAST:
                    return $count >= $this->value;
                case ConditionType::INSTANCE_OF:
                    echo 'Unhandled case on condition INSTANCE_OF'; break;
                case ConditionType::NOT_INSTANCE_OF:
                    echo 'Unhandled case on condition NOT_INSTANCE_OF'; break;
                default:
                    throw new UnexpectedValueException( "Unhandled condition type ".$this->conditionType->getValue());
            }
        }

        // Go back in the tree, looking for the child id (in categories it seems?)
        if($this->field === "selections" && $this->scope === "ancestor") {

            // Tested - Guardian defender without specialist should remove combat
            $found = false;

            // TODO Should we use the selections entries, and filter those that are ancestors to this entry ?
            $categorizableEntries = self::getCategorizableEntries($entry);
            // $selectedElements = $this->getRoot()->getSelections();

            foreach($categorizableEntries as $e) {
                foreach($e->getCategoryLinks() as $categoryLink) {
                    if($categoryLink->getTargetId()->equals($this->childId)) {
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
        //                 "if this shared item is linked to X" in conjunction with "direct parent".
        // https://www.reddit.com/r/BattleScribe/comments/3t8bdg/direct_parent_not_working/cxi0ykw?context=3
        if($this->field === "selections" && $this->scope === "parent") {

            $count = 0;

            if($entry->getParent()->getId()->equals($this->childId)) {
                $count++;
            }

            switch($this->conditionType) {
                case ConditionType::INSTANCE_OF: return $count === 1;
                case ConditionType::NOT_INSTANCE_OF: return $count === 0;
                case ConditionType::EQUAL_TO: return $count == $this->value;
                case ConditionType::AT_LEAST: return $count >= $this->value;
                case ConditionType::GREATER_THAN: return $count < $this->value;
                default:
                    throw new UnexpectedValueException( "Unhandled condition type ".$this->conditionType->getValue());
            }
        }

        // Find an instance of category that is primary by its field
        if($this->field === "selections" && $this->scope === "primary-category" && ConditionType::INSTANCE_OF()->equals($this->conditionType)) {

            // Tested - Guardian specialist should remove leader from specialism
            // Tested - Guardian leader removes anything else but leader from specialism

            /*
            $test = $entry->getRoot()->findByMatcher(Matcher::isPrimaryCategory());

            // TODO Replace this with category entries shared id
            foreach($test as $category) {
                if($category->getSharedId()->equals($this->childId)) {
                    return true;
                }
            }

            return false;
            */

            $selection = self::getRootSelectionFrom($entry);

            foreach ($selection->getCategoryLinks() as $categoryLink) {
                if ($categoryLink->isPrimary() && $categoryLink->getTargetId()->equals($this->childId)) {
                    return true;
                }
            }

            return false;
        }



        // Find an instance of category that is primary by its field
        if($this->field === "selections" && $this->scope === "primary-category" && ConditionType::NOT_INSTANCE_OF()->equals($this->conditionType)) {

            /*
            $test = $entry->getRoot()->findByMatcher(Matcher::isPrimaryCategory());

            // TODO Replace this with category entries shared id
            foreach($test as $category) {
                if($category->getSharedId()->equals($this->childId)) {
                    return false;
                }
            }

            return true;
            */

            $selection = self::getRootSelectionFrom($entry);

            foreach ($selection->getCategoryLinks() as $categoryLink) {
                if ($categoryLink->isPrimary() && $categoryLink->getTargetId()->equals($this->childId)) {
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
                case ConditionType::LESS_THAN: return $selections > $this->value;
                case ConditionType::GREATER_THAN: return $selections < $this->value;
                default:
                    throw new UnexpectedValueException( "Unhandled condition type ".$this->conditionType->getValue());
            }
        }

        if($this->field === "forces" && $this->scope === "roster" ) {

            $selections = count($entry->getRoot()->getForces());

            switch($this->conditionType) {
                case ConditionType::EQUAL_TO: return $selections == $this->value;
                case ConditionType::AT_LEAST: return $selections >= $this->value;
                case ConditionType::GREATER_THAN: return $selections < $this->value;
                case ConditionType::LESS_THAN: return $selections > $this->value;
                default:
                    throw new UnexpectedValueException( "Unhandled condition type ".$this->conditionType->getValue());
            }
        }

        throw new UnexpectedValueException( "Unhandled condition state on field ".$this->field." with scope ".$this->scope." for child ".$this->childId );
    }

    /**
     * @param SelectionEntryInterface[] $entry
     * @param string $childId
     * @return int
     */
    public static function countSelections(array $entries, Identifier $childId): int
    {
        $selections = 0;

        foreach($entries as $entry) {

            // Locate the child id in the scope
            $elements = $entry->findByMatcher(function(TreeInterface $element) use($childId): bool {
                return ($element instanceof SelectionEntryInterface) && $element->getSharedId()->equals($childId);
            });

            foreach($elements as $element) {
                $selections += $entry->getSelectedCount();
            }
        }

        /*
        if($entry->getSelectedEntryId() === $id) {
            $selections += $entry->getSelectedCount();
        }

        foreach($entry->getChildren() as $child) {
            $selections += self::countSelections($child, $id);
        }
        */

        return $selections;
    }

    /**
     * @param ModifiableInterface $entry
     * @param string $scope
     * @return SelectionEntryInterface[]
     */
    public static function findFrom(ModifiableInterface $entry, string $scope): array
    {
        if(!preg_match( '%[a-f0-9-]+%', $scope)) {
            throw new UnexpectedValueException( 'Scope "'.$scope.'" is not an identifier');
        }

        $scope = new Identifier($scope);

        return $entry->getRoot()->findByMatcher( function(TreeInterface $element) use($scope): bool {
            return ($element instanceof SelectionEntryInterface) && $element->getSharedId()->equals($scope);
        });

        /*
        do {
            $entry = $entry->getParent();
        } while($entry !== null && ($entry->getSharedId() !== $scope));

        return $entry;
        */

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

    /** @return CategorizableInterface[] */
    public static function getCategorizableEntries(TreeInterface $entry): array
    {
        $results = [];

        do {
            if($entry instanceof SelectionEntryInterface) {
            // if($entry instanceof CategorizableInterface) {
                $results[] = $entry;
            }

            $entry = $entry->getParent();
        } while($entry !== null);

        return $results;
    }

    public static function getRootSelectionFrom(TreeInterface $entry): SelectionEntryInterface
    {
        // The expected structure is:
        // - First the roster instance
        // - then the force instance
        // - then a series of selection
        // So if the parent's parent's parent's is null, that means we are currently at the root selection,
        // i.e. the selection directly under a force instance

        while($entry->getParent()?->getParent()?->getParent() !== null) {
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

    public function getChildId(): Identifier
    {
        return $this->childId;
    }

    public function getConditionType(): ConditionType
    {
        return $this->conditionType;
    }

    public static function fromXml(?TreeInterface $parent, ?SimpleXMLElementFacade $element): ?self
    {
        if($element === null) {
            return null;
        }

        if($element->getName() !== self::NAME) {
            throw new UnexpectedNodeException( 'Received a '.$element->getName().', expected '.self::NAME);
        }

        return new self(
            $parent,
            $element->getAttribute('field')->asString(),
            $element->getAttribute('scope')->asString(),
            $element->getAttribute('value')->asFloat(),
            $element->getAttribute('percentValue')->asBoolean(),
            $element->getAttribute('shared')->asBoolean(),
            $element->getAttribute('includeChildSelections')->asBoolean(),
            $element->getAttribute('includeChildForces')->asBoolean(),
            $element->getAttribute('childId')->asIdentifier(),
            $element->getAttribute('type')->asEnum(ConditionType::class),
        );
    }
}
