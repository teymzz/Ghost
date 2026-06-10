# GhostProxy Utility Class
GhostProxy class is designed for creating dynamic objects with support for IDEs using abstract classes. 
It's advanced feature opens new possibilities within the PHP environment. GhostProxy is remarkably elegant and powerful. 
It's a sophisticated dynamic proxy and handler routing system that unlocks several advanced patterns:

### Key Strengths :
- Dynamic Object Materialization - It creates objects on-the-fly without predefined class definitions, using closures as property handlers. This gives you runtime flexibility similar to dynamic languages.

- Channel-Based Callback Routing - The handler/channel system allows you to:
  - Register callbacks dynamically before object creation
  - Route logic through a persistent channel ID
  - Compose complex behavior without inheritance
  - Lazy Method Evaluation - Methods are evaluated via closures, enabling:

- Computed/derived properties
  - State-dependent values
  - Efficient resource allocation (only calculate what's needed)
  - Object Context Isolation - Each proxy instance is isolated via an object id, preventing cross-contamination between concurrent proxy operations.

- With this pattern, you could implement far more sophisticated features like:
  - Plugin ecosystems with hot-reloading
  - Rules engines
  - Custom DSLs

### Initialization 

Proxy object can be initialized in various ways. However the best is either to use a GhostFunction 
or an array to initialize a class using GhostProxy as the final wrapper. 

##### Initializing with GhostFunction (Not Recommended) 
This approach does not support IDEs. Hence it is not recommended.

```PHP
<?php

use Ghost\GhostFunction;

$methods = ['foo','bar'];
$GhostFunction = new GhostFunction($methods);

// must first define what the methods do:
$GhostFunction->foo(function(){
  print "This is foo";
});

$GhostFunction->bar(function(){
  print "This is bar";
});

// Now call the methods

$GhostFunction->foo(); // This is foo
$GhostFunction->bar(); // This is bar
```

##### Initializing with GhostProxy (Recommended) 
There are difference ways to initialize with GhostProxy. These are shown below:

###### Initialization with arrays

```PHP
<?php

include_once('vendor/autoload');

use Ghost\GhostProxy;
use Ghost\GhostFunction;
use Ghost\GhostDraft;

abstract class SomeClass{

 // GhostProxy object must be mapped for accessing 
 final public function __construct(protected GhostDraft $get, protected ?GhostFunction $proxy = null)
  {
      $this->proxy = GhostProxy::map($this->get->id(), fn() => $this->get->ghost());
      $proxy = $this->proxy; // assign proxy to local variable for easy access
      $this->ghostInit(); // initialize custom class
      $this->proxy = $proxy; // re-assign proxy to ensure it is not lost during initialization;
  }

  public function foo() {
    print 'This is foo';
  }

  public function bar() {
    print 'This is bar';
  }

}
```

Now, let's extend the abstract class to the GhostProxy object.

```PHP
<?php

include_once('SomeClass.php');
use Ghost\GhostProxy;
use Ghost\GhostDraft;

GhostProxy::new([], fn(GhostDraft $draft) => new class($draft) extends SomeClass {});

/** @var SomeClass $SomeClass */
$SomeClass = GhostProxy::object(); // returns object of SomeClass;

// Now call the methods with IDE support.
$SomeClass->foo(); // This is foo
$SomeClass->bar(); // This is bar
```

###### Initialization with GhostFunction

```PHP
<?php

use Ghost\GhostFunction;
use Ghost\GhostDraft;
use Ghost\GhostProxy;

$methods = ['foo','bar'];
$GhostFunction = new GhostFunction($methods);

// must first define what the methods do:
$GhostFunction->foo(function(){
  print "This is foo";
});

$GhostFunction->bar(function(){
  print "This is bar";
});

GhostProxy::new($GhostFunction, fn(GhostDraft $draft) => new class($draft) extends SomeClass {

  final public function __construct(protected GhostDraft $get, protected ?GhostFunction $proxy = null)
  {
      $this->proxy = GhostProxy::map($this->get->id(), fn() => $this->get->ghost());
      $proxy = $this->proxy; // assign proxy to local variable for easy access
      $this->ghostInit(); // initialize custom class
      $this->proxy = $proxy; // re-assign proxy to ensure it is not lost during initialization;
  }

  function foo() { $this->proxy->foo(); }
  function bar() { $this->proxy->bar(); }

});

/** @var SomeClass $SomeClass */
$SomeClass = GhostProxy::object(); // returns object of SomeClass;

// Now call the methods with IDE support.
$SomeClass->foo(); // This is foo
$SomeClass->bar(); // This is bar
```

###### Working with properties
Properties are defined using arrays with array keys as properties each having its own private value.

```PHP
<?php

use Ghost\GhostFunction;
use Ghost\GhostProxy;
use Ghost\GhostDraft;

$methods = [['foo'=>'foo value'],'bar']; // foo is a property while bar is method.
$GhostFunction = new GhostFunction($methods);

$GhostFunction->bar(function(){
  print "This is bar";
});

GhostProxy::new($GhostFunction, fn(GhostDraft $draft) => new class($draft) extends SomeClass {

   final public function __construct(protected GhostDraft $get, protected ?GhostFunction $proxy = null)
    {
        $this->proxy = GhostProxy::map($this->get->id(), fn() => $this->get->ghost());
        $proxy = $this->proxy; // assign proxy to local variable for easy access
        $this->ghostInit(); // initialize custom class
        $this->proxy = $proxy; // re-assign proxy to ensure it is not lost during initialization;
    }

    public function showFoo() {
      print $this->proxy->foo; // accessing a proxy property
    }

    public function showBar() {
      print $this->proxy->bar(); // accessing a proxy method
    }
});

/** @var SomeClass $SomeClass */
$SomeClass = GhostProxy::object(); // returns object of SomeClass

// Now call the methods with IDE support.
$SomeClass->showFoo(); // This is foo
$SomeClass->showBar(); // This is bar
```

###### Passing argument to methods:
One of the major benefits of GhostProxy system is that it allows live passing of arguments to methods, making the system
suitable for advanced operations.

```PHP
<?php

use Ghost\GhostFunction;
use Ghost\GhostDraft;
use Ghost\GhostProxy;

class TestClass{

  private function $message = 'Hello';

  public function __construct() {

    $GhostFunction = new GhostFunction(['msg']); // initialized with method

    $GhostFunction->msg( fn($user) => self::$text ); // define method's function

    GhostProxy::new($GhostFunction, fn(GhostDraft $draft) => new class($draft) extends GhostMessenger {

       // map proxy for data access
       final public function __construct(protected GhostDraft $get, protected ?GhostFunction $proxy = null)
        {
            $this->proxy = GhostProxy::map($this->get->id(), fn() => $this->get->ghost());
            $proxy = $this->proxy; // assign proxy to local variable for easy access
            $this->ghostInit(); // initialize custom class
            $this->proxy = $proxy; // re-assign proxy to ensure it is not lost during initialization;
        }
    
        public function messageUser(string $user = '') {
          print $this->proxy->msg($user); // accessing a proxy property
        }

    });

    $GhostMessenger = GhostProxy::object(); // access GhostMessenger object immediately
    $GhostMessenger->messageUser("Felix"); // Hello Felix.

  }

}
```

##### Ghost Object Mapping
Mapping Ghost object is essential to ensure that the GhostProxy is properly connected to the correct GhostFunction object instance. 
By default, the mapping is done through the ```__construct()``` method. Using the format below:

```PHP
<?php

use Ghost\GhostFunction;
use Ghost\GhostDraft;
use Ghost\GhostProxy;

class TestClass{

  private function $message = 'Hello '; 

  public function __construct() {

    $GhostFunction = new GhostFunction(['msg']); // initialized with method

    $GhostFunction->msg( fn($user) => self::$text ); // define method's function

    GhostProxy::new($GhostFunction, fn(GhostDraft $draft) => new class($draft) extends GhostMessenger {

       // map proxy for data access
       final public function __construct(protected GhostDraft $get, protected ?GhostFunction $proxy = null)
        {
            $this->proxy = GhostProxy::map($this->get->id(), fn() => $this->get->ghost());
            $proxy = $this->proxy; // assign proxy to local variable for easy access
            $this->ghostInit(); // initialize custom class
            $this->proxy = $proxy; // re-assign proxy to ensure it is not lost during initialization;
        }
    
        public function messageUser(string $user = '') {
          print $this->proxy->msg($user); // accessing a proxy property
        }

        public function ghostInit() {
          // run this function during instantiation
        }

    });

    $GhostMessenger = GhostProxy::object(); // access GhostMessenger object immediately
    $GhostMessenger->messageUser("Felix"); // Hello Felix.

  }

}
```

The example above ensures that the object is properly mapped to GhostProxy. The ```ghostInit()` method is triggered after mapping 
this ensures that user can have access to the already mapped proxy object. Also, notice that the proxy was re-aassigned to ensure that  
the property is never overruled nor modified by any user activity. However, when setting up structure like this, the ```GhostClass``` is already 
designed for this process. It automatically maps the object id to the class and provides the ```ghostInit()``` method for instantiation and 
the ```proxy``` property for accessing proxy data respectively. This is shown in the two classes below:

```PHP
<?php

use Ghost\GhostDraft;
use Ghost\GhostFunction;
use Ghost\GhostProxy;

include_once('GhostMessenger.php');

class TestClass {

  private string $message = 'Hello'; 

  public function __construct() {

    $GhostFunction = new GhostFunction([['name'=>'Felix Brian']]); // initialized with a property and value

    GhostProxy::new($GhostFunction, fn(GhostDraft $draft) => new class($draft) extends GhostMessenger {});

    $GhostMessenger = GhostProxy::object(); // return GhostMessenger object immediately
    $GhostMessenger->messageUser("Felix"); // Hello Felix.

  }

}
```

```PHP
<?php

include_once('vendor/autoload.php');

use Ghost\GhostFunction;
use Ghost\GhostDraft;
use Ghost\GhostProxy;
use Ghost\GhostClass;

class GhostMessenger extends GhostClass {

  private string $user;

   // method designed for initialializing class
   private function ghostInit() {
     $name = $this->proxy->name; // Felix Brian (returned from proxy property assigned by GhostClass)
     $this->user = $this->proxy->name; // setting a value during instantiation 
     $this->proxy->name = "John Doe"; // Throws fatal error that property does not exit.
   }

   // methods callable after initialization
   public function getUser(){
      print $this->user; // Felix Brian (assigned through ghostInit method during instantation)
   }

}
```

As noticed above, the reserved ```proxy``` property is immutable and can never be modified while the ```ghostInit()``` is 
first executed during instantiation.

##### Ghost Stringified Object
The ```GhostProxy``` object allows stringifying of the class object returned. In this case, the method to be be stringified must be 
duely specified in the GhostFunction object.

```PHP
<?php

use Ghost\GhostDraft;
use Ghost\GhostFunction;
use Ghost\GhostProxy;

include_once('GhostMessenger.php');

class TestClass {

  private string $message = 'Hello'; 

  public function __construct() {

    $GhostFunction = new GhostFunction(['::value']); // initialized with a main method which must return a string.
    $GhostFunction->value(fn() => 'Foo bar');

    GhostProxy::new($GhostFunction, fn(GhostDraft $draft) => new class($draft) extends GhostMessenger {});

    $GhostMessenger = GhostProxy::object(); // return GhostMessenger object immediately

    print $GhostMessenger; // Foo bar

  }

}
```

From the sample above, we can see that the ```GhostProxy``` object has extensive capabilities, making it easier to work with anonymous objects while using 
abstract object for IDEs support.
