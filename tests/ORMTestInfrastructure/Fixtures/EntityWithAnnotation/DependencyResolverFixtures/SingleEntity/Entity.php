<?php

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityWithAnnotation\DependencyResolverFixtures\SingleEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Entity
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
