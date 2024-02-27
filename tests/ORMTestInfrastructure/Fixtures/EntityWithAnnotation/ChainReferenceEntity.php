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
 * Entity that references an entity indirectly (over another reference).
 *
 * Reference chain:
 *
 *     ChainReferenceEntity -> TestEntityWithDependency -> ReferencedEntity
 *
 * @ORM\Entity()
 * @ORM\Table(name="test_chain_reference_entity")
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
