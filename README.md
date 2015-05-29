doctrine-orm-test-infrastructure
================================

[![Build Status](https://travis-ci.org/webfactory/doctrine-orm-test-infrastructure.svg?branch=master)](https://travis-ci.org/webfactory/doctrine-orm-test-infrastructure)
[![Coverage Status](https://img.shields.io/coveralls/webfactory/doctrine-orm-test-infrastructure.svg)](https://coveralls.io/r/webfactory/doctrine-orm-test-infrastructure?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/webfactory/doctrine-orm-test-infrastructure/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/webfactory/doctrine-orm-test-infrastructure/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/ee876bf5-48d3-40ce-a488-3fafc5f776d7/mini.png)](https://insight.sensiolabs.com/projects/ee876bf5-48d3-40ce-a488-3fafc5f776d7)

This library provides some infrastructure for tests of Doctrine ORM entities, featuring:

- configuration of a SQLite in memory database, compromising well between speed and a database environment being both
  realistic and isolated 
- a mechanism for importing fixtures into your database that circumvents Doctrine's caching. This results in a more
  realistic test environment when loading entities from a repository.

[We](https://www.webfactory.de/) use it to test Doctrine repositories and entities in Symfony 2 bundles. In
applications, it's a lightweight alternative to the heavyweight [functional tests suggested in the Symfony documentation](http://symfony.com/doc/current/cookbook/testing/doctrine.html)
(we don't suggest you should skip those - we just want to open another path). In non application bundles, where
functional tests are not possible, it is our only way to test repositories and entities.


Installation
------------

Add the following to composer.json (see http://getcomposer.org/):

    "require-dev" :  {
        // ...
        "webfactory/doctrine-orm-test-infrastructure": "@stable"
    }

If you don't have a `require-dev` key in your `composer.json` file, just
add one! You can alternatively add this to your `require` key and things
will work just fine. Confused about the difference? See:
[GetComposer.org: require-dev](https://getcomposer.org/doc/04-schema.md#require-dev).


Usage
-----

    <?php
    
    use Entity\MyEntity;
    use Entity\MyEntityRepository;
    use Webfactory\Doctrine\ORMTestInfrastructure;
    
    class MyEntityRepositoryTest extends \PHPUnit_Framework_TestCase
    {
        /** @var ORMInfrastructure */
        private $infrastructure;
        
        /** @var MyEntityRepository */
        private $repository;
        
        /** @see \PHPUnit_Framework_TestCase::setUp() */
        protected function setUp()
        {
            $this->infrastructure = ORMInfrastructure::createWithDependenciesFor(
                array(
                    'Entity\MyEntity'
                )
            );
            $this->repository = $this->infrastructure->getRepository('Entity\MyEntity');
        }
        
        /**
         * Example test: Asserts imported fixtures are retrieved with findAll().
         */
        public function testFindAllRetrievesFixtures()
        {
            $myEntityFixture = new MyEntity();
            $this->infrastructure->import($myEntityFixture);
            
            $entitiesLoadedFromDatabase = $this->repository->findAll();

            // Please note that due to a bug in PHPUnit, you might not be able to do the following:
            // $this->assertContains($myEntityFixture, $entitiesLoadedFromDatabase);

            // But you can do things like this (you probably want to extract that in a convenient assertion method):
            $this->assertCount(1, $entitiesLoadedFromDatabase);
            $entityLoadedFromDatabase = $entitiesLoadedFromDatabase[0];
            $this->assertEquals($myEntityFixture->getId(), $entityLoadedFromDatabase->getId());
        }
        
        /**
         * Example test for retrieving Doctrine's entity manager.
         */
        public function testSomeFancyThingWithEntityManager()
        {
            $entityManager = $this->infrastructure->getEntityManager();
            // ...
        }
    }
    

Testing the library itself
--------------------------

After installing the dependencies managed via composer, just run

    vendor/bin/phpunit

from the library's root folder. This uses the shipped phpunit.xml.dist - feel free to create your own phpunit.xml if you
need local changes.

Happy testing!


Known Issues
------------

Please note that apart from any [open issues in this library](https://github.com/webfactory/doctrine-orm-test-infrastructure/issues), you
may stumble upon any Doctrine issues. Especially take care of it's [known sqlite issues](http://doctrine-dbal.readthedocs.org/en/latest/reference/known-vendor-issues.html#sqlite).


Credits, Copyright and License
------------------------------

This bundle was started at webfactory GmbH, Bonn.

- <http://www.webfactory.de>
- <http://twitter.com/webfactory>

Copyright 2012-2014 webfactory GmbH, Bonn. Code released under [the MIT license](LICENSE).
