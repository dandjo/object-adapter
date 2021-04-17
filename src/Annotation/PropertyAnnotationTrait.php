<?php


namespace Dandjo\ObjectAdapter\Annotation;


use ReflectionClass;
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
     * @param $property
     * @return AnnotatedProperty|null
     */
    public function getProperty($property): ?AnnotatedProperty
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

}