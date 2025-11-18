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
 * An entity that is referenced by another one.
 */
#[ORM\Table(name: 'referenced_entity')]
#[ORM\Entity]
class ReferencedEntity
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
}
