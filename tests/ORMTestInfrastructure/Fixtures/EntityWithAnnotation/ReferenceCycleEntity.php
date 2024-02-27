<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityWithAnnotation;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity with a minimal reference cycle.
 *
 * @ORM\Entity()
 * @ORM\Table(name="test_reference_cycle_entity")
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
