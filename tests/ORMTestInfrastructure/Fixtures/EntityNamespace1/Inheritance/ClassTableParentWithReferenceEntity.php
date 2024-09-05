<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityNamespace1\Inheritance;

use Doctrine\ORM\Mapping as ORM;
use Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityNamespace1\ReferencedEntity;

/**
 * Base class for entities with class table strategy.
 *
 * References another entity. This dependency is inherited by all children.
 *
 * @see http://doctrine-orm.readthedocs.org/en/latest/reference/inheritance-mapping.html#class-table-inheritance
 */
#[ORM\Table(name: 'class_inheritance_with_reference_parent')]
#[ORM\Entity]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'class', type: 'string')]
abstract class ClassTableParentWithReferenceEntity
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
     * Required reference to another entity.
     *
     * @var ReferencedEntity
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\OneToOne(targetEntity: \Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityNamespace1\ReferencedEntity::class, cascade: ['all'])]
    protected $dependency = null;

    /**
     * Automatically creates a reference on construction.
     */
    public function __construct()
    {
        $this->dependency = new ReferencedEntity();
    }
}