<?php

declare(strict_types=1);

namespace Battlescribe;

use MyCLabs\Enum\Enum;
use SimpleXMLElement;
use UnexpectedValueException;

class SimpleXmlElementFacade
{
    private ?SimpleXMLElement $element;

    public function __construct(?SimpleXMLElement $element)
    {
        $this->element = $element;
    }

    public function __get(string $name): ?SimpleXmlElementFacade
    {
        $subElement = $this->element->$name;

        if($subElement === null) {
            return null;
        }

        return new self($subElement);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return false|mixed
     */
    public function __call( string $name , array $arguments )
    {
        return call_user_func_array( [ $this->element, $name ], $arguments );
    }

    public function getName(): string
    {
        return $this->element->getName();
    }

    public function getAttribute(string $attributeName): ?SimpleXmlElementFacade
    {
        return new self($this->element->attributes()->{$attributeName});
    }

    public function children( ?string $ns = null, ?bool $isPrefix = FALSE) : SimpleXmlElementFacade
    {
        return new self($this->element->children($ns, $isPrefix));
    }

    public function xpath(string $path) : SimpleXmlElementFacadeIterator
    {
        return new SimpleXmlElementFacadeIterator($this->element->xpath($path));
    }

    public function asBoolean(): ?bool
    {
        $value = $this->asString();

        if($value === null) {
            return null;
        }

        $trues = [ 'true', '1', 'on', 'yes' ];
        $falses = [ 'false', '0', 'off', 'no' ];

        if(in_array($value, $trues)) {
            return true;
        }

        if(in_array($value, $falses)) {
            return false;
        }

        throw new UnexpectedValueException( $value.' is not castable to bool' );
    }

    /**
     * @template T of Enum
     * @param string $enumClass
     * @psalm-param class-string<T> $enumClass
     * @psalm-return T
     */
    public function asEnum(string $enumClass)
    {
        $value = $this->asString();

        if($value === null) {
            return null;
        }

        return new $enumClass($value);
    }

    public function asFloat(): ?float
    {
        $value = $this->asString();

        if($value === null) {
            return null;
        }

        if( preg_match( '%-?[0-9]+\.?[0-9]*%', $value) !== 1 ) {
            throw new UnexpectedValueException( $value.' is not castable to float' );
        }

        return floatval($value);
    }

    public function asInt(): ?int
    {
        $value = $this->asString();

        if($value === null) {
            return null;
        }

        if( preg_match( '%-?[0-9]+%', $value) !== 1 ) {
            throw new UnexpectedValueException( $value.' is not castable to int' );
        }

        return intval($value);
    }

    public function asString(): ?string
    {
        if($this->element === null) {
            return null;
        }

        return $this->element->__toString();
    }

    public function __toString(): string
    {
        if($this->element === null) {
            return 'NULL';
        }

        return $this->element->__toString();
    }
}
