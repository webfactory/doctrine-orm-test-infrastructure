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
 * Test entity that references another entity and therefore implicitly depends
 * on it in test scenarios.
 */
#[ORM\Table(name: 'test_entity_with_dependency')]
#[ORM\Entity]
class TestEntityWithDependency
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
    #[ORM\OneToOne(targetEntity: \ReferencedEntity::class, cascade: ['all'])]
    protected $dependency = null;

    /**
     * Automatically creates a reference on construction.
     */
    public function __construct()
    {
        $this->dependency = new ReferencedEntity();
    }
}
