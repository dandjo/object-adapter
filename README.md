# Object Adapter

Provides some base classes for adapting target objects with annotated property access.

## Installation

    composer require dandjo/object-adapter

## Purpose

Its purpose is to make life easier when working with complex objects behind template engines like Twig. It offers some
syntax sugar like writing methods with annotations and accessing the return value via the annotated property name on
the adapter. The same applies to setters. It also offers a generic get method for nested data structures using a dotted
notation. For this to work, each link in the chain must be an instance of ObjectAdapter or an accessible property.

## Usage

To provide a property for your adapter, you have to annotate an appropriate method with either
`@property\getter property_name` or `@property\setter property_name`. A setter method must take a parameter with the
value the property should be set to. The name of the method can be chosen arbitrarily. However, the annotation is
important. If there is no getter or setter, the adapter acts transparently on its target object.

## Examples

Imagine you have an object with some properties, our target object. When using the ObjectAdapter base class directly,
all properties are transparent by default.

```php
$targetObject = new \stdClass();
$targetObject->foo = 'bar';

$myAdapter = new \Dandjo\ObjectAdapter\ObjectAdapter($targetObject);
echo $myAdapter->foo;  // 'bar'
```

We inherit from the ObjectAdapter and create a getter for the `foo` property on our target object. With the annotation,
the adapter offers a property called `myFoo`.

```php
class MyAdapter extends \Dandjo\ObjectAdapter\ObjectAdapter {
    
    /**
     * @property\getter myFoo
     */
    public function getMyFoo(): string
    {
        return $this->targetObject->foo;
    }
    
}

$targetObject = new \stdClass();
$targetObject->foo = 'bar';

$myAdapter = new MyAdapter($targetObject);
echo $myAdapter->myFoo;  // 'bar'
echo $targetObject->foo;  // 'bar'
```

Here's an example with a setter.

```php
class MyAdapter extends \Dandjo\ObjectAdapter\ObjectAdapter {
    
    /**
     * @property\setter myFoo
     */
    public function setMyFoo($value): \Dandjo\ObjectAdapter\ObjectAdapter
    {
        $this->targetObject->foo = $value;
    }
    
}

$targetObject = new \stdClass();
$targetObject->foo = 'bar';

$myAdapter = new MyAdapter($targetObject);
echo $myAdapter->myFoo;  // 'bar'
$myAdapter->myFoo = 'baz';
echo $myAdapter->myFoo;  // 'baz'
echo $targetObject->foo;  // 'baz'
```

If you want to use deep object hierarchies, have a look at the following example. Chained calls are safe since every
non-existent property will return a `NullAdapter`.

```php
class MyChildAdapter extends \Dandjo\ObjectAdapter\ObjectAdapter {

    /**
     * @property\getter myFoo
     */
    public function getMyFoo(): string
    {
        return $this->targetObject->foo;
    }

}

class MyAdapter extends \Dandjo\ObjectAdapter\ObjectAdapter {
    
    /**
     * @property\getter myChild
     */
    public function getMyChild(): string
    {   
        return new MyChildAdapter($this->targetObject->child);
    }
    
}

$targetObject = new \stdClass();
$targetObject->child = new \stdClass();
$targetObject->child->foo = 'bar';

$myAdapter = new MyAdapter($targetObject);
echo $myAdapter->myChild->myFoo;  // 'bar'
```

Each adapter implements the `ArrayAccess` and `Interator` Interface, so you can also access or loop annotated properties
like follows.

```php
class MyAdapter extends \Dandjo\ObjectAdapter\ObjectAdapter {
    
    /**
     * @property\getter myFoo
     */
    public function getMyFoo(): string
    {
        return $this->targetObject->foo;
    }
    
}

$targetObject = new \stdClass();
$targetObject->foo = 'bar';

$myAdapter = new MyAdapter($targetObject);
echo $myAdapter['foo'];  // 'bar'
echo $myAdapter['myFoo'];  // 'bar'

foreach ($myAdapter as $propertyName => $propertyValue) {
    echo "$propertyName: $propertyValue";  // myFoo: bar
}
```
