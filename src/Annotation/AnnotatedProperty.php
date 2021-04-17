<?php


namespace Dandjo\ObjectAdapter\Annotation;


use ReflectionMethod;


/**
 * Class AnnotatedProperty.
 * @package Dandjo\ObjectAdapter\Reflection
 */
class AnnotatedProperty
{

    /**
     * @var ReflectionMethod|null
     */
    private $getter = null;

    /**
     * @var ReflectionMethod|null
     */
    private $setter = null;

    /**
     * @return ReflectionMethod|null
     */
    public function getGetter(): ?ReflectionMethod
    {
        return $this->getter;
    }

    /**
     * @param ReflectionMethod $getter
     */
    public function setGetter(ReflectionMethod $getter): void
    {
        $this->getter = $getter;
    }

    /**
     * @return bool
     */
    public function hasGetter(): bool
    {
        return $this->getter !== null;
    }

    /**
     * @return ReflectionMethod|null
     */
    public function getSetter(): ?ReflectionMethod
    {
        return $this->setter;
    }

    /**
     * @param ReflectionMethod $setter
     */
    public function setSetter(ReflectionMethod $setter): void
    {
        $this->setter = $setter;
    }

    /**
     * @return bool
     */
    public function hasSetter(): bool
    {
        return $this->setter !== null;
    }

}