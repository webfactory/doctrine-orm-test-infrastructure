<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest;

use Doctrine\ORM\Mapping as ORM;

/**
 * Test entity that references another entity and therefore implicitly depends
 * on it in test scenarios.
 *
 * @ORM\Entity()
 * @ORM\Table(name="test_entity_with_dependency")
 */
class TestEntityWithDependency
{
    /**
     * @var ReferencedEntity
     */
    protected $dependency = null;
}
