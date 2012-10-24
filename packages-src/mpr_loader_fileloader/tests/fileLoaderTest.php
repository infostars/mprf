<?php
namespace mpr\loader;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-09-21 at 13:29:11.
 */
class fileLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var fileLoader
     */
    protected $object;

    /**
     * Test file to load for
     *
     * @var string
     */
    protected $somefile = '/tmp/fileLoader.test';

    /**
     * Set up object
     */
    protected function setUp()
    {
        if(file_exists($this->somefile)) {
            unlink($this->somefile);
        }
    }

    /**
     * Tear down object
     */
    protected function tearDown()
    {
        if(file_exists($this->somefile)) {
            unlink($this->somefile);
        }
    }

    /**
     * @covers mpr\loader\fileLoader::load
     */
    public function testLoad()
    {
        $e = null;
        try {
            fileLoader::load($this->somefile);
        } catch(\Exception $e) {

        }
        $this->assertInstanceOf("\\Exception", $e);
        touch($this->somefile);
        $this->assertEquals(1, fileLoader::load($this->somefile));
    }

    /**
     * @covers mpr\loader\fileLoader::loadJson
     */
    public function testLoadJson()
    {
        $data = json_encode(['some', 'array', 'data']);
        file_put_contents($this->somefile, $data);
        $this->assertEquals(json_decode($data, 1), fileLoader::loadJson($this->somefile, true));
        $this->assertEquals(json_decode($data, 0), fileLoader::loadJson($this->somefile));
    }
}
