<?php

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityWithAnnotation\DependencyResolverFixtures\MappedSuperclassInheritance;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass()
 */
#[ORM\MappedSuperclass]
class Superclass
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    private $id;

    /**
     * @ORM\Column
     */
    #[ORM\Column]
    protected $fieldA;
}

/**
 * @ORM\Entity()
 */
#[ORM\Entity]
class Entity extends Superclass
{
    /**
     * @ORM\Column
     */
    #[ORM\Column]
    protected $fieldB;
}
