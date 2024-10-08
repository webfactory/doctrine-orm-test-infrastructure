<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\Tests\ORMTestInfrastructure\Fixtures\EntityNamespace1\Inheritance;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class DiscriminatorMapChildEntity extends DiscriminatorMapEntity
{
    /**
     * @var string
     */
    #[ORM\Column(type: 'string', name: 'child_name', nullable: false)]
    public $childName = 'child-name';
}
