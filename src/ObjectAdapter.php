<?php


namespace Dandjo\ObjectAdapter;


use ArrayAccess;
use Dandjo\ObjectAdapter\Reflection\AdapterProperty;
use Iterator;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;


/**
 * Class ObjectAdapter.
 * @package Dandjo\ObjectAdapter
 */
class ObjectAdapter implements ArrayAccess, Iterator
{

    /**
     * @var object
     */
    public $targetObject;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var AdapterProperty[]
     */
    private $properties;

    /**
     * ObjectAdapter constructor.
     * @param $object
     */
    public function __construct($object)
    {
        assert(is_object($object));
        $this->targetObject = $object;
    }

    /**
     * @param $dottedPath
     * @param mixed $default
     * @return mixed
     */
    public function get($dottedPath, $default = null)
    {
        $properties = preg_split('/\./', $dottedPath);
        $property = array_shift($properties);
        if (empty($property)) {
            return new NullAdapter();
        }
        if (is_a($this->{$property}, self::class)) {
            return $this->{$property}->get(implode('.', $properties), $default);
        }
        if (count($properties) > 0) {
            return new NullAdapter();
        }
        return $this->{$property} ?: ($default !== null ? $default : new NullAdapter());
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
        if (($adapterProperty = $this->getProperty($property)) && ($getter = $adapterProperty->getGetter())) {
            return $getter->invoke($this);
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
        if (($adapterProperty = $this->getProperty($property)) && ($setter = $adapterProperty->getSetter())) {
            $setter->invoke($this, $value);
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
        return property_exists($this->targetObject, $property);
    }

    /**
     * @param $property
     */
    public function __unset($property)
    {
        unset($this->targetObject->{$property});
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
     * @throws ReflectionException
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
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
     * @return AdapterProperty|null
     * @throws ReflectionException
     */
    public function current()
    {
        $properties = array_keys($this->getProperties());
        $property = $properties[$this->position];
        return $this->__get($property);
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
        $properties = array_keys($this->getProperties());
        return $properties[$this->position];
    }

    /**
     * Checks if current position is valid.
     * @return bool
     */
    public function valid()
    {
        $properties = array_keys($this->getProperties());
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
     * Get all annotated AdapterProperties.
     * @return AdapterProperty[]
     */
    public function getProperties(): array
    {
        if ($this->properties !== null) {
            return $this->properties;
        }
        $reflectionCls = new ReflectionClass($this);
        $properties = [];
        foreach ($reflectionCls->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $doc = $method->getDocComment();
            $getter = [];
            $setter = [];
            preg_match('/@property\\\getter(.*\s+)(.*)\n/s', $doc, $getter);
            preg_match('/@property\\\setter(.*\s+)(.*)\n/s', $doc, $setter);
            if (isset($getter[2])) {
                if (empty($properties[$getter[2]])) {
                    $properties[$getter[2]] = new AdapterProperty();
                }
                $properties[$getter[2]]->setGetter($method);
            }
            if (isset($setter[2])) {
                if (empty($properties[$setter[2]])) {
                    $properties[$setter[2]] = new AdapterProperty();
                }
                $properties[$setter[2]]->setSetter($method);
            }

        }
        return $this->properties = $properties;
    }

    /**
     * Get an annotated AdapterProperty.
     * @param $property
     * @return AdapterProperty|null
     */
    public function getProperty($property): ?AdapterProperty
    {
        return $this->getProperties()[$property] ?? null;
    }

    /**
     * Whether a property is annotated.
     * @param $property
     * @return bool
     */
    public function hasProperty($property): bool
    {
        return array_key_exists($property, $this->getProperties());
    }

}