<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest;

/**
 * Entity that references an entity indirectly (over another reference).
 *
 * Reference chain:
 *
 *     ChainReferenceEntity -> TestEntityWithDependency -> ReferencedEntity
 */
class ChainReferenceEntity
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
     * @var TestEntityWithDependency
     * @ORM\OneToOne(targetEntity="TestEntityWithDependency", cascade={"all"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $dependency = null;

    /**
     * Automatically adds a referenced entity on construction.
     */
    public function __construct()
    {
        $this->dependency = new TestEntityWithDependency();
    }
}
