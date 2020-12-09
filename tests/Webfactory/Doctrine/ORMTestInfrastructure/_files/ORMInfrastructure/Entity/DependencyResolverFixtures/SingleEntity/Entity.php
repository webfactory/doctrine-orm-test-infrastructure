<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\_files\ORMInfrastructure\Entity\DependencyResolverFixtures\SingleEntity;

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
