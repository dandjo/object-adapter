<?php


namespace Dandjo\ObjectAdapter\Annotation;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Trait PropertyAnnotationTrait.
 * @package Dandjo\ObjectAdapter\Traits
 */
trait PropertyAnnotationTrait
{

    /**
     * @var AnnotatedProperty[]
     */
    private $properties = [];

    /**
     * @var int
     */
    private $propertyPosition = 0;

    /**
     * @var array
     */
    private $jsonProperties = [];

    /**
     * @param string $property
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function __get(string $property)
    {
        $annotatedProperty = $this->getProperty($property);
        if ($annotatedProperty && $annotatedProperty->hasGetter()) {
            return $annotatedProperty->getGetter()->invoke($this);
        }
        return $this->{$property};
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
        $annotatedProperty = $this->getProperty($property);
        if ($annotatedProperty && $annotatedProperty->hasSetter()) {
            $annotatedProperty->getSetter()->invoke($this, $value);
            return $this;
        }
        $this->{$property} = $value;
        return $this;
    }

    /**
     * @param string $property
     *
     * @return mixed
     */
    public function get(string $property)
    {
        return $this->{$property};
    }

    /**
     * @param string $property
     * @param        $value
     *
     * @return $this
     */
    public function set(string $property, $value)
    {
        $this->{$property} = $value;
        return $this;
    }

    /**
     * @param string $property
     *
     * @return bool
     */
    public function __isset(string $property): bool
    {
        return $this->hasProperty($property) || property_exists($this, $property);
    }

    /**
     * @param string $property
     */
    public function __unset(string $property)
    {
        if ($this->hasProperty($property)) {
            unset($this->properties[$property]);
        } else {
            unset($this->{$property});
        }
    }

    /**
     * Executed prior to any serialization. Return the properties that should be serialized.
     *
     * @return array
     */
    public function __sleep()
    {
        return array_diff(array_keys(get_object_vars($this)), ['properties']);
    }

    /**
     * Executed after deserialization. Reconstruct any resources that the object may have.
     */
    public function __wakeup()
    {
        $this->initProperties();
    }

    /**
     * Whether an offset exists.
     *
     * @param $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->__isset($offset);
    }

    /**
     * Offset to retrieve.
     *
     * @param $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    /**
     * Offset to set.
     *
     * @param $offset
     * @param $value
     *
     * @throws ReflectionException
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * Offset to unset.
     *
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
        $property = $properties[$this->propertyPosition];
        return $this->{$property};
    }

    /**
     * Move forward to next element.
     */
    public function next()
    {
        ++$this->propertyPosition;
    }

    /**
     * Return the key of the current element.
     * @return string
     */
    public function key(): string
    {
        $properties = array_keys($this->properties);
        return $properties[$this->propertyPosition];
    }

    /**
     * Checks if current position is valid.
     * @return bool
     */
    public function valid(): bool
    {
        $properties = array_keys($this->properties);
        return isset($properties[$this->propertyPosition]);
    }

    /**
     * Rewind the Iterator to the first element.
     */
    public function rewind()
    {
        $this->propertyPosition = 0;
    }

    /**
     * Specify properties to be serialized.
     */
    public function jsonProperties(): array
    {
        return [];
    }

    /**
     * @param array $properties
     *
     * @return $this
     */
    public function addJsonProperties(array $properties)
    {
        $this->jsonProperties = array_merge($this->jsonProperties, $properties);
        return $this;
    }

    /**
     * @param string $property
     *
     * @return $this
     */
    public function addJsonProperty(string $property)
    {
        $this->jsonProperties[] = $property;
        return $this;
    }

    /**
     * Specify data which should be serialized to JSON.
     * @return array
     */
    public function jsonSerialize(): array
    {
        $properties = array_merge($this->jsonProperties(), $this->jsonProperties);
        return $this->toArray(array_unique($properties));
    }

    /**
     * Generate an array from given properties. Will use all annotated properties if given properties are empty.
     *
     * @param array $properties
     *
     * @return array
     */
    public function toArray(array $properties = []): array
    {
        if (empty($properties)) {
            $properties = array_keys($this->properties);
        }
        $array = [];
        foreach ($properties as $property) {
            $array[$property] = $this->{$property};
        }
        return $array;
    }

    /**
     * Get all annotated AdapterProperty objects.
     * @return AnnotatedProperty[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Get an annotated AdapterProperty.
     *
     * @param string $property
     *
     * @return AnnotatedProperty|null
     */
    public function getProperty(string $property): ?AnnotatedProperty
    {
        return $this->properties[$property] ?? null;
    }

    /**
     * Whether a property is annotated.
     *
     * @param string $property
     *
     * @return bool
     */
    public function hasProperty(string $property): bool
    {
        return array_key_exists($property, $this->properties);
    }

    /**
     * Initialize properties with reflection.
     */
    protected function initProperties()
    {
        $reflectionCls = new ReflectionClass($this);
        foreach ($reflectionCls->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $doc = $method->getDocComment();
            $matches = [];
            preg_match('/@property\\\(getter|setter)(.*?)\n/s', $doc, $matches);
            if (isset($matches[2])) {
                $property = trim($matches[2]);
                if (empty($this->properties[$property])) {
                    $this->properties[$property] = new AnnotatedProperty();
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