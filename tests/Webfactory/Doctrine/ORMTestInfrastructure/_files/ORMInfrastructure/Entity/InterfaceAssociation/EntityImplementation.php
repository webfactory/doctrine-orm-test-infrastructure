<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest;

use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\InterfaceAssociation\EntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Implements an interface that is used in an association.
 *
 * @ORM\Entity()
 * @ORM\Table(name="entity_implementing_referenced_interface")
 */
class EntityImplementation implements EntityInterface
{
    /**
     * A unique ID.
     *
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue
     */
    public $id = null;

    /**
     * Dummy function.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
