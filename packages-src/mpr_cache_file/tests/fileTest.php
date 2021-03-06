<?php
namespace mpr\cache;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-09-20 at 20:18:07.
 */
class fileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Base instance of object
     *
     * @var file
     */
    protected $object;

    /**
     * Test key
     *
     * @var string
     */
    protected $test_key = "I'am:test_key";

    /**
     * Test value
     *
     * @var string
     */
    protected $test_value = "I'am test value";

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new file;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->object);
    }

    /**
     * @covers mpr\cache\memcached::set
     */
    public function testSet()
    {
        $this->object->set($this->test_key, $this->test_value, 10);
        $this->assertEquals($this->test_value, $this->object->get($this->test_key));
    }

    /**
     * @covers mpr\cache\memcached::exists
     * @depends testSet
     */
    public function testExists()
    {
        $this->testSet();
        $this->assertTrue($this->object->exists($this->test_key));
    }

    /**
     * @covers mpr\cache\memcached::get
     * @depends testExists
     */
    public function testGet()
    {
        $this->testExists();
        $this->assertEquals($this->test_value, $this->object->get($this->test_key));
    }

    /**
     * @covers mpr\cache\memcached::remove
     * @depends testGet
     */
    public function testRemove()
    {
        $this->testGet();
        $this->object->remove($this->test_key);
        $this->assertFalse($this->object->exists($this->test_key));
    }

    /**
     * @covers mpr\cache\memcached::clear
     * @depends testRemove
     */
    public function testClear()
    {
        $data = [
            "key1" => "value1",
            "key2" => "value2",
            "key3" => "value3",
            "key4" => "value4",
            "key5" => "value5"
        ];

        foreach($data as $key => $value) {
            $this->object->set($key, $value, 10);
        }

        $this->object->clear();

        $result = [];
        foreach($data as $key => $value) {
            if($this->object->get($key) != null || $this->object->exists($key)) {
                $result[$key] = $this->object->get($key);
            }
        }
        $this->assertCount(0, $result);
    }
}
