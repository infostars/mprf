<?php
namespace mpr\io;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-09-21 at 12:58:51.
 */
class fileWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Instance of fileWriter object
     *
     * @var fileWriter
     */
    protected $object;

    /**
     * Path to test file
     *
     * @var string
     */
    protected $filename = "/tmp/test_file_name.test";

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        if(file_exists($this->filename)) {
            unlink($this->filename);
        }
        $this->object = new fileWriter($this->filename);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        if(file_exists($this->filename)) {
            unlink($this->filename);
        }
    }

    /**
     * @covers mpr\io\fileWriter::write
     */
    public function testWrite()
    {
        $test_string = "some data";
        $this->object->write($test_string);
        $this->assertContains($test_string, file_get_contents($this->filename));
    }

    /**
     * @covers mpr\io\fileWriter::writeLn
     */
    public function testWriteLn()
    {
        $test_string = "some data with line endings";
        $this->object->writeLn($test_string);
        $this->assertContains($test_string, file_get_contents($this->filename));
    }

    /**
     * @covers mpr\io\fileWriter::close
     */
    public function testClose()
    {
        $e = null;
        $this->object->close();
        try {
            $this->object->writeLn("some data");
        } catch(\Exception $e) {

        }
        $this->assertInstanceOf('\\Exception', $e);
    }
}