<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest;

/**
 * Entity with a minimal reference cycle.
 */
class ReferenceCycleEntity
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
     * Reference to an entity of the same type (minimal cycle).
     *
     * @var ReferenceCycleEntity|null
     * @ORM\OneToOne(targetEntity="ReferenceCycleEntity", cascade={"all"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $referenceCycle = null;

    /**
     * Creates an entity that references the provided entity.
     *
     * @param ReferenceCycleEntity|null $entity
     */
    public function __construct(ReferenceCycleEntity $entity = null)
    {
        $this->referenceCycle = $entity;
    }
}
