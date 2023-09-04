<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure\_files\ORMInfrastructure\Entity;

use Doctrine\ORM\Mapping as ORM;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructureTest\TestEntityRepository;

if (PHP_VERSION_ID >= 80000) {

    /**
     * Doctrine entity that is used for testing.
     */
    #[ORM\Entity(repositoryClass: TestEntityRepository::class)]
    #[ORM\Table(name: 'test_entity')]
    class TestEntityWithAttribute
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
         * @var string|null
         */
         #[ORM\Column(type: 'string', name: 'name', nullable: true)]
        public $name = null;
    }
}