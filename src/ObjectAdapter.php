<?php


namespace Dandjo\ObjectAdapter;


use Dandjo\ObjectAdapter\Annotation\PropertyAnnotationTrait;
use Iterator;
use ReflectionException;


/**
 * Class ObjectAdapter.
 * @package Dandjo\ObjectAdapter
 */
class ObjectAdapter implements ObjectAdapterInterface, Iterator
{

    use PropertyAnnotationTrait;

    /**
     * @var object
     */
    public $targetObject;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * ObjectAdapter constructor.
     * @param $object
     */
    public function __construct($object)
    {
        assert(is_object($object));
        $this->targetObject = $object;
        $this->initProperties();
    }

    /**
     * @param $dottedPath
     * @return mixed
     * @throws ReflectionException
     */
    public function get($dottedPath)
    {
        $properties = preg_split('/\./', $dottedPath);
        $property = array_shift($properties);
        if (empty($property)) {
            return new NullAdapter();
        }
        $propertyValue = $this->{$property};
        if (is_a($propertyValue, self::class)) {
            return $propertyValue->get(implode('.', $properties));
        }
        if (count($properties) > 0) {
            return new NullAdapter();
        }
        return $propertyValue;
    }

    /**
     * @param $property
     * @return mixed|NullAdapter
     * @throws ReflectionException
     */
    public function __get($property)
    {
        if (empty($property)) {
            return new NullAdapter();
        }
        $adapterProperty = $this->getProperty($property);
        if ($adapterProperty && $adapterProperty->hasGetter()) {
            return $adapterProperty->getGetter()->invoke($this);
        }
        return $this->targetObject->{$property} ?? new NullAdapter();
    }

    /**
     * @param $property
     * @param $value
     * @return $this
     * @throws ReflectionException
     */
    public function __set($property, $value): ObjectAdapter
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
     * @param $property
     * @return bool
     */
    public function __isset($property): bool
    {
        return $this->hasProperty($property) || property_exists($this->targetObject, $property);
    }

    /**
     * @param $property
     */
    public function __unset($property)
    {
        if ($this->hasProperty($property)) {
            unset($this->properties[$property]);
        } else {
            unset($this->targetObject->{$property});
        }
    }

    /**
     * Whether an offset exists.
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->__isset($offset);
    }

    /**
     * Offset to retrieve.
     * @param $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    /**
     * Offset to set.
     * @param $offset
     * @param $value
     * @throws ReflectionException
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * Offset to unset.
     * @param $offset
     */
    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

    /**
     * Return the current element.
     * @return mixed
     */
    public function current()
    {
        $properties = array_keys($this->properties);
        $property = $properties[$this->position];
        return $this->{$property};
    }

    /**
     * Move forward to next element.
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Return the key of the current element.
     * @return string
     */
    public function key()
    {
        $properties = array_keys($this->properties);
        return $properties[$this->position];
    }

    /**
     * Checks if current position is valid.
     * @return bool
     */
    public function valid()
    {
        $properties = array_keys($this->properties);
        return isset($properties[$this->position]);
    }

    /**
     * Rewind the Iterator to the first element.
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Specify properties to be serialized.
     */
    public function jsonProperties(): array
    {
        return [];
    }

    /**
     * Specify data which should be serialized to JSON.
     */
    public function jsonSerialize()
    {
        $json = [];
        $properties = $this->jsonProperties();
        foreach ($properties as $property) {
            $json[$property] = $this->{$property};
        }
        return $json;
    }

}