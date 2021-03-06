<?php

namespace mpr;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-09-27 at 18:05:56.
 */
class lockerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Base instance of object
     *
     * @var locker
     */
    protected $object;

    /**
     * Test driver name
     *
     * @var string
     */
    protected $driver_name = "memcached";

    /**
     * Test method name
     *
     * @var string
     */
    protected $test_method_name = "test_method_name";

    /**
     * Test expire seconds
     *
     * @static
     * @var int seconds
     */
    protected static $test_expire = 2;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = locker::factory();
        $this->object = locker::factory($this->driver_name);
    }

    /**
     * @covers mpr\locker::lock
     */
    public function testLock()
    {
        $this->assertTrue($this->object->lock($this->test_method_name));
    }

    /**
     * @covers mpr\locker::unlock
     * @depends testLock
     */
    public function testUnlock()
    {
        $result = $this->object->unlock($this->test_method_name);
        $this->assertTrue($result);
    }

    /**
     * Check is function returns false
     *
     * @param string $method_name
     * @return bool
     */
    public function checkFuncFalse($method_name)
    {
        unset($method_name);
        return false;
    }
}