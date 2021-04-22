<?php

require_once 'src/Annotation/AnnotatedProperty.php';
require_once 'src/Annotation/PropertyAnnotationTrait.php';

class PropertyAnnotationTest
{

    use \Dandjo\ObjectAdapter\Annotation\PropertyAnnotationTrait;

    public function __construct()
    {
        $this->initProperties();
    }

    public $secondProperty = 'second_one';

    /**
     * @property\getter firstProperty
     */
    public function getFirstProperty()
    {
        return 'first_property';
    }

}

$myPropertyAnnotation = new PropertyAnnotationTest();

var_dump([
    $myPropertyAnnotation->firstProperty === 'first_property',
    $myPropertyAnnotation->secondProperty === 'second_one',
    isset($myPropertyAnnotation->firstProperty) === true,
    isset($myPropertyAnnotation->secondProperty) === true,
    $myPropertyAnnotation->invalidProperty === NULL,
]);