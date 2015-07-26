<?php

use Doctrine\Common\Persistence\ObjectManager;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity;

/* @var $objectManager ObjectManager */
for ($i = 0; $i < 10; $i++) {
    $objectManager->persist(new TestEntity());
}
