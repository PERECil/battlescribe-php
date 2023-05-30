<?php

declare(strict_types=1);

namespace Battlescribe\Roster;

use Battlescribe\Data\BranchTrait;
use Battlescribe\Data\CategoryEntryInterface;
use Battlescribe\Data\Cost;
use Battlescribe\Data\Identifier;
use Battlescribe\Data\IdentifierInterface;
use Battlescribe\Data\Profile;
use Battlescribe\Data\Rule;
use Battlescribe\Data\SelectionEntryType;
use Battlescribe\Data\Traits\HasCostTrait;
use Battlescribe\Data\TreeInterface;
use Battlescribe\Utils\SimpleXmlElementFacade;
use Battlescribe\Utils\UnexpectedNodeException;
use Closure;

class Selection implements IdentifierInterface, TreeInterface, SelectionInterface
{
    use BranchTrait;

    use HasCostTrait;

    private const NAME = 'selection';

    private Identifier $id;
    private string $name;
    private string $entryId;
    private int $number;
    private SelectionEntryType $type;

    /** @var Category[] */
    private array $categories;

    /** @var Rule[] */
    private array $rules;

    /** @var Profile[] */
    private array $profiles;

    /** @var Selection[] */
    private array $selections;

    public function __construct(
        ?TreeInterface $parent,
        Identifier $id,
        string $name,
        string $entryId,
        int $number,
        SelectionEntryType $type,
    )
    {
        $this->parent = $parent;

        $this->id = $id;
        $this->name = $name;
        $this->entryId = $entryId;
        $this->number = $number;
        $this->type = $type;

        $this->costs = [];
        $this->categories = [];
        $this->rules = [];
        $this->profiles = [];
        $this->selections = [];
    }

    public function getChildren(): array
    {
        return array_merge(
            $this->costs,
            $this->categories,
            $this->rules,
            $this->profiles,
            $this->selections
        );
    }

    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEntryId(): string
    {
        return $this->entryId;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getType(): SelectionEntryType
    {
        return $this->type;
    }

    public function addCategory(Category $category): void
    {
        $this->categories[] = $category;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function addRule(Rule $rule): void
    {
        $this->rules[] = $rule;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function addProfile(Profile $profile): void
    {
        $this->profiles[] = $profile;
    }

    public function getProfiles(): array
    {
        return $this->profiles;
    }

    public function addSelection(Selection $selection): void
    {
        $this->selections[] = $selection;
    }

    public function hasSelections(): bool
    {
        return !empty($this->selections);
    }

    public function getSelections(): array
    {
        return $this->selections;
    }

    public static function fromXml(?TreeInterface $parent, ?SimpleXmlElementFacade $element): ?self
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
            $element->getAttribute('name')->asString(),
            $element->getAttribute('entryId')->asString(),
            $element->getAttribute('number')->asInt(),
            new SelectionEntryType($element->getAttribute('type')->asString()),
        );

        foreach($element->xpath('costs/cost') as $cost) {
            $result->addCost(Cost::fromXml($result, $cost));
        }

        foreach($element->xpath('categories/category') as $category) {
            $result->addCategory(Category::fromXml($result, $category));
        }

        foreach($element->xpath('rules/rule') as $rule) {
            $result->addRule(Rule::fromXml($result, $rule));
        }

        foreach($element->xpath('profiles/profile') as $profile) {
            $result->addProfile(Profile::fromXml($result, $profile));
        }

        foreach($element->xpath('selections/selection') as $selection) {
            $result->addSelection(Selection::fromXml($result, $selection));
        }

        return $result;
    }

    public function hasCategory(Category $value): bool
    {
        foreach($this->getCategories() as $category) {
            if($category->getEntryId()->equals($value->getEntryId())) {
                return true;
            }
        }

        return false;
    }
}
