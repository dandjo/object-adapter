# Object Adapter

Provides adapters for target objects with easy property access via magic getters and setters.

## Installation

    composer require dandjo/object-adapter

## Purpose

Its purpose is to make life easier when working with complex objects behind template engines like Twig. It offers some
syntax sugar like writing camelcase getters (`getSomeProperty`) and accessing the return value via `someProperty` on
the adapter. The same applies to setters. It also offers a generic get method for nested data structures using a dotted
notation. For this to work, each link in the chain must be an instance of ObjectAdapter or an accessible property.

## Usage

Imagine you have an object with some properties, our target object.

```php
$targetObject = new \stdClass();
$targetObject->foo = 'bar';
```

We inherit from the ObjectAdapter and create a getter for the `foo` property on our target object.

```php
class MyAdapter extends ObjectAdapter {
    
    public function getMyFoo() {
        return $this->targetObject->foo;
    }
    
}
```

Now It is possible to access the property `myFoo` on the adapter.

```php
$myAdapter = MyAdapter::create($targetObject);
echo $myAdapter->myFoo;  // 'bar'
echo $targetObject->foo;  // 'bar'
```

There's also an adapter handling JSON. Imagine you have a JSON like this.

```json
{
  "foo": {
    "bar": "baz"
  }  
}
```

We can write some sort of "poor man's deserializer".

```php
class MyJsonAdapter extends JsonObjectAdapter {
    
    public function getFoo() {
        return ObjectAdapter::create($this->targetObject->foo);
    }
    
}
```

Let's get the value of `bar` directly with a dotted path.

```php
$myJsonAdapter = MyJsonAdapter::create($theJsonAbove);
echo $myJsonAdapter->get('foo.bar');  // 'baz'
```

There's also a feature updating targets of JsonObjectAdapters.

```php
$myJsonAdapter->update('{"foo": {"bar": "doe"}}');
echo $myAdapter->get('foo.bar');  // 'doe'
```
