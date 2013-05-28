<?php
namespace mpr\helper;

use \mpr\helper\ipselect;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-10-17 at 13:04:55.
 */
class ipselectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ipselect
     */
    protected $object;

    /**
     * @var $network_interface_prefix
     */
    private $network_interface_regexp = '/^\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3}$/';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new ipselect;
    }

    /**
     * Check on which machine test was runned and checks ip data by machine
     */
    public function testCheckOnWichMachineRunTest()
    {
        if($this->object->GetCount() == 0) {
            $this->localMachineTest();
        } else {
            $this->botsMachineTest();
        }
    }

    /**
     * test on local machine
     *
     * @covers mpr\helper\ipselect::getList
     * @covers mpr\helper\ipselect::getCount
     */
    private function localMachineTest()
    {
        $this->assertEquals($this->object->getList(), []);
        $this->assertEquals($this->object->GetCount(), 0);
    }

    /**
     * test on bots{N} machine
     *
     * @covers mpr\helper\ipselect::getList
     * @covers mpr\helper\ipselect::getCount
     */
    private function botsMachineTest()
    {
        $network_interfaces_list = $this->object->getList();
        $this->assertTrue(count($network_interfaces_list) > 0);
        foreach($network_interfaces_list as $interface) {
            $this->assertRegExp($this->network_interface_regexp, $interface);
        }

        $this->assertTrue($this->object->GetCount() > 0);
    }
}
