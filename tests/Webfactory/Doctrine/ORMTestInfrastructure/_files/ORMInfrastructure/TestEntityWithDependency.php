<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest;

use Doctrine\ORM\Mapping as ORM;

/**
 * Test entity that references another entity and therefore implicitly depends
 * on it in test scenarios.
 *
 * @ORM\Entity()
 * @ORM\Table(name="test_entity_with_dependency")
 */
class TestEntityWithDependency
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
     * @ORM\OneToOne(targetEntity="ReferencedEntity", cascade={"all"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $dependency = null;

    /**
     * Automatically creates a referenced
     */
    public function __construct()
    {
        $this->dependency = new ReferencedEntity();
    }
}
