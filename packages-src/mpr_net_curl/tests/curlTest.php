<?php
namespace mpr\net;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-09-21 at 13:57:04.
 */
class curlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var curl
     */
    protected $object;

    /**
     * The string to search for in google
     *
     * @var string
     */
    protected $search_string = 'blablatest';

    /**
     * The url to test for
     *
     * @var string
     */
    protected $test_url = "http://www.google.com";

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new curl();
    }

    /**
     * @covers mpr\net\curl::prepare
     */
    public function testPrepare()
    {
        $resource = $this->object->prepare($this->test_url, ['q' => $this->search_string], 'GET');
        $this->assertEquals('curl', get_resource_type($resource));
    }

    /**
     * @covers mpr\net\curl::execute
     */
    public function testExecute()
    {
        $this->object->prepare($this->test_url, ['q' => $this->search_string], 'GET');
        $result = $this->object->execute();
        $this->assertContains($this->search_string, $result);
    }

    /**
     * @covers mpr\net\curl::reset
     */
    public function testReset()
    {
        $clone = clone $this->object;
        $this->object->prepare("http://ya.ru");
        $clone->prepare("http://ya.ru");
        $this->assertEquals($clone, $this->object);
        $this->object->reset();
        $this->assertNotEquals($clone, $this->object);
    }
}
