<?php

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityWithAnnotation\DependencyResolverFixtures\TransientBaseClass;

use Doctrine\ORM\Mapping as ORM;

class BaseClass
{
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
class Entity extends BaseClass
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
    protected $fieldB;
}
