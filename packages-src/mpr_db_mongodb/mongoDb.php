<?php
namespace mpr\db;

use \mpr\config;

/**
 * MongoDb class wrapper
 *
 * @author GreeveX <greevex@gmail.com>
 */
class mongoDb
{

    /**
     * Instance of this object for singleton
     *
     * @var \mpr\db\mongoDb
     */
    private static $instance;

    /**
     * Native mongo driver instance
     *
     * @var \Mongo
     */
    private $mongo;

    /**
     * Instance of database in mongo
     *
     * @var \MongoDB
     */
    private $db;

    /**
     * Get instance of singleton object
     *
     * @static
     * @return mongoDb
     */
    public static function getInstance()
    {
        if(self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Construct new object
     */
    public function __construct()
    {
        $packageConfig = config::getPackageConfig(__CLASS__);
        $this->mongo = new \Mongo($packageConfig['host']);
        $this->db = $this->mongo
                ->selectDB($packageConfig['dbname']);
    }

    /**
     * Reconnect to mongo server
     *
     * @return bool
     */
    public function reconnect()
    {
        $this->mongo->connect();
        $this->db->resetError();
        return true;
    }

    /**
     * Insert new object in collection
     *
     * @param string $collection Collection name
     * @param array $data Data to save
     * @param array $options MongoDB options
     * @return array|bool Result
     */
    public function insert($collection, &$data, $options = [])
    {
        if(!isset($data['_id'])) {
            $data['_id'] = new \MongoId();
        } elseif(!is_object($data['_id'])) {
            $data['_id'] = new \MongoId($data['_id']);
        }
        return $this->db
                    ->selectCollection($collection)
                    ->insert($data, $options);
    }

    /**
    * Select array of data from collection
    *
    * @param string $collection Collection name
    * @param array $criteria Criteria for select by
    * @param array $fields Needle fields of object
    * @return \MongoCursor Native mongocursor object
    */
    public function select($collection, $criteria = [], $fields = [])
    {
        $data = $this->db
                    ->selectCollection($collection)
                    ->find($criteria, $fields);
        return $data;
    }

    /**
     * Select one row from collection
     *
     * @param string $collection Collection name
     * @param array $criteria Criteria for select by
     * @param array $fields Needle fields of object
     * @return array|null Result
     */
    public function selectOne($collection, $criteria = [], $fields = [])
    {

        $data = $this->db
                    ->selectCollection($collection)
                    ->findOne($criteria, $fields);
        return $data;
    }

    /**
     * Update data in collection
     *
     * @param string $collection Collection name
     * @param array $criteria Criteria for update by
     * @param array $update_data New data
     * @return bool Result
     */
    public function update($collection, $criteria = [], $update_data = [])
    {
        $data = $this->db
                    ->selectCollection($collection)
                    ->update($criteria, $update_data);
        return $data;
    }

    /**
     * Remove data from collection
     *
     * @param string $collection Collection name
     * @param array $criteria Criteria to remove by
     * @param array $options MongoDB options
     * @return mixed
     */
    public function remove($collection, $criteria, $options = [])
    {
        $data = $this->db
                    ->selectCollection($collection)
                    ->remove($criteria, $options);
        return $data;
    }

    /**
     * Get count objects for collection
     *
     * @param string $collection Collection name
     * @return int Count
     */
    public function getCount($collection)
    {
        return $this->db->selectCollection($collection)->count();
    }

    /**
     * Get count objects for collection by criteria
     *
     * @param string $collection Collection name
     * @param array $criteria Criteria for count by
     * @return int Count
     */
    public function getCountBy($collection, $criteria)
    {
        return $this->select($collection, $criteria)->count();
    }

    /**
     * Get native driver of database
     *
     * @return \MongoDB
     */
    public function getDatabase()
    {
        return $this->db;
    }
}
