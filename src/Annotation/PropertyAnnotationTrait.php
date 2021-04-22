<?php


namespace Dandjo\ObjectAdapter\Annotation;


use Dandjo\ObjectAdapter\NullAdapter;
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
     * @param string $property
     * @return mixed|NullAdapter
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
     * @param mixed $value
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
     * Get all annotated AdapterProperty objects.
     * @return AnnotatedProperty[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Get an annotated AdapterProperty.
     * @param string $property
     * @return AnnotatedProperty|null
     */
    public function getProperty(string $property): ?AnnotatedProperty
    {
        return $this->properties[$property] ?? null;
    }

    /**
     * Whether a property is annotated.
     * @param string $property
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
            preg_match('/@property\\\(getter|setter)(.*)\n/s', $doc, $matches);
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