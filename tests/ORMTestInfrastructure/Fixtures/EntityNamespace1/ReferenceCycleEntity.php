<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityNamespace1;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity with a minimal reference cycle.
 */
#[ORM\Table(name: 'test_reference_cycle_entity')]
#[ORM\Entity]
class ReferenceCycleEntity
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
     * Reference to an entity of the same type (minimal cycle).
     *
     * @var ReferenceCycleEntity|null
     */
    #[ORM\JoinColumn(nullable: true)]
    #[ORM\OneToOne(targetEntity: \ReferenceCycleEntity::class, cascade: ['all'])]
    protected $referenceCycle = null;

    /**
     * Creates an entity that references the provided entity.
     *
     * @param ReferenceCycleEntity|null $entity
     */
    public function __construct(?ReferenceCycleEntity $entity = null)
    {
        $this->referenceCycle = $entity;
    }
}
