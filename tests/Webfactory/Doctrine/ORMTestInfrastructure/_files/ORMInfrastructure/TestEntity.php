<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest;

use Doctrine\ORM\Mapping as ORM;

/**
 * Doctrine entity that is used for testing.
 *
 * # repositoryClass=""
 *
 * @ORM\Entity()
 * @ORM\Table(name="test_entity")
 */
class TestEntity
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
     * A string property.
     *
     * @var string
     * @ORM\Column(type="string", name="name", nullable=true)
     */
    public $name = null;

}
 