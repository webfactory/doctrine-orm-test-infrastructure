<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityWithAnnotation\Inheritance;

use Doctrine\ORM\Mapping as ORM;

/**
 * Base class for entities with class table strategy.
 *
 * @ORM\Entity()
 * @ORM\Table(name="class_inheritance_parent")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="class", type="string")
 * @see http://doctrine-orm.readthedocs.org/en/latest/reference/inheritance-mapping.html#class-table-inheritance
 */
abstract class ClassTableParentEntity
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
