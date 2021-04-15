<?php


namespace Dandjo\ObjectAdapter;


use Dandjo\ObjectAdapter\Reflection\AdapterProperty;
use Iterator;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;


/**
 * Class ObjectAdapter.
 * @package Dandjo\ObjectAdapter
 */
class ObjectAdapter implements ObjectAdapterInterface, Iterator
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
    private $properties = [];

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
     * @return mixed
     * @throws ReflectionException
     */
    public function current()
    {
        $properties = array_keys($this->properties);
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
     * Gets all annotated AdapterProperty objects.
     * @return AdapterProperty[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Get an annotated AdapterProperty.
     * @param $property
     * @return AdapterProperty|null
     */
    public function getProperty($property): ?AdapterProperty
    {
        return $this->properties[$property] ?? null;
    }

    /**
     * Whether a property is annotated.
     * @param $property
     * @return bool
     */
    public function hasProperty($property): bool
    {
        return array_key_exists($property, $this->properties);
    }

    /**
     * Initialize properties with reflection.
     */
    private function initProperties()
    {
        $reflectionCls = new ReflectionClass($this);
        foreach ($reflectionCls->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $doc = $method->getDocComment();
            $matches = [];
            preg_match('/@property\\\(getter|setter)(.*)\n/s', $doc, $matches);
            if (isset($matches[2])) {
                $property = trim($matches[2]);
                if (empty($this->properties[$property])) {
                    $this->properties[$property] = new AdapterProperty();
                }
                if ($matches[1] === 'getter') {
                    $this->properties[$property]->setGetter($method);
                }
                if ($matches[1] === 'setter') {
                    $this->properties[$property]->setSetter($method);
                }
            }
        }
    }

}