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
 * Entity that references an entity indirectly (over another reference).
 *
 * Reference chain:
 *
 *     ChainReferenceEntity -> TestEntityWithDependency -> ReferencedEntity
 */
#[ORM\Table(name: 'test_chain_reference_entity')]
#[ORM\Entity]
class ChainReferenceEntity
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
     * @var TestEntityWithDependency
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\OneToOne(targetEntity: \TestEntityWithDependency::class, cascade: ['all'])]
    protected $dependency = null;

    /**
     * Automatically adds a referenced entity on construction.
     */
    public function __construct()
    {
        $this->dependency = new TestEntityWithDependency();
    }
}
