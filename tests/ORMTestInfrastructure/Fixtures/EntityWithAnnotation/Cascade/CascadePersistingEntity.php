<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityWithAnnotation\Cascade;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entity that automatically persists its associated entities.
 *
 * @ORM\Entity()
 * @ORM\Table(name="cascade_persist")
 */
#[ORM\Table(name: 'cascade_persist')]
#[ORM\Entity]
class CascadePersistingEntity
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
     * @var Collection
     * @ORM\OneToMany(targetEntity="CascadePersistedEntity", mappedBy="parent", cascade={"persist"})
     */
    #[ORM\OneToMany(targetEntity: \CascadePersistedEntity::class, mappedBy: 'parent', cascade: ['persist'])]
    private $associated;

    /**
     * Initializes the collection.
     */
    public function __construct()
    {
        $this->associated = new ArrayCollection();
    }

    /**
     * Adds the given entity to the persisting association.
     *
     * @param CascadePersistedEntity $entity
     */
    public function add(CascadePersistedEntity $entity)
    {
        $entity->parent = $this;
        $this->associated->add($entity);
    }
}
