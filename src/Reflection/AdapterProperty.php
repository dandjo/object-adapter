<?php


namespace Dandjo\ObjectAdapter\Reflection;


use ReflectionMethod;


/**
 * Class AdapterProperty.
 * @package Dandjo\ObjectAdapter\Reflection
 */
class AdapterProperty
{

    /**
     * @var ReflectionMethod
     */
    protected $getter;

    /**
     * @var ReflectionMethod
     */
    protected $setter;

    /**
     * @return ReflectionMethod
     */
    public function getGetter(): ReflectionMethod
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
     * @return ReflectionMethod
     */
    public function getSetter(): ReflectionMethod
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

}