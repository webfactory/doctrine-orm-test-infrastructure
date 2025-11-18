<?php

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityNamespace1\DependencyResolverFixtures\SingleTableInheritance;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'class', type: 'string')]
#[ORM\DiscriminatorMap(['base' => 'BaseEntity', 'sub' => 'Entity'])]
class BaseEntity
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    private $id;

    #[ORM\Column]
    protected $fieldA;
}

#[ORM\Entity]
class Entity extends BaseEntity
{
    #[ORM\Column]
    protected $fieldB;
}
