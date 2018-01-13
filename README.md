## Annotations for PHP

PHP does not have any kind of native annotation (AKA attributes from .NET world) so if you'd like to implement your own annotation framework think of using this first and save some time.

### Usage

#### Create a new Annotation

First you have to create a new class. In this example the class will be called **MyCustomAnnotation**

```
class MyCustomAnnotation
{

}
```

Then you'll have to extend the **Annotation** class from the library

```
use PHPAnnotations\Annotations\Annotation;

class MyCustomAnnotation extends Annotation
{

}
```

Add some logic to it
```
use PHPAnnotations\Annotations\Annotation;

class MyCustomAnnotation extends Annotation
{
    private $name;
    private $surname;
    
    public function __constructor($name, $surname)
    {
        $this->name = $name;
        $this->surname = $surname;
    }
    
    public function GetFullName()
    {
        return "$this->name $this->surname";
    }
}
```

Now our beautiful annotation is ready to go!

#### Use the annotation

Create a class to used to test the annotation
```
class MyTestClass
{

}
```

And add the annotation through the docs

```
/**
 * [MyCustom(name = "Thomas", surname = "Cocchiara")]
 **/
class MyTestClass
{
   
}
```

Now we're ready to test it out!

```
use use PHPAnnotations\Reflection\Reflector;

$myObject = new MyTestClass();
$reflector = new Reflector($myObject);

echo $reflector->getClass()->getAnnotation("MyCustom")->GetFullName();

```

Hope you guys find this library useful.

Please share it and give me a feedback :)

Thomas