<?php

use Doctrine\Common\Persistence\ObjectManager;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity;

/* @var $objectManager ObjectManager */
$entities = array();
for ($i = 0; $i < 100; $i++) {
    $entities[] = new TestEntity();
}
return $entities;
