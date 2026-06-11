<?php

use Ghost\GhostClass;
use Ghost\GhostDraft;
use Ghost\GhostFunction;
use Ghost\GhostProxy;

include_once "vendor/autoload.php";

class TestClass extends GhostClass{
    // #[Override]
    function ghostInit(): void
    {
        // parent::ghostInit();
        print_r($this->proxy->ghosts(GhostFunction::properties));
    }
}

GhostProxy::new(['name'=>'ade'], fn(GhostDraft $draft) => new class($draft) extends TestClass{} );

$object = GhostProxy::object();

// print $object->name;
// print_r(property_exists($object, 'name'));
// print_r(property_exists($object, 'name'));