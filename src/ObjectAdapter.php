<?php


namespace Dandjo\ObjectAdapter;

use Dandjo\ObjectAdapter\Annotation\PropertyAnnotationTrait;
use Iterator;
use ReflectionException;

/**
 * Class ObjectAdapter.
 * @package Dandjo\ObjectAdapter
 */
class ObjectAdapter implements Iterator, ObjectAdapterInterface
{

    use PropertyAnnotationTrait;

    /**
     * @var object
     */
    public $targetObject;

    /**
     * ObjectAdapter constructor.
     *
     * @param object $object
     */
    public function __construct(object $object)
    {
        assert(is_object($object));
        $this->targetObject = $object;
        $this->initProperties();
    }

    /**
     * @param string $property
     *
     * @return mixed|NullAdapter
     * @throws ReflectionException
     */
    public function __get(string $property)
    {
        $adapterProperty = $this->getProperty($property);
        if ($adapterProperty && $adapterProperty->hasGetter()) {
            return $adapterProperty->getGetter()->invoke($this);
        }
        return $this->targetObject->{$property} ?? new NullAdapter();
    }

    /**
     * @param string $property
     * @param mixed  $value
     *
     * @return $this
     * @throws ReflectionException
     */
    public function __set(string $property, $value)
    {
        $adapterProperty = $this->getProperty($property);
        if ($adapterProperty && $adapterProperty->hasSetter()) {
            $adapterProperty->getSetter()->invoke($this, $value);
            return $this;
        }
        $this->targetObject->{$property} = $value;
        return $this;
    }

    /**
     * @param string $property
     *
     * @return bool
     */
    public function __isset(string $property): bool
    {
        return $this->hasProperty($property) || property_exists($this->targetObject, $property);
    }

    /**
     * @param string $property
     */
    public function __unset(string $property)
    {
        if ($this->hasProperty($property)) {
            unset($this->properties[$property]);
        } else {
            unset($this->targetObject->{$property});
        }
    }

}