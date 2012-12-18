<?php
namespace mpr\db;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-09-21 at 11:04:37.
 */
class mongoDbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var mongoDb
     */
    protected $object;

    /**
     * Test object
     *
     * @var array
     */
    protected $testObject = [
        "key" => "test_blabla",
        "value" => "some_value"
    ];

    /**
     * Set up object
     */
    public function setUp()
    {
        $this->object = mongoDb::factory();
    }

    /**
     * Tear down object
     */
    public function tearDown()
    {
        mongoDb::factory()->remove('test', []);
    }

    /**
     * @covers mpr\db\mongoDb::factory
     */
    public function testFactory()
    {
        $this->assertInstanceOf('\\mpr\\db\\mongoDb', $this->object);
    }

    /**
     * @covers mpr\db\mongoDb::insert
     */
    public function testInsert()
    {
        $this->object->insert('test', $this->testObject);
        $this->assertInstanceOf('\\MongoId', $this->testObject['_id']);
        $this->assertEquals($this->testObject, $this->object->selectOne('test', ['_id' => $this->testObject['_id']]));
    }

    /**
     * @covers mpr\db\mongoDb::update
     * @depends testInsert
     */
    public function testUpdate()
    {
        $this->testInsert();
        $this->testObject['value'] = "second_Value";
        $this->object->update('test', ['_id' => $this->testObject['_id']], $this->testObject);
        $result = $this->object->selectOne('test', ['_id' => $this->testObject['_id']]);
        ksort($result);
        ksort($this->testObject);
        $this->assertEquals($this->testObject, $result);

    }

    /**
     * @covers mpr\db\mongoDb::remove
     * @depends testUpdate
     */
    public function testRemove()
    {
        $this->testUpdate();
        $this->object->remove('test', ['_id', $this->testObject['_id']]);
        $this->assertNull($this->object->selectOne('test', ['_id', $this->testObject['_id']]));
    }

    /**
     * @covers mpr\db\mongoDb::getCount
     */
    public function testGetCount()
    {
        $this->assertEquals(0, $this->object->getCount('test'));
    }

    /**
     * @covers mpr\db\mongoDb::save
     */
    public function testSave()
    {
        $this->tearDown();
        $this->object->save('test', $this->testObject);
        $this->assertInstanceOf('\\MongoId', $this->testObject['_id']);
        $this->assertEquals($this->testObject, $this->object->selectOne('test', ['_id' => $this->testObject['_id']]));
    }
}
