<?php

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityWithAnnotation\DependencyResolverFixtures\TwoEntitiesInheritance;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="entity")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"super"=Superclass::class, "entity"=Entity::class})
 */
#[ORM\Table(name: 'entity')]
#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['super' => Superclass::class, 'entity' => Entity::class])]
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
