<?php

/*
 * (c) webfactory GmbH <info@webfactory.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest;

use Doctrine\ORM\Mapping as ORM;
use Webfactory\Doctrine\ORMTestInfrastructure\_files\ORMInfrastructure\Attribute\AttributeForSimpleTest;

if (PHP_VERSION_ID >= 80000) {
    /**
     * An entity that uses a custom attribute.
     */
    #[ORM\Entity]
    #[ORM\Table(name: 'attributed_test_entity')]
    #[AttributeForSimpleTest]
    class AttributedTestEntity
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

}