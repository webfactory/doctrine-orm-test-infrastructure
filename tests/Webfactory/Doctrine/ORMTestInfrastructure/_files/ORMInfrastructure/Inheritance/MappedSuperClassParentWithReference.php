<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\Inheritance;

use Doctrine\ORM\Mapping as ORM;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferencedEntity;

/**
 * Mapped super class that references another entity.
 *
 * @ORM\MappedSuperclass()
 * @see http://doctrine-orm.readthedocs.org/en/latest/reference/inheritance-mapping.html#mapped-superclasses
 */
abstract class MappedSuperClassParentWithReference
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
     * Required reference to another entity.
     *
     * @var ReferencedEntity
     * @ORM\OneToOne(
     *     targetEntity="\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\ReferencedEntity",
     *     cascade={"all"}
     * )
     * @ORM\JoinColumn(nullable=false)
     */
    protected $dependency = null;

    /**
     * Automatically creates a reference on construction.
     */
    public function __construct()
    {
        $this->dependency = new ReferencedEntity();
    }
}
