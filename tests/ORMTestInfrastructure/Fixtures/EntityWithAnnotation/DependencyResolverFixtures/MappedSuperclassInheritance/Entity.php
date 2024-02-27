<?php

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityWithAnnotation\DependencyResolverFixtures\MappedSuperclassInheritance;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass()
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
 */
class Entity extends Superclass
{
    /**
     * @ORM\Column
     */
    protected $fieldB;
}
