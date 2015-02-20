<?php

namespace Webfactory\Doctrine\ORMTestInfrastructure;

/**
 * Tests the value object that holds query data.
 */
class QueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * System under test.
     *
     * @var Query
     */
    protected $query = null;

    /**
     * Initializes the test environment.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->query= new Query(
            'SELECT * FROM user WHERE id = ?',
            array(42),
            0.012
        );
    }

    /**
     * Cleans up the test environment.
     */
    protected function tearDown()
    {
        $this->query = null;
        parent::tearDown();
    }

    /**
     * Checks if the correct SQL is returned by the query object.
     */
    public function testGetSqlReturnsCorrectValue()
    {
        $this->assertEquals('SELECT * FROM user WHERE id = ?', $this->query->getSql());
    }

    /**
     * Checks if the query parameters are returned correctly.
     */
    public function testGetParamsReturnsCorrectValue()
    {
        $this->assertEquals(array(42), $this->query->getParams());
    }

    /**
     * Ensures that the correct execution time is returned.
     */
    public function testGetExecutionTimeInSecondsReturnsCorrectValue()
    {
        $this->assertEquals(0.012, $this->query->getExecutionTimeInSeconds());
    }

    /**
     * Checks if the query object can be used to retrieve a string representation of the query.
     */
    public function testQueryObjectProvidesStringRepresentation()
    {
        $this->assertNotEmpty((string)$this->query);
    }
}
