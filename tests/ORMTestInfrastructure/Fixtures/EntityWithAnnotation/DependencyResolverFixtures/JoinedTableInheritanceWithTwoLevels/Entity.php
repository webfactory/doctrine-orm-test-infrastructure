<?php

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityWithAnnotation\DependencyResolverFixtures\JoinedTableInheritanceWithTwoLevels;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="class", type="string")
 * @ORM\DiscriminatorMap({"base" = "BaseEntity",  "intermediate" = "IntermediateEntity", "child" = "Entity"})
 */
class BaseEntity
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
 */
class IntermediateEntity extends BaseEntity
{
    /**
     * @ORM\Column
     */
    protected $fieldB;
}

/**
 * @ORM\Entity()
 */
class Entity extends IntermediateEntity
{
    /**
     * @ORM\Column
     */
    protected $fieldC;
}
