<?php

use Doctrine\Common\Persistence\ObjectManager;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntity;

/* @var $objectManager ObjectManager */
for ($i = 0; $i < 100; $i++) {
    $objectManager->persist(new TestEntity());
}
