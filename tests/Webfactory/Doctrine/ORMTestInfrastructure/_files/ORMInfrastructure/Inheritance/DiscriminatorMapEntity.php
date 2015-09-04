<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\Inheritance;

use Doctrine\ORM\Mapping as ORM;

/**
 * Base entity with an explicit discriminator map.
 *
 * The discriminator map contains fully qualified as well as relative entity class names.
 *
 * @ORM\Entity()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "parent" = "\Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\Inheritance\DiscriminatorMapEntity",
 *     "child" = "DiscriminatorMapChildEntity"
 * })
 * @see http://doctrine-orm.readthedocs.org/en/latest/reference/inheritance-mapping.html#class-table-inheritance
 */
class DiscriminatorMapEntity
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
}
