<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\_files\ORMInfrastructure\Entity\DependencyResolverFixtures\JoinedTableInheritanceWithTwoSubclasses;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="class", type="string")
 * @ORM\DiscriminatorMap({"base" = "BaseEntity",  "first" = "Entity", "second" = "SecondEntity"})
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
class SecondEntity extends BaseEntity
{
    /**
     * @ORM\Column
     */
    protected $fieldB;
}

/**
 * @ORM\Entity()
 */
class Entity extends BaseEntity
{
    /**
     * @ORM\Column
     */
    protected $fieldC;
}
