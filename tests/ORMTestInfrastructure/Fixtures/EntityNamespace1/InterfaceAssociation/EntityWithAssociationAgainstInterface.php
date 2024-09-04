<?php

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityNamespace1\InterfaceAssociation;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity that references an interface.
 *
 */
#[ORM\Table(name: 'entity_with_interface_association')]
#[ORM\Entity]
class EntityWithAssociationAgainstInterface
{
    /**
     * A unique ID.
     *
     * @var integer|null
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer', name: 'id')]
    #[ORM\GeneratedValue]
    public $id = null;

    /**
     * @var EntityInterface
     */
    #[ORM\JoinColumn(name: 'entity_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \EntityInterface::class)]
    public $entity = null;
}
