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
 * Doctrine entity that is used for testing.
 */
#[ORM\Table(name: 'test_entity')]
#[ORM\Entity(repositoryClass: \TestEntityRepository::class)]
class TestEntity
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
     * A string property.
     *
     * @var string
     */
    #[ORM\Column(type: 'string', name: 'name', nullable: true)]
    public $name = null;
}
