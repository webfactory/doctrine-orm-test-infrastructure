<?php

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityNamespace1\InterfaceAssociation;

use Doctrine\ORM\Mapping as ORM;

/**
 * Implements an interface that is used in an association.
 */
#[ORM\Table(name: 'entity_implementing_referenced_interface')]
#[ORM\Entity]
class EntityImplementation implements EntityInterface
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
     * Dummy function.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
