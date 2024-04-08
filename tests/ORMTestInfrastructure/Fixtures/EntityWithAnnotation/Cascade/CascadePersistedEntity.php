<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityWithAnnotation\Cascade;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity that automatically persists its associated entities.
 *
 * @ORM\Entity()
 * @ORM\Table(name="cascade_persisted_entity")
 */
#[ORM\Table(name: 'cascade_persisted_entity')]
#[ORM\Entity]
class CascadePersistedEntity
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
     * @var CascadePersistingEntity
     * @ORM\ManyToOne(targetEntity="CascadePersistingEntity")
     */
    #[ORM\ManyToOne(targetEntity: \CascadePersistingEntity::class)]
    public $parent;
}
