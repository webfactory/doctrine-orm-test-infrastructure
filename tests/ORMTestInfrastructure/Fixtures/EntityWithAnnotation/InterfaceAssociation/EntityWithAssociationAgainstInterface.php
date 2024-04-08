<?php

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityWithAnnotation\InterfaceAssociation;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity that references an interface.
 *
 * @ORM\Entity()
 * @ORM\Table(name="entity_with_interface_association")
 */
#[ORM\Table(name: 'entity_with_interface_association')]
#[ORM\Entity]
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
    #[ORM\Id]
    #[ORM\Column(type: 'integer', name: 'id')]
    #[ORM\GeneratedValue]
    public $id = null;

    /**
     * @var EntityInterface
     * @ORM\ManyToOne(targetEntity="EntityInterface")
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id")
     */
    #[ORM\JoinColumn(name: 'entity_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \EntityInterface::class)]
    public $entity = null;
}
