<?php

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityNamespace1\DependencyResolverFixtures\TransientBaseClass;

use Doctrine\ORM\Mapping as ORM;

class BaseClass
{
    #[ORM\Column]
    protected $fieldA;
}

#[ORM\Entity]
class Entity extends BaseClass
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    private $id;

    #[ORM\Column]
    protected $fieldB;
}
