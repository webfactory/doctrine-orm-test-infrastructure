<?php

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityNamespace1\DependencyResolverFixtures\TwoEntitiesInheritance;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'entity')]
#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['super' => Superclass::class, 'entity' => Entity::class])]
class Superclass
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    private $id;

    #[ORM\Column]
    protected $fieldA;
}

#[ORM\Entity]
class Entity extends Superclass
{
    #[ORM\Column]
    protected $fieldB;
}
