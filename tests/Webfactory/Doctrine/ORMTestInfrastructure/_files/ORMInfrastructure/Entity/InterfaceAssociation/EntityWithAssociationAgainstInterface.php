<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\InterfaceAssociation;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity that references an interface.
 *
 * @ORM\Entity()
 * @ORM\Table(name="entity_with_interface_association")
 */
class EntityWithAssociationAgainstInterface
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
     * @var EntityInterface
     * @ORM\ManyToOne(targetEntity="EntityInterface")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id")
     */
    public $entity = null;
}
