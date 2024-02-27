<?php

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityWithAnnotation\DependencyResolverFixtures\TwoEntitiesInheritanceWithConflictingTableNames;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="some_table")
 */
class Superclass
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    private $id;

    /**
     * @ORM\Column
     */
    protected $fieldA;
}

/**
 * @ORM\Entity()
 * @ORM\Table(name="some_table")
 */
class Entity extends Superclass
{
    /**
     * @ORM\Column
     */
    protected $fieldB;
}
