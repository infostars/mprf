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
     * Factory object with config
     *
     * @param string $configName `default` - is default value
     * @return self
     */
    public static function factory($configName = 'default')
    {
        static $instances = [];
        if(!isset($instances[$configName])) {
            $instances[$configName] = new self($configName);
        }
        return $instances[$configName];
    }

    /**
     * Construct new object
     */
    public function __construct($configName = 'default')
    {
        $packageConfig = config::getPackageConfig(__CLASS__);
        if(!isset($packageConfig[$configName])) {
            $packageName = config::getPackageName(__CLASS__);
            \mpr\debug\log::put("Config section for package `{$configName}` not found in config!", $packageName);
            throw new \Exception("[{$packageName}] Config section for package `{$configName}` not found in config!");
        }
        $config = $packageConfig[$configName];
        $this->mongo = new \Mongo($config['host']);
        $this->db = $this->mongo
                ->selectDB($config['dbname']);
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
            $data['_id'] = new \MongoId((string)$data['_id']);
        }
        return $this->db
                    ->selectCollection($collection)
                    ->insert($data, $options);
    }

    /**
     * Save object to MongoDB
     *
     * @param string $collection collection name
     * @param array $data Data to save
     * @param array $options MongoDB options
     * @return array|bool result
     */
    public function save($collection, &$data, $options = [])
    {
        if(!isset($data['_id'])) {
            $data['_id'] = new \MongoId();
        } elseif(!($data['_id'] instanceof \MongoId)) {
            $data['_id'] = new \MongoId((string)$data['_id']);
        }
        return $this->db
            ->selectCollection($collection)
            ->save($data, $options);
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
                    ->find($criteria, $this->checkFields($fields));
        return $data;
    }

    /**
     * Validate fields
     *
     * @param array $fields
     * @return array result
     */
    protected function checkFields($fields)
    {
        $result = [];
        if(count($fields) && 0 === array_keys($fields)[0]) {
            foreach($fields as $field) {
                $result[$field] = true;
            }
        }
        return $result;
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
                    ->findOne($criteria, $this->checkFields($fields));
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
        return $this->select($collection, $criteria, ['_id' => true])->count();
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
